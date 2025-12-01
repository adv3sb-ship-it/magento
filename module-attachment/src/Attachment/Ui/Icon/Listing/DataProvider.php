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

namespace Mirasvit\Attachment\Ui\Icon\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Mirasvit\Attachment\Api\Data\IconInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    private $filesystem;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Filesystem $filesystem,
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
        $this->filesystem = $filesystem;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var IconInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                IconInterface::ID        => $item->getId(),
                IconInterface::IS_ACTIVE => (int)$item->isActive(),
                IconInterface::LABEL     => $item->getLabel(),
                IconInterface::TYPES     => $item->getTypes(),
                IconInterface::ICON_PATH => $item->getIconPath(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
