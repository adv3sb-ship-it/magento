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

use Magento\Framework\Component\ComponentRegistrar;

$registration = dirname(dirname(dirname(__DIR__))) . '/vendor/mirasvit/module-attachment/src/Attachment/registration.php';
if (file_exists($registration)) {
    # module was already installed via composer
    return;
}
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Mirasvit_Attachment',
    __DIR__
);
