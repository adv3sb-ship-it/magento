<?php
namespace Triniti\SeoCrossLinks\Model\ResourceModel\CrossLink;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
class Collection extends AbstractCollection {
 protected function _construct() {
  $this->_init(
   \Triniti\SeoCrossLinks\Model\CrossLink::class,
   \Triniti\SeoCrossLinks\Model\ResourceModel\CrossLink::class
  );
 }
}
