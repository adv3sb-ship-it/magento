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

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Page;
use Magento\Framework\DataObject\IdentityInterface;

interface LinkInterface extends IdentityInterface
{
    const TABLE_NAME = 'mst_attachment_link';

    const ID              = 'link_id';
    const ATTACHMENT_ID   = 'attachment_id';
    const ENTITY_TYPE     = 'entity_type';
    const ENTITY_PK_VALUE = 'entity_pk_value';
    const POSITION        = 'position';

    const ENTITY_TYPE_PRODUCT  = 'product';
    const ENTITY_TYPE_CATEGORY = 'category';
    const ENTITY_TYPE_CMS_PAGE = 'cms_page';

    const CACHE_TAG_MAP
        = [
            LinkInterface::ENTITY_TYPE_PRODUCT  => Product::CACHE_TAG,
            LinkInterface::ENTITY_TYPE_CATEGORY => Category::CACHE_TAG,
            LinkInterface::ENTITY_TYPE_CMS_PAGE => Page::CACHE_TAG,
        ];

    public function getId(): ?int;

    public function getAttachmentId(): int;

    public function setAttachmentId(int $value): self;

    public function getEntityType(): string;

    public function setEntityType(string $value): self;

    public function getEntityPkValue(): int;

    public function setEntityPkValue(int $value): self;

    public function getPosition(): int;

    public function setPosition(int $value): self;
}
