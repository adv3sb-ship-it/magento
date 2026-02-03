<?php
namespace Triniti\SeoCrossLinks\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Triniti\SeoCrossLinks\Model\ResourceModel\CrossLink\CollectionFactory;

class SeoLinks extends Template
{
    protected RequestInterface $request;
    protected CollectionFactory $collectionFactory;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Normalize URL path:
     *  – leading /
     *  – no double //
     *  – LOWERCASE
     *  – NO trailing / (except root "/")
     */
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

        // remove trailing slash, but keep "/" as is
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function getLinks(): array
    {
        $storeId = (int)$this->_storeManager->getStore()->getId();

        // Get current path from request
        $currentPath = $this->normalizePath($this->request->getPathInfo());

        // Remove store code from URL if present (e.g., /ru/page -> /page)
        $currentPath = $this->removeStoreCodeFromPath($currentPath);

        // Allow both: exact and variant with trailing slash in DB (just in case)
        $variants = array_values(array_unique([
            $currentPath,
            ($currentPath === '/' ? '/' : $currentPath . '/')
        ]));

        $collection = $this->collectionFactory->create();
        $collection
            // allow store_id 0 (Admin/global) + конкретний store view
            ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
            ->addFieldToFilter('donor_url', ['in' => $variants])
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', 'ASC');

        return $collection->getItems();
    }

    /**
     * Remove store code from path (e.g., /ru/page -> /page)
     * For multi-store setups where URLs have language/store prefixes
     */
    private function removeStoreCodeFromPath(string $path): string
    {
        $storeCode = $this->_storeManager->getStore()->getCode();
        
        // Skip if it's the default admin store
        if ($storeCode === 'admin') {
            return $path;
        }

        // Remove /{store_code}/ from the beginning
        // Example: /ru/zariadni-stantsii -> /zariadni-stantsii
        $pattern = '#^/' . preg_quote($storeCode, '#') . '(/|$)#';
        $path = preg_replace($pattern, '/', $path);

        return $path;
    }
}