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

namespace Mirasvit\Attachment\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\AttachmentInterfaceFactory;
use Mirasvit\Attachment\Model\ResourceModel\Attachment\Collection;
use Mirasvit\Attachment\Model\ResourceModel\Attachment\CollectionFactory;
use Mirasvit\Attachment\Service\AttachmentService;

class AttachmentRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    private $cache;

    private $attachmentService;

    public function __construct(
        PageCache $cache,
        AttachmentService $attachmentService,
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        AttachmentInterfaceFactory $factory
    ) {
        $this->cache             = $cache;
        $this->attachmentService = $attachmentService;
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(): AttachmentInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?AttachmentInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function delete(AttachmentInterface $model): void
    {
        $this->cleanFullPageCache($model);
        $this->entityManager->delete($model);
    }

    public function save(AttachmentInterface $model): AttachmentInterface
    {
        $this->cleanFullPageCache($model);

        return $this->entityManager->save($model);
    }

    private function cleanFullPageCache(AttachmentInterface $model): void
    {
        $cacheTags = $this->attachmentService->getIdentities($model);
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $cacheTags);
    }
}
