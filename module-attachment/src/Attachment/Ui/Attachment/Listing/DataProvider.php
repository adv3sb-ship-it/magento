<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-attachment
 * @version   1.1.12
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Attachment\Ui\Attachment\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\HistoryInterface;
use Mirasvit\Attachment\Repository\HistoryRepository;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    private $historyRepository;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HistoryRepository $historyRepository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->historyRepository = $historyRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var AttachmentInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $periodData = $this->getDataPerPeriod($item->getId());
            $data       = [
                AttachmentInterface::ID                 => $item->getId(),
                AttachmentInterface::IS_ACTIVE          => (int)$item->isActive(),
                AttachmentInterface::TYPE               => $item->getType(),
                AttachmentInterface::SOURCE_NAME        => $item->getSourceName(),
                AttachmentInterface::FILE_PATH          => $item->getFilePath(),
                AttachmentInterface::FILE_TYPE          => $item->getFileType(),
                AttachmentInterface::LABEL              => $item->getLabel(),
                AttachmentInterface::POSITION           => $item->getPosition(),
                AttachmentInterface::ICON_ID            => $item->getIconId(),
                AttachmentInterface::CUSTOMER_GROUP_IDS => $item->getCustomerGroupIds(),
                AttachmentInterface::STORE_IDS          => $item->getStoreIds(),
                'downloads'                             => $this->getAnalyticsHtml($periodData),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }

    protected function getAnalyticsHtml(array $periodData)
    {
        $html = '<div class="mst_attachment__downloads-html">';
        foreach ($periodData as $label => $count) {
            $html .= sprintf('<div><p>%s <span>%s</span></p></div>', $label, $count);
        }
        $html .= '</div>';

        return $html;
    }

    protected function getDataPerPeriod(int $attachmentId)
    {
        $periods = [
            'TODAY'    => 1,
            '30 DAYS'  => 30,
            'LIFETIME' => 0,
        ];

        $result = [];
        foreach ($periods as $label => $days) {
            $collection = $this->historyRepository->getCollection()->addFieldToFilter(HistoryInterface::ATTACHMENT_ID, $attachmentId);
            if ($days) {
                $days = (int)$days;
                $from = date(
                    DateTime::DATETIME_PHP_FORMAT,
                    mktime(0, 0, 0, (int)date("m"), (int)date("d") - $days + 1, (int)date("Y"))
                );
                $to   = date(DateTime::DATETIME_PHP_FORMAT);
                $collection->addFieldToFilter(HistoryInterface::CREATED_AT, ['from' => $from, 'to' => $to]);
            }
            $result[$label] = $collection->getSize();
        }

        return $result;
    }
}
