<?php

namespace Stagem\KeyCrm\ApiClient;

use Stagem\KeyCrm\ApiClient\Exceptions\FailedResponseException;
use Stagem\KeyCrm\Model\Api\Source;
use Stagem\KeyCrm\Model\Api\PaymentMethod;
use Stagem\KeyCrm\Model\Api\ShippingMethod;

class ApiClientFactory
{
    protected $httpClient;
    /**
     * Client constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->httpClient = new HttpClient($apiKey);
    }

    /**
     * @return Source[]
     */
    public function listSources(): array
    {
        $result = [];
        $page = 0;

        do {
            ++$page;

            $params = [
                'filter' => ['driver' => 'magento'],
                'limit' => 50,
                'page' => $page
            ];

            $response = $this->httpClient
                ->makeRequest(HttpClient::METHOD_GET, '/order/source', $params);

            $this->throwOnFail($response);
            foreach ($response->data as $source) {
                $result[] = new Source($source);
            }
        } while ($this->hasMore($response));

        return $result;
    }

    /**
     * @return PaymentMethod[]
     */
    public function listPaymentMethods(): array
    {
        $result = [];
        $page = 0;

        do {
            ++$page;

            $response = $this->httpClient
                ->makeRequest(HttpClient::METHOD_GET, '/order/payment-method', ['limit' => 50, 'page' => $page]);

            $this->throwOnFail($response);
            foreach ($response->data as $source) {
                $result[] = new PaymentMethod($source);
            }
        } while ($this->hasMore($response));

        return $result;
    }

    /**
     * @return ShippingMethod[]
     */
    public function listShippingMethods(): array
    {
        $result = [];
        $page = 0;

        do {
            ++$page;

            $response = $this->httpClient
                ->makeRequest(HttpClient::METHOD_GET, '/order/delivery-service', ['limit' => 50, 'page' => $page]);

            $this->throwOnFail($response);
            foreach ($response->data as $source) {
                $result[] = new ShippingMethod($source);
            }
        } while ($this->hasMore($response));

        return $result;
    }

    /**
     * @param $payment
     * @param $paymentIdFromApi
     * @param $orderIdFromApi
     * @return ApiResponse
     */
    public function ordersPaymentsEdit($payment,$paymentIdFromApi, $orderIdFromApi){
        $response = $this->httpClient
            ->makeRequest(HttpClient::METHOD_PUT, '/order/'.$orderIdFromApi.'/payment/'.$paymentIdFromApi,$payment);

        $this->throwOnFail($response);

        return $response;
    }


    /**
     * @param $id
     * @param $include
     * @return ApiResponse
     */
    public function orderGetByUuid($id, $include = null): ApiResponse
    {
        $data = ['filter' => ['source_uuid' => $id]];
        if($include){
           $data['include'] = $include;
        }
        $response = $this->httpClient
            ->makeRequest(HttpClient::METHOD_GET, '/order/',$data);

        $this->throwOnFail($response);

        return $response;
    }

    /**
     * @param $data
     * @return ApiResponse
     */
    public function createOrder($data): ApiResponse
    {
        $response = $this->httpClient
            ->makeRequest(HttpClient::METHOD_POST, '/order', $data);

        $this->throwOnFail($response);

        return $response;
    }

    /**
     * @param $data
     * @return ApiResponse
     */
    public function ordersUpload($data): ApiResponse
    {
        $response = $this->httpClient
            ->makeRequest(HttpClient::METHOD_POST, '/order/import', $data);

        $this->throwOnFail($response);

        return $response;
    }

    /**
     * @param ApiResponse $response
     *
     * @return bool
     */
    protected function hasMore(ApiResponse $response): bool
    {
        $cur = $response->current_page;
        $last = $response->last_page;

        return $last > $cur;
    }

    /**
     * @param ApiResponse $response
     *
     * @throws FailedResponseException
     */
    protected function throwOnFail(ApiResponse $response)
    {
        if (! $response->isSuccessful()) {
            throw new FailedResponseException($response);
        }
    }

}
