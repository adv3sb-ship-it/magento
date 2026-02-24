<?php

namespace Stagem\KeyCrm\Model\Api;

class Source
{
    /** @var int */
    public $id;
    /** @var string */
    public $alias;
    /** @var string */
    public $name;
    /** @var string */
    public $driver;

    /** @var string */
    public $label;

    /**
     * Source constructor.
     *
     * @param array $apiResponse Source array from api response
     */
    public function __construct($apiResponse = [])
    {
        $this->id = $apiResponse['id'];
        $this->alias = $apiResponse['alias'];
        $this->name = $apiResponse['name'];
        $this->driver = $apiResponse['driver'];

        $this->label = sprintf('%s / %s', ucfirst($this->driver), $this->name);
    }
}
