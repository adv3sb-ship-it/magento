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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirasvit\Attachment\Api\Data\IconInterface;

class Delete extends AbstractIcon implements HttpGetActionInterface
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();

            $this->iconRepository->delete($model);

            $this->messageManager->addSuccessMessage((string)__('Item was successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [
                IconInterface::ID => $this->getRequest()->getParam(IconInterface::ID),
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
