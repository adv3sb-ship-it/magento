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

namespace Mirasvit\Attachment\Ui\Attachment\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\AttachmentRepository;
use Mirasvit\Attachment\Repository\IconRepository;
use Mirasvit\Attachment\Service\IconService;

class Thumbnail extends Column
{
    private   $configProvider;

    protected $iconRepository;

    protected $attachmentRepository;

    protected $iconService;

    public function __construct(
        ConfigProvider $configProvider,
        IconRepository $iconRepository,
        AttachmentRepository $attachmentRepository,
        IconService $iconService,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->configProvider       = $configProvider;
        $this->iconRepository       = $iconRepository;
        $this->iconService          = $iconService;
        $this->attachmentRepository = $attachmentRepository;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $iconId = (int)$item['icon_id'];
                $icon   = $this->iconRepository->get($iconId);
                if (!$icon) {
                    $attachmentId = $iconId = (int)$item['attachment_id'];
                    $attachment   = $this->attachmentRepository->get($attachmentId);
                    if ($attachment) {
                        $icon = $this->iconService->getIcon($attachment);
                    }
                }
                if ($icon) {
                    $item[$fieldName . '_src']      = $this->configProvider->getAbsUrl($icon->getIconPath());
                    $item[$fieldName . '_alt']      = $item['label'];
                    $item[$fieldName . '_orig_src'] = $this->configProvider->getAbsUrl($icon->getIconPath());
                }
            }
        }

        return $dataSource;
    }
}
