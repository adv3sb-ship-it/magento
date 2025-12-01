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

namespace Mirasvit\Attachment\Plugin\Backend;

use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Service\AttachmentService;

/**
 * @see \Magento\Catalog\Controller\Adminhtml\Product\Save::execute()
 */
class AddAttachmentBeforeProductSavePlugin
{
    private $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject)
    {
        $data = $subject->getRequest()->getPostValue();

        $productId = (int)$subject->getRequest()->getParam('id');
        if (!$productId) {
            return;
        }

        $attachmentsData = $this->getAttachmentsData($data);
        $this->attachmentService->saveForEntity(LinkInterface::ENTITY_TYPE_PRODUCT, $productId, $attachmentsData);
    }

    private function getAttachmentsData(array $data): array
    {
        if (!array_key_exists('links', $data)) {
            return [];
        }

        $links = $data['links'];
        if (!is_array($links) || !array_key_exists('attachment', $links)) {
            return [];
        }

        $attachment = $links['attachment'];

        return is_array($attachment) ? $attachment : [];
    }
}
