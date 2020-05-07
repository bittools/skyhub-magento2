<?php

namespace BitTools\SkyHub\Cron\Queue\Sales\Order;

use BitTools\SkyHub\Cron\AbstractCron;
use BitTools\SkyHub\Helper\Sales\Order\Created\Message;
use BitTools\SkyHub\Integration\Processor\Sales\Order;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\Data\StoreInterface;
use SkyHub\Api\Handler\Response\HandlerDefault;

class Queue extends AbstractCron
{
    const QUEUE_PROCESS = 'sales_order_queue_process';

    public function execute(Schedule $schedule)
    {
        $this->processStoreIteration($this, 'executeIntegration', $schedule);
    }

    /**
     * @param Schedule $schedule
     * @param StoreInterface $store
     *
     * @throws LocalizedException
     */
    public function executeIntegration(Schedule $schedule, StoreInterface $store)
    {
        if (!$this->canRun($schedule, $store->getId())) {
            return;
        }

        $this->context->helperContext()->registryManager()->register(self::QUEUE_PROCESS, true, true);

        $limit = $this->cronConfig()->salesOrderQueue()->getLimit();
        $count = 0;

        /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order\Queue $queueIntegrator */
        $queueIntegrator = $this->createObject(\BitTools\SkyHub\Integration\Integrator\Sales\Order\Queue::class);

        /** @var Order $orderProcessor */
        $orderProcessor = $this->createObject(Order::class);

        $this->initArea();

        while ($count < $limit) {

            $message = $schedule->getMessages();
            $orderData = $queueIntegrator->nextOrder();

            if (empty($orderData)) {
                $schedule->setMessages(empty($message) ? __('No order found in the queue.') : $message);
                break;
            }

            /** @var Message $helper */
            $helper = $this->createObject(Message::class);
            $skyhubCode = $this->arrayExtract($orderData, 'code');


            /** @var OrderInterface $order */
            $order = $orderProcessor->createOrder($orderData);
            if (!$order || !$order->getEntityId()) {
                $message = $schedule->getMessages();
                $message .= ($helper->getNotCreatedOrderMessage($skyhubCode));
                $schedule->setMessages($message);
                continue;
            }

            $message .= $helper->getOrderCreationMessage($order, $skyhubCode);

            /** @var HandlerDefault $isDeleted */
            $isDeleted = $queueIntegrator->deleteByOrder($order);

            if ($isDeleted) {
                $message .= ' ' . __('It was also removed from queue.');
            }

            $schedule->setMessages($message);
            $count++;
        }

        $this->context->helperContext()->registryManager()->unregister(self::QUEUE_PROCESS);
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
