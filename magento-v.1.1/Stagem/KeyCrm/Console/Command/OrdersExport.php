<?php

namespace Stagem\KeyCrm\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class OrdersExport extends Command
{
    private $orderRepository;
    private $searchCriteriaBuilder;
    private $appState;
    private $serviceOrder;
    private $api;
    private $helper;

    public function __construct(
        \Magento\Sales\Model\OrderRepository         $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State                 $appState,
        \Stagem\KeyCrm\Model\Service\Order           $serviceOrder,
        \Stagem\KeyCrm\Helper\Proxy                  $api,
        \Stagem\KeyCrm\Helper\Data                   $helper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->appState = $appState;
        $this->serviceOrder = $serviceOrder;
        $this->api = $api;
        $this->helper = $helper;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('keycrm:orders:export')
            ->setDescription('Upload archive orders to KeyCRM from Magento')
            ->addArgument('from', InputArgument::OPTIONAL, 'Beginning order number')
            ->addArgument('to', InputArgument::OPTIONAL, 'End order number');

        parent::configure();
    }

    /**
     * Upload orders to KeyCRM
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return boolean
     * @throws \Exception
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        $arguments = $input->getArguments();

        if ($arguments['from'] !== null && $arguments['to'] !== null) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $arguments['from'], 'from')
                ->addFilter('increment_id', $arguments['to'], 'to')
                ->create();
        } else {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $resultSearch = $this->orderRepository->getList($searchCriteria);
        $orders = $resultSearch->getItems();

        if (empty($orders)) {
            $output->writeln('<comment>Orders not found</comment>');

            return false;
        }

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order) {
            try {
                $ordersToCrm[] = $this->serviceOrder->process($order);
            } catch (\Exception $exception) {
            }
        }

        $chunked = array_chunk($ordersToCrm, 50);

        foreach ($chunked as $chunk) {
            $this->api->ordersUpload(['orders' => $chunk]);
            time_nanosleep(0, 250000000);
        }

        $output->writeln('<info>Uploading orders finished</info>');

        return true;
    }
}
