<?php

namespace Stagem\KeyCrm\Model\Config\Backend;

use Stagem\KeyCrm\Helper\Proxy as ApiClient;

class ApiKey extends \Magento\Framework\App\Config\Value
{
    private $api;

    /**
     * ApiUrl constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param ApiClient $api
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        ApiClient $api,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->api = $api;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Call before save api url
     *
     * @throws \Magento\Framework\Exception\ValidatorException
     *
     * @return void
     */
    public function beforeSave()
    {
        $this->setParams([
            'url' => $this->getFieldsetDataValue('api_url'),
            'apiKey' => $this->getValue()
        ]);


        if ($this->validateApiUrl($this->api)) {
            $this->setValue($this->getValue());
        }

        parent::beforeSave();
    }

    /**
     * Validate selected api url
     *
     * @param ApiClient $api
     * @param string $apiVersion
     *
     * @throws \Magento\Framework\Exception\ValidatorException
     *
     * @return boolean
     */
    private function validateApiUrl(ApiClient $api)
    {
        $response =  $api->listPaymentMethods();
        if ($response === false) {
            throw new \Magento\Framework\Exception\ValidatorException(__('Incorrect API key'));
        }

        return true;
    }


    /**
     * @param array $data
     */
    private function setParams(array $data)
    {
        $this->api->setUrl($data['url']);
        $this->api->setApiKey($data['apiKey']);
        $this->api->init();
    }
}
