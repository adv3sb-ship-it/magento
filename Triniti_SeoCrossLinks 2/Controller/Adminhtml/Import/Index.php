<?php

namespace Triniti\SeoCrossLinks\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Triniti_SeoCrossLinks::import';

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        // Щоб пункт меню підсвічувався (не критично, але корисно)
        $resultPage->setActiveMenu('Triniti_SeoCrossLinks::import');

        // Заголовок сторінки
        $resultPage->getConfig()->getTitle()->prepend(__('SEO Cross Links – Import / Export'));

        // Гарантовано додаємо контент навіть якщо layout xml не підхопився
        $block = $resultPage->getLayout()->createBlock(
            \Triniti\SeoCrossLinks\Block\Adminhtml\Import::class
        );
        $resultPage->addContent($block);

        return $resultPage;
    }
}