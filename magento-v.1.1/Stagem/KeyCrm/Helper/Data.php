<?php

namespace Stagem\KeyCrm\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    private $storeManager;

    const XML_PATH_KEYCRM = 'keycrm/';

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager  = $storeManager;
        parent::__construct($context);
    }

    public function getGeneralSettings($setting = null)
    {
        return $setting === null
            ? $this->getConfigValue(self::XML_PATH_KEYCRM . 'general')
            : $this->getConfigValue(self::XML_PATH_KEYCRM . 'general/' . $setting);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getConfigPayments()
    {
        $json = $this->scopeConfig->getValue('keycrm/paymentList/paymentList');
        $List = $this->getConfigJsonUnserialize($json);
        $payments = [];
        foreach ($List as $code => $el) {
            $payments[$el['payment_cms']] = $el['payment_crm'];
        }

        return $payments;
    }


    /**
     * @return mixed
     */
    public function getConfigSource()
    {
        return $this->scopeConfig->getValue('keycrm/sourceList/sourceList');
    }



    /**
     * @return mixed
     */
    public function getCongigShipping()
    {
        $json = $this->scopeConfig->getValue('keycrm/shippingList/shippingList');
        $shippingList = $this->getConfigJsonUnserialize($json);
        $shippings = [];
        foreach ($shippingList as $code => $el) {
            $shippings[$el['shipping_cms']] = $el['shipping_crm'];
        }

        return $shippings;
    }

    /**
     * @return mixed
     */
    public function getCongigStatus()
    {
        return  $this->scopeConfig->getValue('keycrm/statusList/statusList');
    }

    /**
     * @param $json
     *
     * @return mixed
     */
    public function getConfigJsonUnserialize($json)
    {
        if (class_exists(\Magento\Framework\Serialize\Serializer\Json::class)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $serializer = $objectManager->create(\Magento\Framework\Serialize\Serializer\Json::class);
            return $serializer->unserialize($json);
        } else {
            return json_decode($json);
        }
    }


    /**
     * @return mixed
     */
    public function getConfigProductTypesToSend()
    {
        return $this->scopeConfig->getValue('keycrm/product_types_to_send/product_types_to_send');
    }

}
