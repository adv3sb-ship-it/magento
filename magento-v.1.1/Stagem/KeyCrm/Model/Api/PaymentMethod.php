<?php

namespace Stagem\KeyCrm\Model\Api;

class PaymentMethod
{
    /** @var int */
    public $id;
    /** @var string */
    public $alias;
    /** @var string */
    public $name;
    /** @var bool */
    public $is_active;

    /**
     * PaymentMethod constructor.
     *
     * @param array $apiResponse PaymentMethod array from api response
     */
    public function __construct($apiResponse = [])
    {
        $this->id = $apiResponse['id'];
        $this->alias = $apiResponse['alias'];
        $this->name = $apiResponse['name'];
        $this->is_active = $apiResponse['is_active'];
    }
}
