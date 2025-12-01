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

namespace Mirasvit\Attachment\Controller\Adminhtml\Attachment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\View\Layout;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Repository\LinkRepository;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\CategoryModifier;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\CmsPageModifier;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\ProductModifier;

class Save extends AbstractAttachment implements HttpPostActionInterface
{
    const ENTITY_TYPE_MAP
        = [
            ProductModifier::DATA_SCOPE_ENTITY  => LinkInterface::ENTITY_TYPE_PRODUCT,
            CategoryModifier::DATA_SCOPE_ENTITY => LinkInterface::ENTITY_TYPE_CATEGORY,
            CmsPageModifier::DATA_SCOPE_ENTITY  => LinkInterface::ENTITY_TYPE_CMS_PAGE,
        ];

    private $linkRepository;

    private $resultJsonFactory;

    private $layout;

    public function __construct(
        LinkRepository $linkRepository,
        ResultJsonFactory $resultJsonFactory,
        Layout $layout,
        AttachmentRepository $attachmentRepository,
        Context $context
    ) {
        $this->linkRepository    = $linkRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layout            = $layout;

        parent::__construct($attachmentRepository, $context);
    }

    public function execute()
    {
        $id             = $this->getRequest()->getParam(AttachmentInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getParams();

        if ($data) {
            $model = $this->initModel();

            if ($id && !$model) {
                $this->messageManager->addErrorMessage((string)__('This attachment no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model = $this->setFileFields($model);
            $model->setIsActive((bool)$data[AttachmentInterface::IS_ACTIVE])
                ->setType((string)$data[AttachmentInterface::TYPE])
                ->setLabel((string)$data[AttachmentInterface::LABEL])
                ->setPosition((int)$data[AttachmentInterface::POSITION])
                ->setCustomerGroupIds((array)$data[AttachmentInterface::CUSTOMER_GROUP_IDS])
                ->setStoreIds((array)$data[AttachmentInterface::STORE_IDS])
                ->setIconId((int)$data[AttachmentInterface::ICON_ID]
                    ? (int)$data[AttachmentInterface::ICON_ID]
                    : null);

            try {
                $this->attachmentRepository->save($model);

                if ($model->getId()) {
                    $this->saveLinks($model);
                }

                $this->messageManager->addSuccessMessage((string)__('Attachment was saved.'));

                if ($this->getRequest()->getParam('isAjax')) {
                    $block = $this->layout->getMessagesBlock();
                    $block->setMessages($this->messageManager->getMessages(true));

                    $resultJson = $this->resultJsonFactory->create();

                    $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
                        \Magento\Framework\Message\MessageInterface::TYPE_ERROR
                    );

                    return $resultJson->setData([
                        'messages'   => $block->getGroupedHtml(),
                        'error'      => $hasError,
                        'attachment' => [
                            AttachmentInterface::ID    => $model->getId(),
                            AttachmentInterface::LABEL => $model->getLabel(),
                        ],
                    ]);
                }

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [AttachmentInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [AttachmentInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    private function setFileFields(AttachmentInterface $model): AttachmentInterface
    {
        $data = $this->getRequest()->getParams();

        if ($data[AttachmentInterface::TYPE] == AttachmentInterface::TYPE_LINK) {
            $fileUrl = (string)$data[AttachmentInterface::SOURCE_NAME];
            $model->setSourceName($fileUrl)
                ->setFilePath('')
                ->setFileType('');
        }

        if ($data[AttachmentInterface::TYPE] == AttachmentInterface::TYPE_FILE) {
            $attachment = $this->getRequest()->getParam(ConfigProvider::ATTACHMENT_FIELD_NAME);
            if ($attachment && is_array($attachment)) {
                $fileName = (string)$attachment[0]['name'] ?? '';
                $file     = (string)$attachment[0]['file'] ?? '';
                $filePath = $fileName ? ConfigProvider::ATTACHMENT_DIR . '/' . $file : '';
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $model->setSourceName($fileName)
                    ->setFilePath($filePath)
                    ->setFileType($fileType);
            }
        }

        return $model;
    }

    private function saveLinks(AttachmentInterface $attachment): void
    {
        $links = $this->getRequest()->getParam('links');
        if (!$links) {
            $this->deleteLinks($attachment);

            return;
        }

        foreach (static::ENTITY_TYPE_MAP as $fieldName => $entityType) {
            if (!array_key_exists($fieldName, $links)) {
                $this->deleteLinks($attachment, $entityType);
                continue;
            }

            $entitiesData = $links[$fieldName];
            $entityIds    = [];
            foreach ($entitiesData as ['id' => $entityId]) {
                $entityIds[] = $entityId;
            }
            $entityPkValues = $this->getEntityPkValues($attachment, $entityType);

            // save new
            foreach ($entitiesData as ['id' => $entityId]) {
                $entityId = (int)$entityId;
                if (!in_array($entityId, $entityPkValues)) {
                    $link = $this->linkRepository->create()
                        ->setAttachmentId($attachment->getId())
                        ->setEntityType($entityType)
                        ->setEntityPkValue($entityId)
                        ->setPosition($attachment->getPosition());
                    $this->linkRepository->save($link);
                }
            }

            // delete
            foreach ($entityPkValues as $entityPkValue) {
                if (!in_array($entityPkValue, $entityIds)) {
                    $this->deleteLinks($attachment, $entityType, $entityPkValue);
                }
            }
        }
    }

    private function deleteLinks(AttachmentInterface $attachment, string $entityType = null, int $entityPkValue = null)
    {
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachment->getId());

        if ($entityType) {
            $linkCollection->addFieldToFilter(LinkInterface::ENTITY_TYPE, $entityType);
        }

        if ($entityPkValue) {
            $linkCollection->addFieldToFilter(LinkInterface::ENTITY_PK_VALUE, $entityPkValue);
        }

        foreach ($linkCollection as $link) {
            $this->linkRepository->delete($link);
        }
    }

    private function getEntityPkValues(AttachmentInterface $attachment, string $entityType): array
    {
        $linkCollection = $this->linkRepository->getCollection()
            ->addFieldToFilter(LinkInterface::ATTACHMENT_ID, $attachment->getId())
            ->addFieldToFilter(LinkInterface::ENTITY_TYPE, $entityType);

        $entityPkValues = [];
        /** @var LinkInterface $link */
        foreach ($linkCollection as $link) {
            $entityPkValues[] = $link->getEntityPkValue();
        }

        return $entityPkValues;
    }
}
