<?php
namespace Triniti\SeoCrossLinks\Model;
use Magento\Framework\Model\AbstractModel;
class CrossLink extends AbstractModel {
 protected function _construct() {
  $this->_init(\Triniti\SeoCrossLinks\Model\ResourceModel\CrossLink::class);
 }
}
