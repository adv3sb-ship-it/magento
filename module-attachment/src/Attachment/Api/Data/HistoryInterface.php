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

interface HistoryInterface
{
    const TABLE_NAME = 'mst_attachment_history';

    const ID            = 'history_id';
    const ATTACHMENT_ID = 'attachment_id';
    const SOURCE        = 'source';
    const ACTION        = 'action';
    const VALUE         = 'value';
    const CREATED_AT    = 'created_at';

    public function getId(): ?int;

    public function getAttachmentId(): int;

    public function setAttachmentId(int $value): self;

    public function getSource(): string;

    public function setSource(string $value): self;

    public function getAction(): string;

    public function setAction(string $value): self;

    public function getValue(): int;

    public function setValue(int $value): self;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $value): self;
}
