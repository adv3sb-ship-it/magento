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
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Mirasvit\Attachment\Api\Data\AttachmentInterface;
use Mirasvit\Attachment\Repository\AttachmentRepository;

class AttachmentGrid extends Extended
{
    private $attachmentRepository;

    public function __construct(
        AttachmentRepository $attachmentRepository,
        Context $context,
        Data $backendHelper,
        array $data = []
    ) {
        $this->attachmentRepository = $attachmentRepository;

        parent::__construct($context, $backendHelper, $data);
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('mst_attachment/attachment_widget/chooser', ['_current' => true]);
    }

//    public function toHtml(): string
//    {
//        return '
//                <div class="page-main-actions">
//                    <div class="page-actions">
//                        <div class="page-actions-buttons">
//                            <button type="button" data-role="action" onclick="' . $this->getId() . '.close()">
//                                <span>Close</span>
//                            </button>
//                        </div>
//                    </div>
//                </div>' . parent::toHtml();
//    }

    public function getRowInitCallback(): string
    {
        $chooserJsObject = $this->getId();

        $js = '
        function (grid, row) {
            var elementValue = ' . $chooserJsObject . '.getElementValue();
            var arValues = elementValue.split(",");
            arValues = arValues.filter(function (el) {return el != "";});
            arValues = arValues.map(function(item){return parseInt(item)});

            var elTdId = row.querySelector("td.col-attachment_id");
            if(elTdId){
                attachmentId = parseInt(elTdId.innerHTML.trim());
                if(arValues.indexOf(attachmentId) !== -1){
                    row.style.fontWeight = "bold";
                } else {
                    row.style.fontWeight = "normal";
                }
            }
        }';

        return $js;
    }

    public function getRowClickCallback(): string
    {
        $chooserJsObject = $this->getId();

        $js = '
                function (grid, event) {
                    var $tr = jQuery(Event.findElement(event, "tr"));
                    var attachmentLabel = jQuery(".col-label", $tr).html().trim();
                    var attachmentId = jQuery(".col-id", $tr).html().trim();
                    attachmentId = parseInt(attachmentId);

                    var elementValue = ' . $chooserJsObject . '.getElementValue();
                    var arValues = elementValue.split(",");
                    arValues = arValues.filter(function (el) {return el != "";});
                    arValues = arValues.map(function(item){return parseInt(item)});

                    var ind = arValues.indexOf(attachmentId);
                    if (ind === -1) {
                        arValues.push(attachmentId);
                        $tr.css("font-weight", "bold");
                    } else{
                        arValues.splice(ind,1);
                        $tr.css("font-weight", "normal");
                    }
                    arValues.sort(function(a,b){return parseInt(a)>parseInt(b);})

                    elementValue = arValues.join(",");' .
                    $chooserJsObject . '.setElementValue(elementValue);' .
                    $chooserJsObject . '.setElementLabel(elementValue); // for debug
                    //' .$chooserJsObject . '.close();
                }
            ';

        return $js;
    }

    /**
     * Block construction, prepare grid params
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        /** @var string $defaultFilter */
        $defaultFilter = ['chooser_is_active' => '1'];
        $this->setDefaultFilter($defaultFilter);
    }

    protected function _prepareCollection(): self
    {
        $collection = $this->attachmentRepository->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns(): self
    {
        $this->addColumn(
            AttachmentInterface::ID,
            [
                'header'           => __('ID'),
                'index'            => AttachmentInterface::ID,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            AttachmentInterface::LABEL,
            [
                'header'           => __('Label'),
                'index'            => AttachmentInterface::LABEL,
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title',
            ]
        );

        return parent::_prepareColumns();
    }
}
