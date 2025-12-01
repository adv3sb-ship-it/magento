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

namespace Mirasvit\Attachment\Controller;

use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry as GenericRegistry;
use Magento\Store\Model\ScopeInterface;

class Registry
{
    private $pageInterfaceFactory;

    private $registry;

    private $request;

    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PageInterfaceFactory $pageInterfaceFactory,
        GenericRegistry $registry,
        RequestInterface $request
    ) {
        $this->scopeConfig          = $scopeConfig;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->registry             = $registry;
        $this->request              = $request;
    }

    public function getProductId(): ?int
    {
        return $this->registry->registry('current_product')
            ? (int)$this->registry->registry('current_product')->getId()
            : null;
    }

    public function getCmsPageId(): ?int
    {
        $fullActionName = (string)$this->request->getFullActionName();

        switch ($fullActionName) {
            case 'cms_noroute_index':
                $pageId = $this->scopeConfig->getValue(Page::XML_PATH_NO_ROUTE_PAGE, ScopeInterface::SCOPE_STORE); // no-route
                $page   = $this->pageInterfaceFactory->create();
                $page->load($pageId);
                $id = (int)$page->getId();

                return $id ? $id : null;

            case 'cms_index_index':
                $pageId = $this->scopeConfig->getValue(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE); // home
                $page   = $this->pageInterfaceFactory->create();
                $page->load($pageId);
                $id = (int)$page->getId();

                return $id ? $id : null;

            case 'cms_page_view':
                return $this->request->getParam('page_id')
                    ? (int)$this->request->getParam('page_id')
                    : null;
        }

        return null;
    }

    public function getCategoryId(): ?int
    {
        return $this->registry->registry('current_category')
            ? (int)$this->registry->registry('current_category')->getId()
            : null;
    }
}
