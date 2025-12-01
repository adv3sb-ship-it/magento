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

namespace Mirasvit\Attachment\Ui\Attachment\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Attachment\Api\Data\IconInterface;
use Mirasvit\Attachment\Repository\IconRepository;

class IconSource implements OptionSourceInterface
{
    private $iconRepository;

    public function __construct(IconRepository $iconRepository)
    {
        $this->iconRepository = $iconRepository;
    }

    public function toOptionArray(): array
    {
        $options    = [];
        $collection = $this->iconRepository->getCollection()->addFieldToFilter(IconInterface::IS_ACTIVE, IconInterface::STATUS_ACTIVE);

        /** @var IconInterface $icon */
        foreach ($collection as $icon) {
            $options[] = [
                'label' => $icon->getLabel(),
                'value' => $icon->getId(),
            ];
        }

        return $options;
    }
}
