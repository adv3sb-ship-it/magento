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

namespace Mirasvit\Attachment\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Service\AttachmentProviderService;
use Mirasvit\Attachment\Service\AttachmentService;
use Mirasvit\Attachment\Service\IconService;

class AttachmentList extends Template implements IdentityInterface
{
    private $attachmentProviderService;

    private $iconService;

    private $configProvider;

    private $attachmentList;

    private $attachmentService;

    public function __construct(
        AttachmentProviderService $attachmentProviderService,
        AttachmentService $attachmentService,
        IconService $iconService,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->attachmentProviderService = $attachmentProviderService;
        $this->attachmentService         = $attachmentService;
        $this->iconService               = $iconService;
        $this->configProvider            = $configProvider;

        parent::__construct($context, $data);
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachmentList(): array
    {
        if ($this->attachmentList) {
            return $this->attachmentList;
        }

        return $this->attachmentProviderService->getList();
    }

    public function getClickHandlerUrl(AttachmentInterface $attachment): string
    {
        return $this->getUrl('mst_attachment/attachment/click', [AttachmentInterface::ID => $attachment->getId()]);
    }

    public function getLabel(AttachmentInterface $attachment): string
    {
        return $attachment->getLabel();
    }

    public function getSize(AttachmentInterface $attachment): ?string
    {
        if ($attachment->getFileType() === AttachmentInterface::TYPE_LINK) {
            return null;
        }

        $absPath = $this->configProvider->getAbsPath($attachment->getFilePath());

        if (file_exists($absPath)) {
            $size = filesize($absPath);

            $kb = 1024;
            $mb = 1024 * $kb;

            if ($size >= $mb) {
                return sprintf('%.2f MB', $size / $mb);
            } elseif ($size >= $kb) {
                return sprintf('%.2f KB', $size / $kb);
            } else {
                return sprintf('%.2f B', $size);
            }
        }

        return null;
    }

    public function getIconUrl(AttachmentInterface $attachment): ?string
    {
        $icon = $this->iconService->getIcon($attachment);

        if ($icon) {
            return $this->configProvider->getAbsUrl($icon->getIconPath());
        }

        return null;
    }

    public function setAttachmentList(array $attachmentList)
    {
        $this->attachmentList = $attachmentList;
    }

    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getAttachmentList() as $item) {
            $identities = array_merge($identities, $this->attachmentService->getIdentities($item));
        }

        return $identities;
    }
}
