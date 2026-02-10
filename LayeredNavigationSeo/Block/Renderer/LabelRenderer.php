<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Block\Renderer;

use Mirasvit\LayeredNavigation\Block\Renderer\LabelRenderer as MirasvitLabelRenderer;
use Magento\Catalog\Model\Layer\Filter\Item;
use Triniti\LayeredNavigationSeo\Service\SeoUrlService;
use Triniti\LayeredNavigationSeo\Model\Config;
use Mirasvit\LayeredNavigation\Service\FilterService;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Model\Config\HighlightConfigProvider;
use Magento\Swatches\Helper\Media as MediaHelper;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Magento\Framework\View\Element\Template\Context;

class LabelRenderer extends MirasvitLabelRenderer
{
    private $seoUrlService;
    private $config;

    public function __construct(
        FilterService $filterService,
        ConfigProvider $configProvider,
        HighlightConfigProvider $highlightConfigProvider,
        MediaHelper $mediaHelper,
        SeoConfigProvider $seoConfigProvider,
        Context $context,
        
        SeoUrlService $seoUrlService,
        Config $config,
        
        array $data = []
    ) {
        $this->seoUrlService = $seoUrlService;
        $this->config = $config;

        parent::__construct(
            $filterService,
            $configProvider,
            $highlightConfigProvider,
            $mediaHelper,
            $seoConfigProvider,
            $context,
            $data
        );
    }

    public function getFilterData(Item $filterItem): array
    {
        $relAttribute = $this->getRelAttributeValue();

        $urlData = $this->seoUrlService->getFilterUrls($filterItem);
        
        return [
            'label'     => $filterItem->getLabel(),
            'count'     => $filterItem->getCount(),
            'url'       => $urlData['href'],
            'data_url'  => $urlData['data_url'],
            'is_checked'=> $this->isFilterItemChecked($filterItem), 
            'rel'       => $relAttribute,
        ];
    }
}