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

namespace Mirasvit\Attachment\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mirasvit\Attachment\Repository\AttachmentRepository;

class Chooser extends Widget
{
    protected $attachmentRepository;

    public function __construct(
        AttachmentRepository $attachmentRepository,
        Context $context
    ) {
        $this->attachmentRepository = $attachmentRepository;

        parent::__construct($context);
    }

    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $uniqId    = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('mst_attachment/attachment_widget/chooser', ['uniq_id' => $uniqId]);

        /** @var \Magento\Widget\Block\Adminhtml\Widget\Chooser $chooser */
        $chooser = $this->getLayout()->createBlock(\Magento\Widget\Block\Adminhtml\Widget\Chooser::class)
            ->setElement($element)
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId)
            ->setLabel('');

        if ($element->getValue()) {
            $attachmentIds = explode(',', $element->getValue());
            $attachmentIds = array_filter($attachmentIds, function ($element) {
                return !empty($element);
            });

            $chooser->setLabel($this->escapeHtml(implode(',', $attachmentIds)));
        }

        $element->setData('after_element_html', $chooser->toHtml());

        return $element;
    }

}
