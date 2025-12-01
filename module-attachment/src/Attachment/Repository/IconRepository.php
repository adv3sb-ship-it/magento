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
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Api\Data\IconInterfaceFactory;
use Mirasvit\Attachment\Model\ResourceModel\Icon\Collection;
use Mirasvit\Attachment\Model\ResourceModel\Icon\CollectionFactory;

class IconRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        IconInterfaceFactory $factory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(): IconInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?IconInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function delete(IconInterface $model): void
    {
        $this->entityManager->delete($model);
    }

    public function save(IconInterface $model): IconInterface
    {
        return $this->entityManager->save($model);
    }
}
