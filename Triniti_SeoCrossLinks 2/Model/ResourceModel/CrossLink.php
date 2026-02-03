<?php
namespace Triniti\SeoCrossLinks\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class CrossLink extends AbstractDb {
 protected function _construct() {
  $this->_init('triniti_seo_crosslink','entity_id');
 }
}
