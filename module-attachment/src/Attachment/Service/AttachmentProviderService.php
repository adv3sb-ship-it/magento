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

namespace Mirasvit\Attachment\Service;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Controller\Registry;
use Mirasvit\Attachment\Model\Config\Source\DefaultSortDirection;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\AttachmentRepository;

class AttachmentProviderService
{
    private $registry;

    private $attachmentRepository;

    private $configProvider;

    private $resource;

    public function __construct(
        ResourceConnection   $resource,
        Registry             $registry,
        AttachmentRepository $attachmentRepository,
        ConfigProvider       $configProvider
    ) {
        $this->resource             = $resource;
        $this->registry             = $registry;
        $this->attachmentRepository = $attachmentRepository;
        $this->configProvider       = $configProvider;
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getList(): array
    {
        ['type' => $type, 'pk' => $pk] = $this->getContext();
        if (!$type || !$pk) {
            return [];
        }

        $defaultSortOrder     = $this->configProvider->getDefaultSortOrder();
        $defaultSortDirection = $defaultSortOrder === DefaultSortDirection::OPTION_NEWEST
            ? 'DESC'
            : 'ASC';

        $collection = $this->attachmentRepository->getCollection()
            ->addCustomerFilter()
            ->addStoreFilter()
            ->addFieldToFilter(AttachmentInterface::IS_ACTIVE, true)
            ->join(['link' => $this->resource->getTableName(LinkInterface::TABLE_NAME)],
                'main_table.' . AttachmentInterface::ID . ' = link.' . LinkInterface::ATTACHMENT_ID)
            ->addFieldToFilter('link.' . LinkInterface::ENTITY_TYPE, $type)
            ->addFieldToFilter('link.' . LinkInterface::ENTITY_PK_VALUE, $pk)
            ->addOrder('link.' . LinkInterface::POSITION, 'ASC')
            ->addOrder('main_table.' . AttachmentInterface::POSITION, 'ASC')
            ->addOrder('main_table.' . AttachmentInterface::ID, $defaultSortDirection);

        return $collection->getItems();
    }

    private function getContext(): array
    {
        $productId  = $this->registry->getProductId();
        $pageId     = $this->registry->getCmsPageId();
        $categoryId = $this->registry->getCategoryId();
        if ($productId) {
            return ['type' => LinkInterface::ENTITY_TYPE_PRODUCT, 'pk' => $productId];
        } elseif ($categoryId) {
            return ['type' => LinkInterface::ENTITY_TYPE_CATEGORY, 'pk' => $categoryId];
        } elseif ($pageId) {
            return ['type' => LinkInterface::ENTITY_TYPE_CMS_PAGE, 'pk' => $pageId];
        }

        return ['type' => null, 'pk' => null];
    }
}
