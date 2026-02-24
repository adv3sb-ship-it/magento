<?php

namespace Stagem\KeyCrm\Api;

interface OrderManagerInterface
{
    public function process(\Magento\Sales\Model\Order $order);
}
