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

namespace Mirasvit\Attachment\Controller\Adminhtml\Icon;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Attachment\Api\Data\IconInterface;

class Edit extends AbstractIcon implements HttpGetActionInterface
{
    public function execute()
    {
        $id    = $this->getRequest()->getParam(IconInterface::ID);
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage((string)__('This icon no longer exists.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        /** @var Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Magento_Catalog::catalog');
        $title = $id ? 'Edit Icon' : 'New Icon';
        $this->initPage($page, $title);

        return $page;
    }
}
