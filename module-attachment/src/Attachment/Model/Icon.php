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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Attachment\Api\Data\IconInterface;

class Icon extends AbstractModel implements IconInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): IconInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getLabel(): string
    {
        return (string)$this->getData(self::LABEL);
    }

    public function setLabel(string $value): IconInterface
    {
        return $this->setData(self::LABEL, $value);
    }

    public function getTypes(): array
    {
        return array_filter(explode(',', $this->getData(self::TYPES)));
    }

    public function setTypes(array $value): IconInterface
    {
        return $this->setData(self::TYPES, implode(',', $value));
    }

    public function getIconPath(): string
    {
        return (string)$this->getData(self::ICON_PATH);
    }

    public function setIconPath(string $value): IconInterface
    {
        return $this->setData(self::ICON_PATH, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Icon::class);
    }
}
