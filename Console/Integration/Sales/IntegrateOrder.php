<?php

namespace BitTools\SkyHub\Console\Integration\Sales;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Integration\Integrator\Sales\Order as OrderIntegrator;
use BitTools\SkyHub\Integration\Processor\Sales\Order as OrderProcessor;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrateOrder extends AbstractSales
{
    
    /** @var string */
    const INPUT_KEY_ORDER_CODE = 'order_code';
    
    /** @var OrderIntegrator */
    protected $integrator;
    
    /** @var OrderProcessor */
    protected $processor;
    
    
    /**
     * IntegrateOrder constructor.
     *
     * @param Context         $context
     * @param OrderIntegrator $integrator
     * @param OrderProcessor  $processor
     * @param null            $name
     */
    public function __construct(Context $context, OrderIntegrator $integrator, OrderProcessor $processor, $name = null)
    {
        parent::__construct($context, $name);
        
        $this->integrator = $integrator;
        $this->processor  = $processor;
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:integrate:order')
            ->setDescription('Import orders from SkyHub service.')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_ORDER_CODE,
                    'o',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'The order code in SkyHub that will be imported to Magento.'
                ),
                $this->getStoreIdOption(),
            ]);
        
        parent::configure();
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param StoreInterface  $store
     *
     * @return int|null|void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processExecute(InputInterface $input, OutputInterface $output, StoreInterface $store)
    {
        $orderCodes = array_unique($input->getOption(self::INPUT_KEY_ORDER_CODE));
        $orderCodes = array_filter($orderCodes);
        
        /** @var string $orderCode */
        foreach ($orderCodes as $orderCode) {
            /** @var array $orderData */
            $orderData = $this->integrator->order($orderCode);
            
            if (!$orderData) {
                $this->style()->error(__('The order code %1 does not exist.', $orderCode));
                continue;
            }
            
            try {
                /** @var OrderInterface|bool $order */
                $order = $this->processor->createOrder($orderData);
                
                if (!$order || !$order->getEntityId()) {
                    $this->style()->error(__('This order could not be created.'));
                    continue;
                }

                if (true === $order->getData('is_created')) {
                    $message = __(
                        'The order code %1 was successfully created. Order ID %2.',
                        $orderCode,
                        $order->getIncrementId()
                    );
                } else {
                    $message = __(
                        'The order code %1 already exists and had its status updated. Order ID %2.',
                        $orderCode,
                        $order->getIncrementId()
                    );
                }
                
                $this->style()->success($message);
            } catch (\Exception $e) {
                $this->style()->error(__('Error when trying to create an order.'));
                $this->style()->error(__('Message: %1.', $e->getMessage()));
            }
        }
    }
}
