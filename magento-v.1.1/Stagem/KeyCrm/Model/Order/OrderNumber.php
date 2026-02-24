<?php

namespace Stagem\KeyCrm\Model\Order;

use Stagem\KeyCrm\ApiClient\ApiResponse;
use Stagem\KeyCrm\Helper\Data as Helper;
use Stagem\KeyCrm\Helper\Proxy as ApiClient;
use Stagem\KeyCrm\Model\Logger\Logger;
use Magento\Sales\Api\Data\OrderInterface;

class OrderNumber
{
    private $orderResource;
    private $orderFactory;
    private $orderService;
    private $helper;
    private $api;
    private $logger;
    private $searchCriteriaBuilder;
    private $orderRepository;


    public function __construct(
        Helper                                          $helper,
        ApiClient                                       $api,
        \Stagem\KeyCrm\Model\Service\Order              $orderService,
        \Magento\Sales\Model\Spi\OrderResourceInterface $orderResource,
        \Magento\Sales\Api\Data\OrderInterfaceFactory   $orderFactory,
        Logger                                          $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder    $searchCriteriaBuilder,
        \Magento\Sales\Model\OrderRepository            $orderRepository

    )
    {
        $this->api = $api;
        $this->helper = $helper;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->orderService = $orderService;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;

    }

    /**
     * @param string $orderNumbers
     *
     * @return array
     */
    public function exportOrderNumbers($orderNumbers): array
    {
        $orderNumbers = trim($orderNumbers);
        if (strpos($orderNumbers, '-')) {
            $from = trim(stristr($orderNumbers, '-', true));
            $to = trim(str_replace("-", "", stristr($orderNumbers, '-')));
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $from, 'from')
                ->addFilter('increment_id', $to, 'to')
                ->create();
            $resultSearch = $this->orderRepository->getList($searchCriteria);
            $orders = $resultSearch->getItems();
            foreach ($orders as $order) {
                try {
                    $orders[] = $this->orderService->process($order);
                } catch (\Exception $exception) {
                    $this->logger->writeRow(sprintf("Error: %s", $exception));
                }
            }
        } else {
            $ordersId = explode(",", $orderNumbers);
            $orders = [];
            foreach ($ordersId as $id) {
                $magentoOrder = $this->getOrder($id);
                if (!$magentoOrder->getEntityId()) {
                    $this->logger->writeRow(sprintf("Order with %s does not exist", $id));
                    continue;
                }
                try {
                    $orders[] = $this->orderService->process($magentoOrder);
                } catch (\Exception $exception) {
                    $this->logger->writeRow(sprintf("Order with %s cant export;", $id));
                }
            }
        }


        $orders = array_filter($orders);
        if (!$orders) {
            return ['success' => false];
        }
        $chunked = array_chunk($orders, 50);

        foreach ($chunked as $chunk) {
            $response = $this->api->ordersUpload(['orders' => $chunk]);
            if ($response && !$response->isSuccessful()) {
                return [
                    'success' => false,
                    'error' => $response->getErrorMsg()
                ];
            }
            time_nanosleep(0, 250000000);
        }


        return ['success' => true];
    }

    public function getOrder($incrementId)
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId, OrderInterface::INCREMENT_ID);
        return $order;
    }
}
