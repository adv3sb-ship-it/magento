<?php
declare(strict_types=1);

namespace Triniti\LayeredNavigationSeo\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Block\Template\Context;

class CategoryTree extends Field
{
    protected $_template = 'Triniti_LayeredNavigationSeo::system/config/category_tree.phtml';
    
    private $categoryCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->addClass('select admin__control-select');
        
        $this->setData('element_id', $element->getHtmlId());
        $this->setData('element_name', $element->getName());
        $this->setData('current_value', $element->getValue());

        return $this->_toHtml();
    }

    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        
        $collection->addFieldToFilter('level', ['gt' => 0]); 
        
        $collection->addAttributeToSort('path', 'asc');
        
        return $collection;
    }
}