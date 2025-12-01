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

namespace Mirasvit\Attachment\Ui\Attachment\Form\Control;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    private $request;

    public function __construct(RequestInterface $request, Context $context)
    {
        $this->request = $request;
        parent::__construct($context);
    }

    public function getButtonData(): array
    {
        $handle    = $this->request->getParam('handle') ?? '';
        $namespace = $this->request->getParam('namespace') ?? '';
        if ($this->request->getFullActionName() === 'mui_index_render_handle' && $handle === 'mst_attachment_attachment_create' && $namespace === 'attachment_attachment_form') {
            return [];
        } else {
            return [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit'],
                    ],
                ],
                'sort_order'     => 80,
            ];
        }
    }
}
