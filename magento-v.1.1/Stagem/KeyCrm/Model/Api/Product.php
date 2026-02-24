<?php

namespace Stagem\KeyCrm\Model\Api;

class Product
{
    /** @var string */
    public $sku;
    /** @var string */
    public $name;
    /** @var string */
    public $quantity;
    /** @var float */
    public $price;
    /** @var float */
    public $discount_percent;
    /** @var float */
    public $discount_amount;
    /** @var string */
    public $picture;
    /** @var array */
    public $properties;
}
