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

namespace Mirasvit\Attachment\Api\Data;

use Magento\Framework\DataObject\IdentityInterface;

interface AttachmentInterface extends IdentityInterface
{
    const TABLE_NAME = 'mst_attachment';

    const ID                 = 'attachment_id';
    const IS_ACTIVE          = 'is_active';
    const TYPE               = 'type';
    const SOURCE_NAME        = 'source_name';
    const FILE_PATH          = 'file_path';
    const FILE_TYPE          = 'file_type';
    const LABEL              = 'label';
    const ICON_ID            = 'icon_id';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const STORE_IDS          = 'store_ids';
    const POSITION           = 'attachment_position';

    const TYPE_LINK = 'link';
    const TYPE_FILE = 'file';

    public function getId(): ?int;

    public function isActive(): bool;

    public function setIsActive(bool $value): self;

    public function getType(): string;

    public function setType(string $value): self;


    public function getSourceName(): string;

    public function setSourceName(string $value): self;

    public function getFilePath(): string;

    public function setFilePath(string $value): self;

    public function getFileType(): string;

    public function setFileType(string $value): self;

    public function getLabel(): string;

    public function setLabel(string $value): self;

    public function getIconId(): int;

    public function setIconId(?int $value): self;

    public function getCustomerGroupIds(): array;

    public function setCustomerGroupIds(array $value): self;

    public function getStoreIds(): array;

    public function setStoreIds(array $value): self;

    public function getPosition(): int;

    public function setPosition(int $value): self;

}
