<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED        = 'triniti_seo_indexer/layered_navigation_seo/enabled';
    private const XML_PATH_ALL_CATEGORIES = 'triniti_seo_indexer/layered_navigation_seo/all_categories';
    private const XML_PATH_CATEGORIES     = 'triniti_seo_indexer/layered_navigation_seo/categories';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isAllCategoriesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ALL_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllowedCategories(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );

        if (!$value) {
            return [];
        }

        return explode(',', $value);
    }

    public function isCategoryAllowed(int $categoryId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if ($this->isAllCategoriesEnabled()) {
            return true;
        }

        $allowed = $this->getAllowedCategories();
        
        if (empty($allowed)) {
            return false; 
        }

        return in_array((string)$categoryId, $allowed, true);
    }
}