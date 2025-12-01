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

namespace Mirasvit\Attachment\Model\ResourceModel\Attachment;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    private $customerSession;

    private $storeManager;

    public function __construct(
        StoreManagerInterface  $storeManager,
        CustomerSession        $customerSession,
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    ) {
        $this->storeManager    = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function addCustomerFilter(): Collection
    {
        $groupId = $this->customerSession->isLoggedIn()
            ? (int)$this->customerSession->getCustomer()->getGroupId()
            : 0;
        $this->addFieldToFilter(AttachmentInterface::CUSTOMER_GROUP_IDS, ['finset' => $groupId]);

        return $this;
    }

    public function addStoreFilter(): Collection
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->addFieldToFilter(
            [AttachmentInterface::STORE_IDS, AttachmentInterface::STORE_IDS],
            [
                ['finset' => 0],
                ['finset' => $storeId],
            ]
        );

        return $this;
    }

    protected function _construct(): void
    {
        $this->_init(Model\Attachment::class, Model\ResourceModel\Attachment::class);
    }
}
