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

namespace Mirasvit\Attachment\Plugin\Backend;

use Magento\Framework\App\RequestInterface;
use Mirasvit\Attachment\Api\Data\LinkInterface;
use Mirasvit\Attachment\Ui\Attachment\Form\Modifier\AttachmentModifier;

/**
 * @see Magento\Catalog\Model\Category\DataProvider
 */
class AddAttachmentTabToCategoryEditPlugin
{
    private $attachmentModifier;

    private $request;

    public function __construct(AttachmentModifier $attachmentModifier, RequestInterface $request)
    {
        $this->attachmentModifier = $attachmentModifier;
        $this->request            = $request;
    }

    public function afterGetMeta(object $subject, array $result): array
    {
        $result = $this->attachmentModifier->modifyMeta(
            $result,
            'category_form.category_form',
            'links',
            'data.' . AttachmentModifier::GRID_DATA_PROVIDER,
            '',
            80
        );

        return $result;
    }

    public function afterGetData(object $subject, array $result): array
    {
        $categoryId = (int)$this->request->getParam('id');
        $result     = $this->attachmentModifier->modifyData($result, LinkInterface::ENTITY_TYPE_CATEGORY, $categoryId);

        return $result;
    }
}
