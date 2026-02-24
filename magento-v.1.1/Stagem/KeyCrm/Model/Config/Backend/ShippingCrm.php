<?php

namespace Stagem\KeyCrm\Model\Config\Backend;

class ShippingCrm extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Stagem\KeyCrm\Helper\Proxy
     */
    private $client;

    /**
     * @var \Stagem\KeyCrm\Model\Logger\Logger
     */
    private $logger;

    /**
     * Activation constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Stagem\KeyCrm\Helper\Proxy $client,
        \Stagem\KeyCrm\Model\Logger\Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->client = $client;
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
            try {
                $response = $this->client->listShippingMethods();
            } catch (\Exception $exception) {
                $this->logger->writeRow($exception->getMessage());
            }
            $this->addOption( 'null',  "not selected");
            if (isset($response) && $response) {
                foreach ($response as $deliveryType) {
                    $this->addOption($deliveryType->id, $deliveryType->name);
                }
            }
        }
        return parent::_toHtml();
    }
}
