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
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Model\ResourceModel\Link as LinkResource;

class Link extends AbstractModel implements LinkInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttachmentId(): int
    {
        return (int)$this->getData(self::ATTACHMENT_ID);
    }

    public function setAttachmentId(int $value): LinkInterface
    {
        return $this->setData(self::ATTACHMENT_ID, $value);
    }

    public function getEntityType(): string
    {
        return (string)$this->getData(self::ENTITY_TYPE);
    }

    public function setEntityType(string $value): LinkInterface
    {
        return $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getEntityPkValue(): int
    {
        return (int)$this->getData(self::ENTITY_PK_VALUE);
    }

    public function setEntityPkValue(int $value): LinkInterface
    {
        return $this->setData(self::ENTITY_PK_VALUE, $value);
    }

    public function getPosition(): int
    {
        return (int)$this->getData(self::POSITION);
    }

    public function setPosition(int $value): LinkInterface
    {
        return $this->setData(self::POSITION, $value);
    }

    public function getIdentities(): array
    {
        $entityType = $this->getEntityType();
        $entityId   = $this->getEntityPkValue();
        if (!$entityType || !$entityId) {
            return [];
        }
        $tag = LinkInterface::CACHE_TAG_MAP[$entityType] . '_' . $this->getEntityPkValue();

        return [$tag];
    }

    protected function _construct()
    {
        $this->_init(LinkResource::class);
    }
}
