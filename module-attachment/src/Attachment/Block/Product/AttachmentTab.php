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

namespace Mirasvit\Attachment\Block\Product;

use Magento\Framework\View\Element\Template;
use Mirasvit\Attachment\Model\ConfigProvider;

class AttachmentTab extends Template
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,

        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;

        $data['title']      = $this->configProvider->getProductTabLabel();
        $data['sort_order'] = $this->configProvider->getProductTabPosition();

        parent::__construct($context, $data);
    }

    public function toHtml(): ?string
    {
        if (!$this->configProvider->isProductTabEnabled()) {
            return null;
        }

        return parent::toHtml();
    }
}
