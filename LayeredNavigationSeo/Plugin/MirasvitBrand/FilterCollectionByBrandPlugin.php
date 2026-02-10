<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Plugin\MirasvitBrand;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirasvit\Brand\Registry;
use Mirasvit\Brand\Service\BrandActionService;

class FilterCollectionByBrandPlugin
{
    private $brandActionService;
    private $registry;

    public function __construct(
        BrandActionService $brandActionService,
        Registry $registry
    ) {
        $this->brandActionService = $brandActionService;
        $this->registry           = $registry;
    }

    public function aroundFilter(object $subject, callable $proceed, ?Collection $collection = null, ...$args): void
    {
        $proceed($collection, ...$args);

        if (!$this->brandActionService->isBrandViewPage()) {
            return;
        }
        if (!$this->registry->getBrand()) {
            return;
        }

        $collection->setFlag('do_not_use_category_id', true);
        $collection->addUrlRewrite(0);

        $collection->addFieldToFilter(
            $this->registry->getBrand()->getAttributeCode(),
            $this->registry->getBrandPage()->getAttributeOptionId()
        );
    }
}