<?php

namespace Stagem\KeyCrm\Model\Config\Backend;

class ShippingCms extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @var \Stagem\KeyCrm\Model\Logger\Logger
     */
    private $logger;

    /**
     * ShippingColumn constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Shipping\Model\Config          $shippingConfig
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Stagem\KeyCrm\Model\Logger\Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->shippingConfig = $shippingConfig;
        $this->logger = $logger;
    }

    /**
     * @param string $value
     * @return Magently\Tutorial\Block\Adminhtml\Form\Field\Activation
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $deliveryMethods = array();

            try {
                $deliveryMethods = $this->shippingConfig->getActiveCarriers();
            } catch (\Exception $exception) {
                $this->logger->writeRow($exception->getMessage());
            }

            $this->addOption( 'null',  "not selected");
            if ($deliveryMethods) {
                foreach ($deliveryMethods as $code => $delivery) {
                    $this->addOption($delivery->getCarrierCode(), $delivery->getConfigData('title'));
                }
            }

        }

        return parent::_toHtml();
    }
}
