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

interface IconInterface
{
    const TABLE_NAME = 'mst_attachment_icon';

    const ID        = 'icon_id';
    const IS_ACTIVE = 'is_active';
    const LABEL     = 'label';
    const TYPES     = 'types';
    const ICON_PATH = 'icon_path';

    const FILE_DEFAULT_TYPE = '';
    const LINK_DEFAULT_TYPE = 'url';

    const STATUS_ACTIVE = 1;

    public function getId(): ?int;

    public function isActive(): bool;

    public function setIsActive(bool $value): self;

    public function getLabel(): string;

    public function setLabel(string $value): self;

    public function getTypes(): array;

    public function setTypes(array $value): self;

    public function getIconPath(): string;

    public function setIconPath(string $value): self;

}
