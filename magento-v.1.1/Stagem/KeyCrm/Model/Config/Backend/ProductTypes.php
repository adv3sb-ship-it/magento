<?php
namespace Stagem\KeyCrm\Model\Config\Backend;

class ProductTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    const SEND_ALL_TYPES = 0;
    const SEND_ONLY_SIMPLE = 1;
    const SEND_ONLY_PARENT = 2;

    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('Send both (simple, parent) products')],
            ['value' => 1, 'label' => __('Send only simple products')],
            ['value' => 2, 'label' => __('Send only parent products')]
        ];
    }
}
