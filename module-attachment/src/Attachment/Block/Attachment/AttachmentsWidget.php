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

namespace Mirasvit\Attachment\Block\Attachment;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model\Config\Source\DefaultSortDirection;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Model\ResourceModel\Attachment\Collection;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Service\AttachmentService;

class AttachmentsWidget extends Template implements BlockInterface, IdentityInterface
{
    protected $_template = "attachment/attachments_widget.phtml";

    private $attachmentRepository;

    private $attachmentService;

    private $configProvider;

    public function __construct(
        AttachmentRepository $attachmentRepository,
        AttachmentService    $attachmentService,
        ConfigProvider       $configProvider,
        Template\Context     $context,
        array                $data = []
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->attachmentService    = $attachmentService;
        $this->configProvider       = $configProvider;
        parent::__construct($context, $data);
    }

    public function getAttachmentIds(): array
    {
        $ids = explode(',', (string)$this->getData('attachment_ids'));

        return $ids ? $ids : [];
    }

    public function getAttachmentCollection(): Collection
    {
        $defaultSortDirection = $this->configProvider->getDefaultSortOrder() === DefaultSortDirection::OPTION_NEWEST
            ? 'DESC'
            : 'ASC';

        return $this->attachmentRepository->getCollection()
            ->addCustomerFilter()
            ->addStoreFilter()
            ->addFieldToFilter(AttachmentInterface::IS_ACTIVE, true)
            ->addFieldToFilter(AttachmentInterface::ID, ['in', $this->getAttachmentIds()])
            ->addOrder(AttachmentInterface::POSITION, 'ASC')
            ->addOrder(AttachmentInterface::ID, $defaultSortDirection);
    }

    public function getAttachmentList(): array
    {
        $result = [];
        foreach ($this->getAttachmentCollection() as $attachment) {
            $result[] = $attachment;
        }

        return $result;
    }

    public function getAttachmentListHtml(): ?string
    {
        $attachments = $this->getAttachmentList();
        if (!$attachments) {
            return null;
        }

        $title = $this->getData('title');
        $html  = $title ? "<h3>{$title}</h3>" : '';

        /** @var \Mirasvit\Attachment\Block\AttachmentList $attachmentListBlock */
        $attachmentListBlock = $this->getLayout()
            ->createBlock(\Mirasvit\Attachment\Block\AttachmentList::class)
            ->setTemplate('Mirasvit_Attachment::list.phtml');
        $attachmentListBlock->setAttachmentList($this->getAttachmentList());
        $html .= $attachmentListBlock->toHtml();

        return $html;
    }

    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getAttachmentList() as $item) {
            $identities = array_merge($identities, $this->attachmentService->getIdentities($item));
        }

        return $identities;
    }
}
