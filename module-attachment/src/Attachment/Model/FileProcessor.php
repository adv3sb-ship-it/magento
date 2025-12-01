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

namespace Mirasvit\Attachment\Model;

use Magento\MediaStorage\Model\File\UploaderFactory;

class FileProcessor
{
    private $uploaderFactory;

    private $config;

    public function __construct(
        ConfigProvider $config,
        UploaderFactory $uploaderFactory
    ) {
        $this->config          = $config;
        $this->uploaderFactory = $uploaderFactory;
    }

    public function saveIcon(string $fileId): array
    {
        $destination = $this->config->getAbsPath(ConfigProvider::ICON_DIR);

        return $this->save($fileId, $destination);
    }

    public function saveAttachment(string $fileId): array
    {
        $destination = $this->config->getAbsPath(ConfigProvider::ATTACHMENT_DIR);

        return $this->save($fileId, $destination);
    }

    private function save(string $fileId, string $destination): array
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        return $uploader->save($destination);
    }
}
