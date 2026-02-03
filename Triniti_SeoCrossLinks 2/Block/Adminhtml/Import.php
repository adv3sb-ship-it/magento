<?php

namespace Triniti\SeoCrossLinks\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Import extends Template
{
    protected $_template = 'Triniti_SeoCrossLinks::import.phtml';

    public function getExportUrl(): string
    {
        return $this->getUrl('triniti_crosslinks/export/csv');
    }

    public function getUploadUrl(): string
    {
        return $this->getUrl('triniti_crosslinks/import/upload');
    }
}