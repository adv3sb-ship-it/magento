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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Repository\AttachmentRepository;

abstract class AbstractAttachment extends Action
{
    protected $attachmentRepository;

    private   $context;

    public function __construct(
        AttachmentRepository $attachmentRepository,
        Context $context
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->context              = $context;

        parent::__construct($context);
    }

    protected function initModel(): AttachmentInterface
    {
        $model = null;

        $id = (int)$this->getRequest()->getParam(AttachmentInterface::ID);
        if ($id) {
            $model = $this->attachmentRepository->get($id);
        }

        return $model ? $model : $this->attachmentRepository->create();
    }

    protected function initPage(Page $page, string $title): void
    {
        $page->getConfig()->getTitle()->prepend((string)__($title));
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Attachment::manage_attachment');
    }
}
