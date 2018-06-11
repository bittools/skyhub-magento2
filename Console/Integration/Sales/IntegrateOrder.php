<?php

namespace BitTools\SkyHub\Console\Integration\Sales;

use BitTools\SkyHub\Helper\Sales\Order\Created\Message;
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
        /** @var Message $message */
        $message = $this->objectManager()->create(Message::class);
        
        $orderCodes = array_unique($input->getOption(self::INPUT_KEY_ORDER_CODE));
        $orderCodes = array_filter($orderCodes);
        
        /** @var string $orderCode */
        foreach ($orderCodes as $orderCode) {
            /** @var OrderIntegrator $integrator */
            $integrator = $this->objectManager()->create(OrderIntegrator::class);
            
            /** @var array $orderData */
            $orderData = $integrator->order($orderCode);
            
            if (!$orderData) {
                $this->style()->error($message->getNonExistentOrderMessage($orderCode));
                continue;
            }
            
            try {
                /** @var OrderProcessor $processor */
                $processor = $this->objectManager()->create(OrderProcessor::class);
                
                /** @var OrderInterface|bool $order */
                $order = $processor->createOrder($orderData);
                
                if (!$order || !$order->getEntityId()) {
                    $this->style()->error(__('This order could not be created.'));
                    continue;
                }
                
                $string = $message->getOrderCreationMessage($order, $orderCode);
                $this->style()->success($string);
            } catch (\Exception $e) {
                $this->style()->error(__('Error when trying to create an order.'));
                $this->style()->error(__('Message: %1.', $e->getMessage()));
            }
        }
    }
}
