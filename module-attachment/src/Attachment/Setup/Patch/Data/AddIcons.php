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



namespace Mirasvit\Attachment\Setup\Patch\Data;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\IconRepository;

class AddIcons implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    private $filesystem;

    private $moduleDirReader;

    private $iconRepository;

    private $configProvider;


    public function __construct(
        ModuleDataSetupInterface $setup,
        ModuleDirReader          $moduleDirReader,
        Filesystem               $filesystem,
        IconRepository           $iconRepository,
        ConfigProvider           $configProvider
    ) {
        $this->setup           = $setup;
        $this->moduleDirReader = $moduleDirReader;
        $this->filesystem      = $filesystem;
        $this->iconRepository  = $iconRepository;
        $this->configProvider  = $configProvider;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        //this script does not overwrite existing data
        $this->setup->getConnection()->startSetup();
        $installer = $this->setup;
        $icons     = [
            'doc.png'  => ['doc'],
            'docx.png' => ['docx'],
            'exe.png'  => ['exe'],
            'file.png' => [],
            'html.png' => ['html', 'htm'],
            'jpg.png'  => ['jpg', 'jpeg'],
            'mov.png'  => ['mov', 'avi'],
            'pdf.png'  => ['pdf'],
            'png.png'  => ['png'],
            'ppt.png'  => ['ppt', 'pptx'],
            'rar.png'  => ['rar'],
            'txt.png'  => ['txt'],
            'xlsx.png' => ['xlsx'],
            'zip.png'  => ['zip'],
            'url.png'  => ['url'],
        ];

        $setupDir   = $this->moduleDirReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_SETUP_DIR, 'Mirasvit_Attachment');
        $srcPath    = implode(DIRECTORY_SEPARATOR, [$setupDir, 'Patch', 'Data', 'data', 'icon']);
        $driverFile = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        $dstPath    = $this->configProvider->getAbsPath(ConfigProvider::ICON_DIR);

        foreach ($icons as $filename => $types) {
            $srcFile = $srcPath . DIRECTORY_SEPARATOR . $filename;
            $dstFile = $dstPath . DIRECTORY_SEPARATOR . $filename;
            if (!$driverFile->isExists($dstFile)) {
                $driverFile->copy($srcFile, $dstFile);
            }

            $label = ucfirst(explode('.', $filename)[0]);

            $icon = $this->iconRepository->create();
            $icon->setIsActive(true)
                ->setTypes($types)
                ->setLabel($label)
                ->setIconPath(ConfigProvider::ICON_DIR . '/' . $filename);
            $this->iconRepository->save($icon);
        }
        $this->setup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
