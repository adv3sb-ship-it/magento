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

namespace Mirasvit\Attachment\Service;

use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Repository\IconRepository;

class IconService
{
    private $iconRepository;

    public function __construct(IconRepository $iconRepository)
    {
        $this->iconRepository = $iconRepository;
    }

    public function getIcon(AttachmentInterface $attachment): ?IconInterface
    {
        if ($attachment->getIconId()) {
            $icon = $this->iconRepository->get($attachment->getIconId());
            if ($icon && $icon->isActive()) {
                return $icon;
            }
        }

        if ($attachment->getType() == AttachmentInterface::TYPE_FILE) {
            $filePath  = $attachment->getFilePath();
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!$extension) {
                return null;
            }

            /** @var IconInterface $icon */
            $icon = $this->iconRepository->getCollection()
                ->addFieldToFilter(IconInterface::IS_ACTIVE, true)
                ->addFieldToFilter(IconInterface::TYPES, ['finset' => $extension])
                ->getFirstItem();

            $icon = $icon->getId() ? $icon : null;
            if (!$icon) {
                $icon = $this->getDefaultFileIcon();
            }

            return $icon;
        }

        if ($attachment->getType() == AttachmentInterface::TYPE_LINK) {
            return $this->getDefaultLinkIcon();
        }

        return null;
    }

    private function getDefaultFileIcon(): ?IconInterface
    {
        /** @var IconInterface $icon */
        $icon = $this->iconRepository->getCollection()
            ->addFieldToFilter(IconInterface::IS_ACTIVE, true)
            ->addFieldToFilter(IconInterface::TYPES, IconInterface::FILE_DEFAULT_TYPE)
            ->getFirstItem();

        return $icon->getId() ? $icon : null;
    }

    private function getDefaultLinkIcon(): ?IconInterface
    {
        /** @var IconInterface $icon */
        $icon = $this->iconRepository->getCollection()
            ->addFieldToFilter(IconInterface::IS_ACTIVE, true)
            ->addFieldToFilter(IconInterface::TYPES, IconInterface::LINK_DEFAULT_TYPE)
            ->getFirstItem();

        return $icon->getId() ? $icon : null;
    }
}
