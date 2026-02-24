<?php

namespace Stagem\KeyCrm\Model\Observer;

use Magento\Framework\Event\Observer;
use Stagem\KeyCrm\Helper\Proxy as ApiClient;
use Stagem\KeyCrm\Helper\Data as Helper;

class OrderUpdate implements \Magento\Framework\Event\ObserverInterface
{
    private $api;
    private $config;
    private $registry;
    private $order;
    private $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param ApiClient $api
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        ApiClient $api
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->api = $api;
        $this->order = [];
    }

    /**
     * Execute update order in CRM
     *
     * @param Observer $observer
     *
     * @return mixed
     */
    public function execute(Observer $observer)
    {
        if (!$this->api->isConfigured()) {
            return false;
        }

        $order = $observer->getEvent()->getOrder();

        if ($order) {
            if ($order->getBaseTotalDue() == 0) {
                $orderFromApi = $this->api->orderGetByUuid($order->getRealOrderId(),'payments');
                if(isset($orderFromApi->__get('data')[0]['id'])){
                    $payment = [
                        'status' => 'paid'
                    ];
                    $this->api->ordersPaymentsEdit($payment,$orderFromApi->__get('data')[0]['payments'][0]['id'],$orderFromApi->__get('data')[0]['id']);
                }
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }
}
