<?php

namespace Stagem\KeyCrm\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Address;
use Stagem\KeyCrm\Helper\Proxy as ApiClient;
use Stagem\KeyCrm\Helper\Data as Helper;
use Stagem\KeyCrm\Model\Logger\Logger;
use Stagem\KeyCrm\Model\Service\Order;

class OrderCreate implements ObserverInterface
{
    protected $api;
    protected $logger;
    protected $helper;

    private $order;
    private $serviceOrder;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param Order $serviceOrder
     * @param Helper $helper
     * @param ApiClient $api
     */
    public function __construct(
        Logger $logger,
        Order $serviceOrder,
        Helper $helper,
        ApiClient $api
    ) {
        $this->logger = $logger;
        $this->serviceOrder = $serviceOrder;
        $this->helper = $helper;
        $this->api = $api;
        $this->order = [];
    }

    /**
     * Execute send order in CRM
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
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($this->existsInCrm($order->getRealOrderId()) === true) {
            $this->logger->writeDump($this->order, 'Order not added to crm, order exist in crm');
            return false;
        }
        if(!in_array($order->getStatus(), json_decode($this->helper->getCongigStatus()))){
            $this->logger->writeDump($this->order, 'Order not added to crm, order status: ' . $order->getStatus());
            return false;
        }
        $this->order = $this->serviceOrder->process($order);
        $this->logger->writeDump($this->order, 'CreateOrder');
        $this->api->createOrder($this->order);
        return $this;
    }


    /**
     * Check exists order in CRM
     *
     * @param int $id
     * @param string $method
     * @param string $by
     * @param string $site
     *
     * @return boolean
     */
    private function existsInCrm($id, $method = 'orderGetByUuid')
    {
        $response = $this->api->{$method}($id);

        if (!isset($response->__get('data')[0]['id'])){
            return false;
        }
        return true;

    }


    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }
}
