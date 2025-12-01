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
use Mirasvit\Attachment\Api\Data\HistoryInterface;
use Mirasvit\Attachment\Model\ResourceModel\History as HistoryResource;

class History extends AbstractModel implements HistoryInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getAttachmentId(): int
    {
        return (int)$this->getData(self::ATTACHMENT_ID);
    }

    public function setAttachmentId(int $value): HistoryInterface
    {
        return $this->setData(self::ATTACHMENT_ID, $value);
    }

    public function getSource(): string
    {
        return (string)$this->getData(self::SOURCE);
    }

    public function setSource(string $value): HistoryInterface
    {
        return $this->setData(self::SOURCE, $value);
    }

    public function getAction(): string
    {
        return (string)$this->getData(self::ACTION);
    }

    public function setAction(string $value): HistoryInterface
    {
        return $this->setData(self::ACTION, $value);
    }

    public function getValue(): int
    {
        return (int)$this->getData(self::VALUE);
    }

    public function setValue(int $value): HistoryInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $value): HistoryInterface
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    protected function _construct()
    {
        $this->_init(HistoryResource::class);
    }
}
