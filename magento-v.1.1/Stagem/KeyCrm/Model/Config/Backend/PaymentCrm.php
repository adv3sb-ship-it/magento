<?php

namespace Stagem\KeyCrm\Model\Config\Backend;

class PaymentCrm extends \Magento\Framework\View\Element\Html\Select
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
            $paymentsTypes = array();

            try {
                $response = $this->client->listPaymentMethods();
            } catch (\Exception $exception) {
                $this->logger->writeRow($exception->getMessage());
            }

            if (isset($response)) {
                $paymentsTypes = $response;
            }

            $this->addOption( 'null',  "not selected");
            if ($paymentsTypes) {
                foreach ($paymentsTypes as $paymentsType) {
                    $this->addOption($paymentsType->id.','.$paymentsType->name, $paymentsType->name);
                }
            }

        }

        return parent::_toHtml();
    }
}
