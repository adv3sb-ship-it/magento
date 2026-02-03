<?php
namespace Triniti\SeoCrossLinks\Controller\Adminhtml\Export;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Triniti\SeoCrossLinks\Model\ResourceModel\CrossLink\CollectionFactory;
class Csv extends Action {
 const ADMIN_RESOURCE = 'Triniti_SeoCrossLinks::import';
 public function __construct(
  Action\Context $context,
  private FileFactory $fileFactory,
  private CollectionFactory $collectionFactory
 ) { parent::__construct($context); }
 public function execute() {
  $rows[] = ['store_id','donor','acceptor','anchor','sort_order','is_active'];
  foreach ($this->collectionFactory->create() as $i) {
   $rows[] = [
    $i->getStoreId(),
    $i->getDonorUrl(),
    $i->getAcceptorUrl(),
    $i->getAnchor(),
    $i->getSortOrder(),
    $i->getIsActive()
   ];
  }
  $csv=''; foreach($rows as $r){ $csv.=implode(';',$r)."\n"; }
  return $this->fileFactory->create(
   'seo_crosslinks_export.csv',
   $csv,
   DirectoryList::VAR_DIR,
   'text/csv'
  );
 }
}
