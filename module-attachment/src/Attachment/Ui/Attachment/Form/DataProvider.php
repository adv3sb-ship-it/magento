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

namespace Mirasvit\Attachment\Ui\Attachment\Form;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\File\Mime as FileMime;
use Magento\Framework\Filesystem;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\CategoryModifier;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\CmsPageModifier;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\ProductModifier;

class DataProvider extends AbstractDataProvider
{
    private $config;

    private $fileMime;

    private $filesystem;

    private $attachmentRepository;

    private $categoryModifier;

    private $cmsPageModifier;

    private $productModifier;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigProvider       $config,
        FileMime             $fileMime,
        Filesystem           $filesystem,
        AttachmentRepository $attachmentRepository,
        CategoryModifier     $categoryModifier,
        CmsPageModifier      $cmsPageModifier,
        ProductModifier      $productModifier,


        string               $name,
        string               $primaryFieldName,
        string               $requestFieldName,
        array                $meta = [],
        array                $data = []
    ) {
        $this->config               = $config;
        $this->fileMime             = $fileMime;
        $this->filesystem           = $filesystem;
        $this->attachmentRepository = $attachmentRepository;
        $this->categoryModifier     = $categoryModifier;
        $this->cmsPageModifier      = $cmsPageModifier;
        $this->productModifier      = $productModifier;
        $this->collection           = $this->attachmentRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $meta = $this->productModifier->modifyMeta($meta);
        $meta = $this->categoryModifier->modifyMeta($meta);
        $meta = $this->cmsPageModifier->modifyMeta($meta);

        return $meta;
    }

    public function getData(): array
    {
        $result = [];

        /** @var AttachmentInterface|DataObject $item */
        foreach ($this->collection as $item) {
            $result[$item->getId()] = [
                AttachmentInterface::ID                 => $item->getId(),
                AttachmentInterface::IS_ACTIVE          => $item->isActive() ? "1" : "0",
                AttachmentInterface::TYPE               => $item->getType(),
                AttachmentInterface::SOURCE_NAME        => $item->getSourceName(),
                AttachmentInterface::FILE_PATH          => $item->getFilePath(),
                AttachmentInterface::FILE_TYPE          => $item->getFileType(),
                AttachmentInterface::LABEL              => $item->getLabel(),
                AttachmentInterface::POSITION           => $item->getPosition(),
                AttachmentInterface::ICON_ID            => $item->getIconId(),
                AttachmentInterface::CUSTOMER_GROUP_IDS => $item->getCustomerGroupIds(),
                AttachmentInterface::STORE_IDS          => $item->getStoreIds(),
                ConfigProvider::ATTACHMENT_FIELD_NAME   => $this->getFileData($item),
            ];
        }

        $result = $this->productModifier->modifyData($result);
        $result = $this->categoryModifier->modifyData($result);
        $result = $this->cmsPageModifier->modifyData($result);

        return $result;
    }

    private function getFileData(AttachmentInterface $attachment): array
    {
        if (!$attachment->getFilePath()) {
            return [];
        }

        $driverFile = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        $absPath    = $this->config->getAbsPath($attachment->getFilePath());
        if (!$driverFile->isReadable($absPath)) {
            return [];
        }
        $fileStat = $driverFile->stat($absPath);
        $fileSize = (int)$fileStat['size'];
        $mimeType = in_array(strtolower(pathinfo($absPath, PATHINFO_EXTENSION)), ['gif', 'png', 'jpg', 'jpeg'])
            ? 'image/*'
            : $this->fileMime->getMimeType($absPath);


        $fileData = [
            'name' => $attachment->getSourceName(),
            'url'  => $this->config->getAbsUrl($attachment->getFilePath()),
            'size' => $fileSize,
            'type' => $mimeType,
            'file' => $attachment->getSourceName(),
        ];

        return [$fileData];
    }
}
