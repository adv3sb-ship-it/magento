<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Service;

use Magento\Framework\Registry;
use Mirasvit\SeoFilter\Service\FriendlyUrlService;

class SeoUrlService
{
    private $filterService;

    private $registry;

    private $friendlyUrlService;

    public function __construct(
        FilterService $filterService,
        Registry $registry,
        FriendlyUrlService $friendlyUrlService
    ) {
        $this->filterService      = $filterService;
        $this->registry           = $registry;
        $this->friendlyUrlService = $friendlyUrlService;
    }

    public function isNoindex(array $additionalParams = []): bool
    {
        try {
            $activeFiltersCount = count($this->filterService->getActiveFilters());

            $totalFilters = $activeFiltersCount + 1;

            return $totalFilters >= 2;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSingleFilterUrl(string $attributeCode, $value): string
    {
        try {
            $category = $this->registry->registry('current_category');

            if (!$category) {
                return '';
            }

            $categoryUrl = $category->getUrl();

            return $this->friendlyUrlService->getUrlWithFilters(
                $categoryUrl,
                [$attributeCode => (string)$value]
            );
        } catch (\Exception $e) {
            return '';
        }
    }
}
