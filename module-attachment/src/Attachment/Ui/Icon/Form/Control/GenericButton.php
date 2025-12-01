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

namespace Mirasvit\Attachment\Ui\Icon\Form\Control;

use Magento\Backend\Block\Widget\Context;
use Mirasvit\Attachment\Api\Data\IconInterface;

class GenericButton
{
    private $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getId(): ?int
    {
        $id = (int)$this->context->getRequest()->getParam(IconInterface::ID);
        return $id ? $id : null;
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
