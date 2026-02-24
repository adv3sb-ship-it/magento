<?php

namespace Stagem\KeyCrm\Model\Service;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Stagem\KeyCrm\Api\OrderManagerInterface;
use \Stagem\KeyCrm\Helper\Data as Helper;
use Stagem\KeyCrm\Model\Logger\Logger;
use Magento\Directory\Model\RegionFactory;
use Stagem\KeyCrm\Model\Config\Backend\ProductTypes;


class Order implements OrderManagerInterface
{
    private $productRepository;
    private $config;
    private $helper;
    private $configurableProduct;
    private $logger;
    private $storeManager;
    private $request;
    private $regionFactory;
    private $timezone;


    public function __construct(
        ProductRepository     $productRepository,
        ScopeConfigInterface  $config,
        Configurable          $configurableProduct,
        Helper                $helper,
        Logger                $logger,
        StoreManagerInterface $storeManager,
        Http                  $request,
        RegionFactory $regionFactory,
        TimezoneInterface $timezone


    )
    {
        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->helper = $helper;
        $this->configurableProduct = $configurableProduct;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->regionFactory = $regionFactory;
        $this->timezone = $timezone;



    }

    /**
     * Process order
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     * @throws Exception
     */
    public function process(\Magento\Sales\Model\Order $order): array
    {
        $items = $order->getAllItems();
        $products = $this->addProducts($items);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            return [];
        }
        $shipping = $order->getShippingMethod();
        do {
            $shipping = $this->getShippingCode($shipping);
        } while (strpos($shipping, "_"));
        $shipList = $this->helper->getCongigShipping();
        $paymentList = $this->helper->getConfigPayments();
        $status = $order->getBaseTotalDue() == 0 ? 'paid' : 'not_paid';
        $region = "";
        if($shippingAddress->getData('region_id')){
            $region = $this->regionFactory->create()->load($shippingAddress->getData('region_id'))->getData()['name'];
        }
        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s',$order->getCreatedAt());
        $timezone = $this->config->getValue(
            'general/locale/timezone'
        );
        if ($timezone) {
            $storeTime = new \DateTimeZone($timezone);
            $datetime->setTimezone($storeTime);
        }
        $createdAt = $datetime->format('Y-m-d H:i:s');
        $preparedOrder = [
            'source_id' => intval($this->helper->getConfigSource()),
            'source_uuid' => $order->getRealOrderId(),
            'ordered_at' => $createdAt,
            'products' => $products,
            'discount_amount' => abs($order->getDiscountAmount()),
            'shipping_price' => floatval($order->getShippingAmount()),
            'promocode' => $order->getCouponCode() ? $order->getCouponCode() : "",
            'buyer' => [
                'full_name' => $shippingAddress->getFirstname() . " " . $shippingAddress->getMiddlename() . " " . $shippingAddress->getLastname(),
                'email' => $shippingAddress->getEmail(),
                'phone' => $shippingAddress->getTelephone(),
            ],
            'shipping' => [
                'shipping_address_city' => $shippingAddress->getData('city'),
                'shipping_address_country' => $shippingAddress->getData('country_id'),
                'shipping_receive_point' => $shippingAddress->getData('street'),
                'shipping_address_region' => $region,
                'shipping_address_zip' => $shippingAddress->getData('postcode'),
                'recipient_full_name' => $shippingAddress->getFirstname() . " " . $shippingAddress->getMiddlename() . " " . $shippingAddress->getLastname(),
                'recipient_phone' => $shippingAddress->getTelephone()
            ]
        ];

        if(isset($shipList[$shipping])){
            if(intval($shipList[$shipping])){
                $preparedOrder['shipping']['delivery_service_id'] = intval($shipList[$shipping]);
            }
        }
        if(isset($paymentList[$order->getPayment()->getMethodInstance()->getCode()])){
            if(intval($paymentList[$order->getPayment()->getMethodInstance()->getCode()])){
                $preparedOrder['payments'] = [(object)[
                    'amount' => floatval($order->getGrandTotal()),
                    'status' => $status,
                    'payment_method_id' => intval(stristr($paymentList[$order->getPayment()->getMethodInstance()->getCode()],',',true)),
                    'payment_method' => str_replace(',', '',stristr($paymentList[$order->getPayment()->getMethodInstance()->getCode()],','))
                ]];
            }
        }

        $marketing = $this->getMarketing();
        if ($marketing) {
            $preparedOrder['marketing'] = $marketing;
        }
        return $preparedOrder;
    }

    /**
     * @return array[]
     */
    public function getMarketing(): array
    {
        $params = $this->request->getParams();
        $marketing = [];
        if ((isset($params['utm_source']) && $params['utm_source'])) {
            $marketing['utm_source'] = $params['utm_source'];
        }
        if ((isset($params['utm_medium']) && $params['utm_medium'])) {
            $marketing['utm_medium'] = $params['utm_medium'];
        }
        if ((isset($params['utm_campaign']) && $params['utm_campaign'])) {
            $marketing['utm_campaign'] = $params['utm_campaign'];
        }
        if ((isset($params['utm_term']) && $params['utm_term'])) {
            $marketing['utm_term'] = $params['utm_term'];
        }
        if ((isset($params['utm_content']) && $params['utm_content'])) {
            $marketing['utm_content'] = $params['utm_content'];
        }

        return $marketing;
    }

    /**
     * Get shipping code
     *
     * @param string $string
     *
     * @return string
     */
    public function getShippingCode($string): string
    {
        $split = array_values(explode('_', $string));
        $length = count($split);
        $prepare = array_slice($split, 0, $length / 2);

        return implode('_', $prepare);
    }

    /**
     * Add products in order array
     *
     * @param $items
     *
     * @return array
     */
    protected function addProducts($items): array
    {
        $productTypesToSend = $this->helper->getConfigProductTypesToSend();
        $products = [];

        foreach ($items as $item) {
            $itemType = $item->getProductType();
            if($productTypesToSend){
                if($productTypesToSend == ProductTypes::SEND_ONLY_SIMPLE){
                    if($itemType == 'configurable' || $itemType == 'bundle' || $itemType == 'grouped'){
                        continue;
                    }
                }
                if($productTypesToSend == ProductTypes::SEND_ONLY_PARENT){
                    if($item->getParentItem()){
                        continue;
                    }
                }
            }
            $price = $item->getPrice();
            $resultProduct = [
                'sku' => $itemType != 'simple' ? $item->getProduct()->getSku() : $item->getSku(),
                'price' => floatval($price),
                'quantity' => intval($item->getQtyOrdered()),
                'name' => $item->getName(),
            ];
            $discountAmount =  $item->getDiscountAmount() ?  $item->getDiscountAmount() : $item->getOriginalPrice() - $item->getPrice();
            if($discountAmount) {
                $resultProduct['discount_amount'] = floatval($discountAmount);
            }
            $product = $item->getProduct();
            if ($product) {
                if ($productImage = $product->getImage()) {
                    $store = $this->storeManager->getStore();
                    $resultProduct['picture'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $productImage;
                }
            }
            $products[] = $resultProduct;
        }

        return $products;
    }
}
