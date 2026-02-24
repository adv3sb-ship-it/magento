<?php

namespace Stagem\KeyCrm\Model\Api;

class Order
{
    /** @var null|int */
    public $id;
    /** @var int */
    public $source_id;
    /** @var string */
    public $source_uuid;
    /** @var string */
    public $ordered_at;
    /** @var string */
    public $buyer_comment;
    /** @var string */
    public $promocode;
    /** @var float */
    public $discount_percent;
    /** @var float */
    public $discount_amount;
    /** @var float */
    public $shipping_price;
    /** @var float */
    public $taxes;
    /** @var \Stagem\KeyCrm\Model\Api\Buyer */
    public $buyer;
    /** @var \Stagem\KeyCrm\Model\Api\Shipping */
    public $shipping;
    /** @var \Stagem\KeyCrm\Model\Api\Product[] */
    public $products;
    /** @var \Stagem\KeyCrm\Model\Api\Payment[] */
    public $payments;
}
