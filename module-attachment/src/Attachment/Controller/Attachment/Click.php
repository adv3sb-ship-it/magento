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

namespace Mirasvit\Attachment\Controller\Attachment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\File\Mime as FileMime;
use Magento\Framework\Filesystem;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Service\AttachmentService;

class Click implements HttpGetActionInterface
{
    private $request;

    private $response;

    private $configProvider;

    private $attachmentService;

    private $attachmentRepository;

    private $resultForwardFactory;

    private $fileFactory;

    private $redirectFactory;

    private $fileMime;

    private $filesystem;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface     $request,
        ResponseInterface    $response,
        ConfigProvider       $configProvider,
        AttachmentService    $attachmentService,
        AttachmentRepository $attachmentRepository,
        ForwardFactory       $resultForwardFactory,
        RedirectFactory      $redirectFactory,
        FileMime             $fileMime,
        Filesystem           $filesystem,
        FileFactory          $fileFactory
    ) {
        $this->request              = $request;
        $this->response             = $response;
        $this->configProvider       = $configProvider;
        $this->attachmentService    = $attachmentService;
        $this->attachmentRepository = $attachmentRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory      = $redirectFactory;
        $this->fileMime             = $fileMime;
        $this->filesystem           = $filesystem;
        $this->fileFactory          = $fileFactory;
    }

    public function execute()
    {
        $id         = (int)$this->request->getParam(AttachmentInterface::ID);
        $attachment = $this->initModel();

        if ($id && !$attachment->getId()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        if ($attachment->getType() == AttachmentInterface::TYPE_FILE) {
            return $this->fileClickHandler($attachment);
        }

        if ($attachment->getType() == AttachmentInterface::TYPE_LINK) {
            return $this->linkClickHandler($attachment);
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }

    private function initModel(): AttachmentInterface
    {
        $model = null;
        $id    = (int)$this->request->getParam(AttachmentInterface::ID);
        if ($id) {
            $model = $this->attachmentRepository->get($id);
        }

        return $model ? $model : $this->attachmentRepository->create();
    }

    private function fileClickHandler(AttachmentInterface $attachment)
    {
        $driverFile = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        $filePath   = $this->configProvider->getAbsPath($attachment->getFilePath());
        if (!$driverFile->isReadable($filePath)) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $fileStat = $driverFile->stat($filePath);
        $fileSize = (int)$fileStat['size'];
        if (!$fileSize) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $this->attachmentService->saveHistory($attachment);

        $contentType  = $this->fileMime->getMimeType($filePath);
        $downloadName = $attachment->getLabel() . '.' . $attachment->getFileType();
        if (!$contentType) {
            $content = [
                'type'  => 'filename',
                'value' => $filePath,
            ];

            return $this->fileFactory->create($downloadName, $content);
        }

        $this->response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Disposition', 'inline; filename="' . $downloadName . '"', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $fileSize)
            ->setHeader('Last-Modified', date('r'), true);
        $this->response->sendHeaders();
        $this->response->setBody($driverFile->fileGetContents($filePath));

        return $this->response;
    }

    private function linkClickHandler(AttachmentInterface $attachment)
    {
        $url = $attachment->getSourceName();
        if (!$url) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $this->attachmentService->saveHistory($attachment);

        return $this->redirectFactory->create()->setUrl($url);
    }

}
