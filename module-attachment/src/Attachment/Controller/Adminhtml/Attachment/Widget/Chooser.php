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

namespace Mirasvit\Attachment\Controller\Adminhtml\Attachment\Widget;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;

class Chooser extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    protected $layoutFactory;

    protected $resultRawFactory;

    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory    = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');

        /** @var Layout $layout */
        $layout = $this->layoutFactory->create();

        $grid = $layout->createBlock(
            \Mirasvit\Attachment\Block\Adminhtml\Widget\AttachmentGrid::class,
            '',
            ['data' => ['id' => $uniqId]]
        );

        $html      = $grid->toHtml();
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($html);
    }
}
