<?php
namespace Stagem\KeyCrm\Model\Config\Backend;

class SourceCrm implements \Magento\Framework\Data\OptionSourceInterface
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
        \Stagem\KeyCrm\Helper\Proxy $client,
        \Stagem\KeyCrm\Model\Logger\Logger $logger
    ) {

        $this->client = $client;
        $this->logger = $logger;
    }

    public function toOptionArray(): array
    {
        try {
            $response = $this->client->listSources();
        } catch (\Exception $exception) {
            $this->logger->writeRow($exception->getMessage());
        }


        $options = [];
        if (isset($response) && $response) {
            foreach ($response as $sources) {
                $options[] = ['value' => $sources->id, 'label' => $sources->name];
            }
        }
        return $options;
    }
}
