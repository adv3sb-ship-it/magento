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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Model\ConfigProvider;
use Mirasvit\Attachment\Repository\IconRepository;

class Save extends AbstractIcon implements HttpPostActionInterface
{
    public function __construct(
        IconRepository $iconRepository,
        Context $context
    ) {
        parent::__construct($iconRepository, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id             = $this->getRequest()->getParam(IconInterface::ID);
        $data           = $this->getRequest()->getParams();

        if ($data) {
            $model = $this->initModel();

            if ($id && !$model) {
                $this->messageManager->addErrorMessage((string)__('This icon no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setIsActive((bool)$data[IconInterface::IS_ACTIVE])
                ->setLabel((string)$data[IconInterface::LABEL])
                ->setTypes($this->getTypes())
                ->setIconPath($this->getIconPath());

            try {
                $this->iconRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('Icon was saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [IconInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [IconInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    private function getIconPath(): string
    {
        $icon = $this->getRequest()->getParam(ConfigProvider::ICON_FIELD_NAME);

        if (!$icon || !is_array($icon)) {
            return '';
        }

        $iconName = $icon[0]['file'] ?? null;

        return $iconName ? ConfigProvider::ICON_DIR . '/' . $iconName : '';
    }

    private function getTypes(): array
    {
        $typeRows = $this->getRequest()->getParam(IconInterface::TYPES);

        if (!$typeRows || !is_array($typeRows)) {
            return [];
        }

        $types = [];
        foreach ($typeRows as ['type' => $type]) {
            $types[] = strtolower($type);
        }

        return $types;
    }
}
