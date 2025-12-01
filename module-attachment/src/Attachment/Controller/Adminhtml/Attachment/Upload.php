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
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Model\FileProcessor;
use Mirasvit\Attachment\Repository\AttachmentRepository;

class Upload extends AbstractAttachment implements HttpPostActionInterface
{
    private $fileProcessor;

    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        FileProcessor $fileProcessor,
        AttachmentRepository $attachmentRepository,
        Context $context
    ) {
        $this->configProvider = $configProvider;
        $this->fileProcessor  = $fileProcessor;

        parent::__construct($attachmentRepository, $context);
    }

    public function execute()
    {
        $result = $this->fileProcessor->saveAttachment(ConfigProvider::ATTACHMENT_FIELD_NAME);

        if (is_array($result)) {
            $result['url'] = $this->configProvider->getAbsUrl(ConfigProvider::ATTACHMENT_DIR . '/' . $result['file']);
        }

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
