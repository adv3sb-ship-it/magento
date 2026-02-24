<?php

namespace Stagem\KeyCrm\Model\Api;

class ShippingMethod
{
    /** @var int */
    public $id;
    /** @var string */
    public $alias;
    /** @var string */
    public $name;
    /** @var bool */
    public $source_name;

    /**
     * ShippingMethod constructor.
     *
     * @param array $apiResponse ShippingMethod array from api response
     */
    public function __construct($apiResponse = [])
    {
        $this->id = $apiResponse['id'];
        $this->alias = $apiResponse['alias'];
        $this->name = $apiResponse['name'];
        $this->source_name = $apiResponse['source_name'];
    }
}
