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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Repository\IconRepository;

abstract class AbstractIcon extends Action
{
    protected $iconRepository;

    private   $context;

    public function __construct(
        IconRepository $iconRepository,
        Context $context
    ) {
        $this->iconRepository = $iconRepository;
        $this->context        = $context;

        parent::__construct($context);
    }

    protected function initModel(): IconInterface
    {
        $model = null;
        $id    = (int)$this->getRequest()->getParam(IconInterface::ID);
        if ($id) {
            $model = $this->iconRepository->get($id);
        }

        return $model ? $model : $this->iconRepository->create();
    }

    protected function initPage(Page $page, string $title): void
    {
        $page->getConfig()->getTitle()->prepend((string)__($title));
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Attachment::icon');
    }
}
