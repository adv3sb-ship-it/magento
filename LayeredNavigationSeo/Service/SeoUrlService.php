<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\LayeredNavigation\Service\FilterService;
use Mirasvit\SeoFilter\Service\FriendlyUrlService;
use Magento\Catalog\Model\Layer\Filter\Item;
use Triniti\LayeredNavigationSeo\Model\Config;
use Mirasvit\Brand\Registry as BrandRegistry;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Brand\Model\BrandFactory; 

class SeoUrlService
{
    private $filterService;
    private $registry;
    private $friendlyUrlService;
    private $resource;
    private $connection;
    private $storeManager;
    private $config;
    private $brandRegistry;
    private $request;
    private $brandFactory;

    private $runtimeCache = [];

    private $ignoredAttributes = [
        'price',
        'rating',
        'cat',
        'stock',
        'availability',
        'mst_stock', 
        'on_sale',
        'mst_on_sale'
    ];

    private $ignoredQueryParams = [
        'p',
        'product_list_order',
        'product_list_limit',
        'product_list_mode',
        'product_list_dir'
    ];

    public function __construct(
        FilterService $filterService,
        Registry $registry,
        FriendlyUrlService $friendlyUrlService,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        Config $config,
        BrandRegistry $brandRegistry,
        RequestInterface $request,
        BrandFactory $brandFactory
    ) {
        $this->filterService      = $filterService;
        $this->registry           = $registry;
        $this->friendlyUrlService = $friendlyUrlService;
        $this->resource           = $resource;
        $this->connection         = $resource->getConnection();
        $this->storeManager       = $storeManager;
        $this->config             = $config;
        $this->brandRegistry      = $brandRegistry;
        $this->request            = $request;
        $this->brandFactory       = $brandFactory;
    }

    private function getCurrentBrand()
    {
        $brand = $this->brandRegistry->getBrand();
        if ($brand) {
            return $brand;
        }

        if ($this->request->getFullActionName() === 'brand_brand_view') {
            $brandId = $this->request->getParam('id');
            if ($brandId) {
                $cacheKey = 'loaded_brand_' . $brandId;
                if (isset($this->runtimeCache[$cacheKey])) {
                    return $this->runtimeCache[$cacheKey];
                }

                try {
                    /** @var \Mirasvit\Brand\Model\Brand $brandModel */
                    $brandModel = $this->brandFactory->create();
                    $brandModel->load($brandId);
                    
                    if ($brandModel->getId()) {
                        $this->runtimeCache[$cacheKey] = $brandModel;
                        return $brandModel;
                    }
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    private function isSeoLogicEnabled(): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        if ($this->config->isAllCategoriesEnabled()) {
            return true;
        }

        if ($this->getCurrentBrand()) {
            return true;
        }

        $category = $this->registry->registry('current_category');
        if (!$category) {
            return false;
        }

        return $this->config->isCategoryAllowed((int)$category->getId());
    }

    public function getFilterUrls(Item $filterItem): array
    {
        if (!$this->isSeoLogicEnabled()) {
            return [
                'href'     => $filterItem->getUrl(),
                'data_url' => null 
            ];
        }

        $code = $filterItem->getFilter()->getRequestVar();
        $value = (string)$filterItem->getValueString();

        // 1. DATA-URL
        $dataUrl = $filterItem->getUrl();

        if (in_array($code, $this->ignoredAttributes)) {
            return ['href' => '#', 'data_url' => $dataUrl];
        }

        // 2. HREF
        $allParams = $this->getAllOptionParams([$code => $value]);
        $cleanParams = $this->cleanParamsForSeo($allParams);

        $href = '#';

        if (!$this->isNoindex($cleanParams, true)) {
            $href = $this->generateSeoUrl($cleanParams);
        } else {
            // Fallback
            $singleParams = $this->getSingleOptionParams($code, $value);
            $cleanSingle = $this->cleanParamsForSeo($singleParams);
            
            if (!$this->isNoindex($cleanSingle, true)) {
                $href = $this->generateSeoUrl($cleanSingle);
            }
        }

        return [
            'href'     => $href,
            'data_url' => $dataUrl
        ];
    }

    public function getRemoveFilterUrls(Item $filterItem): array
    {
        $removeUrl = $filterItem->getRemoveUrl();

        if (!$this->isSeoLogicEnabled()) {
             return [
                'href'     => $removeUrl,
                'data_url' => null
            ];
        }

        $code = $filterItem->getFilter()->getRequestVar();
        $value = (string)$filterItem->getValueString();
        
        if (in_array($code, $this->ignoredAttributes)) {
             return [
                'href'     => '#',
                'data_url' => $removeUrl
            ];
        }

        $selfParams = $this->getSingleOptionParams($code, $value);
        $cleanSelf = $this->cleanParamsForSeo($selfParams);

        if (!$this->isNoindex($cleanSelf, true)) {
            $selfUrl = $this->generateSeoUrl($cleanSelf);
            return [
                'href'     => $selfUrl,
                'data_url' => $removeUrl
            ];
        }

        return [
            'href'     => '#',
            'data_url' => $removeUrl
        ];
    }

    public function isNoindex(array $paramsToCheck = [], bool $exactParams = false): bool
    {
        try {
            $paramsToCheck = $this->cleanParamsForSeo($paramsToCheck);

            if (empty($paramsToCheck)) return false;

            $generatedUrl = $this->generateSeoUrl($paramsToCheck);
            if ($generatedUrl) {
                $path = parse_url($generatedUrl, PHP_URL_PATH);
                $rewriteRobots = $this->getContentRewriteRobots($path);
                if ($rewriteRobots !== false) {
                    if (stripos($rewriteRobots, 'NOINDEX') !== false) return true;
                    if (stripos($rewriteRobots, 'INDEX') !== false) return false;
                }
            }

            $optionIds = $this->extractOptionIds($paramsToCheck);
            $landingRobots = $this->getLandingPageRobots($optionIds);
            if ($landingRobots !== false) {
                 if (stripos($landingRobots, 'NOINDEX') !== false) return true;
                 if (stripos($landingRobots, 'INDEX') !== false) return false;
            }

            if (count($paramsToCheck) === 1) {
                return false;
            }
            return true;

        } catch (\Exception $e) {
            return true;
        }
    }

    // --- HELPER METHODS ---

    private function cleanParamsForSeo(array $params): array
    {
        foreach ($this->ignoredAttributes as $attr) {
            if (isset($params[$attr])) {
                unset($params[$attr]);
            }
        }
        return $params;
    }

    private function generateSeoUrl(array $params): string
    {
        try {
            $brand = $this->getCurrentBrand();
            
            if ($brand) {
                $baseUrl = $brand->getUrl();
            } else {
                $category = $this->registry->registry('current_category');
                if (!$category) return '';
                $baseUrl = $category->getUrl();
            }
            
            $url = $this->friendlyUrlService->getUrlWithFilters($baseUrl, $params);

            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                foreach ($this->ignoredQueryParams as $ignore) {
                    if (isset($queryParams[$ignore])) {
                        unset($queryParams[$ignore]);
                    }
                }
                
                $scheme   = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
                $host     = isset($parsed['host']) ? $parsed['host'] : '';
                $path     = isset($parsed['path']) ? $parsed['path'] : '';
                $newQuery = http_build_query($queryParams);
                
                $url = $scheme . $host . $path;
                if (!empty($newQuery)) {
                    $url .= '?' . $newQuery;
                }
            }
            
            return $url;

        } catch (\Exception $e) {
            return '';
        }
    }

    // --- DATA METHODS ---

    public function getSingleFilterUrl(string $attributeCode, $value): string
    {
        return $this->generateSeoUrl([$attributeCode => (string)$value]);
    }

    private function generateUrlForParams(array $params): string
    {
        return $this->generateSeoUrl($params);
    }

    private function getAllOptionParams(array $additionalParams = []): array
    {
        $params = [];
        foreach ($this->filterService->getActiveFilters() as $item) {
            $code = $item->getFilter()->getRequestVar();
            if (in_array($code, $this->ignoredAttributes)) continue;
            $this->appendParam($params, $code, (string)$item->getValueString());
        }
        foreach ($additionalParams as $code => $value) {
            if (in_array($code, $this->ignoredAttributes)) continue;
            $this->appendParam($params, $code, (string)$value);
        }
        return $this->prepareParamsForFriendlyUrl($params);
    }

    private function getSingleOptionParams(string $code, string $value): array
    {
        $params = [];
        if (!in_array($code, $this->ignoredAttributes)) {
            $this->appendParam($params, $code, $value);
        }
        return $this->prepareParamsForFriendlyUrl($params);
    }

    private function appendParam(array &$params, string $code, string $value): void
    {
        if (!isset($params[$code])) $params[$code] = [];
        if (!in_array($value, $params[$code])) $params[$code][] = $value;
    }

    private function prepareParamsForFriendlyUrl(array $rawParams): array
    {
        $final = [];
        foreach ($rawParams as $code => $values) {
            if (is_array($values)) {
                if (empty($values)) continue;
                $final[$code] = (count($values) === 1) ? (string)reset($values) : implode(',', $values);
            } else {
                $final[$code] = (string)$values;
            }
        }
        return $final;
    }

    private function extractOptionIds(array $params): array
    {
        $ids = [];
        foreach ($params as $key => $value) {
            if (is_string($value) && strpos($value, ',') !== false) {
                foreach (explode(',', $value) as $v) $ids[] = trim($v);
            } else {
                $ids[] = (string)$value;
            }
        }
        return array_unique($ids);
    }

    // --- DB METHODS (Cached) ---

    private function getContentRewriteRobots($path)
    {
        if (!$path) return false;

        $cacheKey = 'rewrite_' . md5($path);
        if (array_key_exists($cacheKey, $this->runtimeCache)) {
            return $this->runtimeCache[$cacheKey];
        }

        $tableName = $this->resource->getTableName('mst_seo_content_rewrite');
        if (!$this->connection->isTableExists($tableName)) {
            $this->runtimeCache[$cacheKey] = false;
            return false;
        }

        $storeId = (int)$this->storeManager->getStore()->getId();
        $pathTrimmed = rtrim($path, '/');
        $pathSlash   = $pathTrimmed . '/';
        
        $select = $this->connection->select()
            ->from($tableName, ['meta_robots'])
            ->where('is_active = 1')
            ->where('url IN (?)', [$pathTrimmed, $pathSlash])
            ->where('(store_ids = 0 OR FIND_IN_SET(?, store_ids))', $storeId)
            ->limit(1);
        
        $result = $this->connection->fetchOne($select);
        $this->runtimeCache[$cacheKey] = $result;

        return $result;
    }

    private function getLandingPageRobots(array $optionIds)
    {
        if (empty($optionIds)) return false;
        
        sort($optionIds);
        $cacheKey = 'landing_' . implode('_', $optionIds);
        
        if (array_key_exists($cacheKey, $this->runtimeCache)) {
            return $this->runtimeCache[$cacheKey];
        }

        $count = count($optionIds);
        $storeId = (int)$this->storeManager->getStore()->getId();
        $tablePage   = $this->resource->getTableName('mst_landing_page');
        $tableFilter = $this->resource->getTableName('mst_landing_page_filter');
        $tableStore  = $this->resource->getTableName('mst_landing_page_store');

        if (!$this->connection->isTableExists($tablePage) || !$this->connection->isTableExists($tableStore)) {
             $this->runtimeCache[$cacheKey] = false;
             return false;
        }

        $select = $this->connection->select()
            ->from(['p' => $tablePage], [])
            ->join(['s' => $tableStore], 'p.page_id = s.page_id', ['meta_robots'])
            ->join(['f' => $tableFilter], 'p.page_id = f.page_id', [])
            ->where('p.is_active = 1')
            ->where('(s.store_id = ? OR s.store_id = 0)', $storeId)
            ->where('f.option_id IN (?)', $optionIds)
            ->group(['p.page_id', 's.meta_robots'])
            ->having('COUNT(DISTINCT f.option_id) = ?', $count)
            ->order('s.store_id DESC')
            ->limit(1);
        
        $res = $this->connection->fetchOne($select);
        if (!$res && $this->connection->tableColumnExists($tableStore, 'robots')) {
             $select->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('s.robots');
             $res = $this->connection->fetchOne($select);
        }

        $this->runtimeCache[$cacheKey] = $res;
        return $res;
    }
}