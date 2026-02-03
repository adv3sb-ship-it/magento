<?php

namespace Triniti\SeoCrossLinks\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Magento\Framework\Exception\LocalizedException;

class Upload extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    public function __construct(
        Action\Context $context,
        private Csv $csv,
        private ResourceConnection $resource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if (empty($_FILES['csv_file']['tmp_name'])) {
                throw new LocalizedException(__('CSV file not uploaded'));
            }

            $rows = $this->csv->getData($_FILES['csv_file']['tmp_name']);

            if (count($rows) < 2) {
                throw new LocalizedException(__('CSV is empty'));
            }

            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('triniti_seo_crosslink');

            // FULL REPLACE
            $connection->truncateTable($table);

            unset($rows[0]); // header

            foreach ($rows as $row) {
                [
                    $storeId,
                    $donor,
                    $acceptor,
                    $anchor,
                    $sortOrder,
                    $isActive
                ] = array_pad($row, 6, null);

                $donor = $this->normalizePath((string)$donor);

                $acceptor = trim((string)$acceptor);
                $anchor   = trim((string)$anchor);

                $connection->insert($table, [
                    'store_id'     => (int)$storeId,
                    'donor_url'    => $donor,
                    'acceptor_url' => $acceptor,
                    'anchor'       => $anchor,
                    'sort_order'   => (int)$sortOrder,
                    'is_active'    => (int)$isActive
                ]);
            }

            $this->messageManager->addSuccessMessage(__('SEO links imported successfully'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('triniti_crosslinks/import/index');
    }

    private function normalizePath(string $urlOrPath): string
    {
        $urlOrPath = trim($urlOrPath);

        $path = parse_url($urlOrPath, PHP_URL_PATH);
        if (!$path) {
            $path = $urlOrPath;
        }

        $path = preg_replace('#/+#', '/', $path);
        $path = strtolower($path);

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}