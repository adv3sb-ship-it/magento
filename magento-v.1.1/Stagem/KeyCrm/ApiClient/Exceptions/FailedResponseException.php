<?php

namespace Stagem\KeyCrm\ApiClient\Exceptions;

use Stagem\KeyCrm\ApiClient\ApiResponse;

class FailedResponseException extends \DomainException
{
    /** @var ApiResponse */
    protected $response;

    /**
     * FailedResponseException constructor.
     *
     * @param ApiResponse $response
     */
    public function __construct($response)
    {
        parent::__construct();
        $this->response = $response;
    }

    /**
     * @return ApiResponse
     */
    public function getResponse(): ApiResponse
    {
        return $this->response;
    }
}
