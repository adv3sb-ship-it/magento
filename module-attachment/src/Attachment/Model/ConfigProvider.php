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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider
{
    const BASE_DIR = 'attachment';

    const ICON_DIR        = 'icon';
    const ICON_FIELD_NAME = 'icon';

    const ATTACHMENT_DIR        = 'attachment';
    const ATTACHMENT_FIELD_NAME = 'attachment';

    private $scopeConfig;

    private $filesystem;

    private $storeManager;

    public function __construct(
        ScopeConfigInterface  $scopeConfig,
        Filesystem            $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->filesystem   = $filesystem;
    }

    public function getAbsUrl(string $filePath = ''): string
    {
        $url = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::BASE_DIR;

        if ($filePath) {
            $url .= '/' . $filePath;
        }

        return $url;
    }

    public function getAbsPath(string $filePath = ''): string
    {
        $path = $this->filesystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(self::BASE_DIR);

        $this->ensureDirs();

        if ($filePath) {
            $path .= DIRECTORY_SEPARATOR . $filePath;
        }

        return $path;
    }

    public function isProductTabEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_attachment/product_tab/is_enabled');
    }

    public function getProductTabLabel(): string
    {
        return (string)$this->scopeConfig->getValue('mst_attachment/product_tab/label');
    }

    public function getProductTabPosition(): int
    {
        return (int)$this->scopeConfig->getValue('mst_attachment/product_tab/position');
    }

    private function ensureDirs(): void
    {
        $this->ensureDir(self::BASE_DIR . DIRECTORY_SEPARATOR . self::ICON_DIR);
        $this->ensureDir(self::BASE_DIR . DIRECTORY_SEPARATOR . self::ATTACHMENT_DIR);
    }

    private function ensureDir(string $path): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->isExist($path)) {
            if (!$mediaDirectory->isDirectory($path)) {
                throw new \Exception($path . ' not a directory.');
            }

            if (!$mediaDirectory->isWritable($path)) {
                throw new \Exception($path . ' is not writable.');
            }
        } else {
            if (!$mediaDirectory->create($path)) {
                throw new \Exception('Error creating directory ' . $path);
            }
            if (!$mediaDirectory->changePermissions($path, 0777)) {
                throw new \Exception('Error changing ' . $path . 'directory permissions');
            }
        }
    }

    public function getDefaultSortOrder(): string
    {
        return (string)$this->scopeConfig->getValue('mst_attachment/general/default_order');
    }
}
