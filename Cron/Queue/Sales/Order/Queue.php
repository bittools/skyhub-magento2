<?php

namespace BitTools\SkyHub\Cron\Queue\Sales\Order;

use BitTools\SkyHub\Cron\AbstractCron;
use Magento\Cron\Model\Schedule;
use Magento\Store\Api\Data\StoreInterface;

class Queue extends AbstractCron
{
    
    public function execute(Schedule $schedule)
    {
        $this->processStoreIteration($this, 'executeIntegration', $schedule);
    }
    
    
    /**
     * @param Schedule       $schedule
     * @param StoreInterface $store
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeIntegration(Schedule $schedule, StoreInterface $store)
    {
        if (!$this->canRun($schedule, $store->getId())) {
            return;
        }
        
        $limit = $this->cronConfig()->salesOrderQueue()->getLimit();
        $count = 0;
        
        /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order\Queue $queueIntegrator */
        $queueIntegrator = $this->createObject(\BitTools\SkyHub\Integration\Integrator\Sales\Order\Queue::class);
        
        /** @var \BitTools\SkyHub\Integration\Processor\Sales\Order $orderProcessor */
        $orderProcessor = $this->createObject(\BitTools\SkyHub\Integration\Processor\Sales\Order::class);
        
        $this->initArea();
        
        while ($count < $limit) {
            /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
            $orderData  = $queueIntegrator->nextOrder();
            
            if (empty($orderData)) {
                $schedule->setMessages(__('No order found in the queue.'));
                break;
            }
    
            /** @var \BitTools\SkyHub\Helper\Sales\Order\Created\Message $helper */
            $helper     = $this->createObject(\BitTools\SkyHub\Helper\Sales\Order\Created\Message::class);
            $skyhubCode = $this->arrayExtract($orderData, 'code');
            
            try {
                /** @var \Magento\Sales\Api\Data\OrderInterface $order */
                $order = $orderProcessor->createOrder($orderData);
            } catch (\Exception $e) {
                /** The log is already created in the createOrder method. */
                continue;
            }
            
            if (!$order || !$order->getEntityId()) {
                $schedule->setMessages($helper->getNotCreatedOrderMessage($skyhubCode));
                return;
            }
            
            $message  = $schedule->getMessages();
            $message .= $helper->getOrderCreationMessage($order, $skyhubCode);
            
            /** @var \SkyHub\Api\Handler\Response\HandlerDefault $isDeleted */
            $isDeleted = $queueIntegrator->deleteByOrder($order);
            
            if ($isDeleted) {
                $message .= ' ' . __('It was also removed from queue.');
            }
            
            $schedule->setMessages($message);
            $count++;
        }
    }
    
    
    /**
     * @param Schedule $schedule
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function canRun(Schedule $schedule, $storeId = null)
    {
        if (!$this->cronConfig()->salesOrderQueue()->isEnabled($storeId)) {
            $schedule->setMessages(__('Sales Order Queue Cron is Disabled'));
            return false;
        }
        
        return parent::canRun($schedule, $storeId);
    }
}
