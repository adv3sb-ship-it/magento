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
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Model\ResourceModel\Attachment as AttachmentResource;

class Attachment extends AbstractModel implements AttachmentInterface
{
    const CACHE_TAG = 'mst_attachment_attachment_p';

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): AttachmentInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    public function setType(string $value): AttachmentInterface
    {
        return $this->setData(self::TYPE, $value);
    }

    public function getSourceName(): string
    {
        return (string)$this->getData(self::SOURCE_NAME);
    }

    public function setSourceName(string $value): AttachmentInterface
    {
        return $this->setData(self::SOURCE_NAME, $value);
    }

    public function getFilePath(): string
    {
        return (string)$this->getData(self::FILE_PATH);
    }

    public function setFilePath(string $value): AttachmentInterface
    {
        return $this->setData(self::FILE_PATH, $value);
    }

    public function getFileType(): string
    {
        return (string)$this->getData(self::FILE_TYPE);
    }

    public function setFileType(string $value): AttachmentInterface
    {
        return $this->setData(self::FILE_TYPE, $value);
    }

    public function getLabel(): string
    {
        return (string)$this->getData(self::LABEL);
    }

    public function setLabel(string $value): AttachmentInterface
    {
        return $this->setData(self::LABEL, $value);
    }

    public function getIconId(): int
    {
        return (int)$this->getData(self::ICON_ID);
    }

    public function setIconId(?int $value): AttachmentInterface
    {
        return $this->setData(self::ICON_ID, $value);
    }

    public function getCustomerGroupIds(): array
    {
        $ids = $this->getData(self::CUSTOMER_GROUP_IDS) ?? '';

        return explode(',', $ids);
    }

    public function setCustomerGroupIds(array $value): AttachmentInterface
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, implode(',', $value));
    }

    public function getStoreIds(): array
    {
        $ids = $this->getData(self::STORE_IDS) ?? '';

        return explode(',', $ids);
    }

    public function setStoreIds(array $value): AttachmentInterface
    {
        return $this->setData(self::STORE_IDS, implode(',', $value));
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(AttachmentResource::class);
    }

    public function getPosition(): int
    {
        return intval($this->getData(self::POSITION));
    }

    public function setPosition(int $value): AttachmentInterface
    {
        return $this->setData(self::POSITION, $value);
    }
}
