<?php
namespace Stagem\KeyCrm\Model\Config\Backend;

use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

class OrderStatuses
{
    private $orderStatusCollection;

    public function __construct(OrderStatusCollection $orderStatusCollection)
    {
         $this->orderStatusCollection=$orderStatusCollection;
    }

    public function toOptionArray(): array
    {
         return $this->orderStatusCollection->toOptionArray();
    }


}
