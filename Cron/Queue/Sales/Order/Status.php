<?php

namespace BitTools\SkyHub\Cron\Queue\Sales\Order;

use BitTools\SkyHub\Cron\Queue\AbstractQueue;
use Magento\Cron\Model\Schedule;

class Status extends AbstractQueue
{
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(Schedule $schedule)
    {
        if (!$this->canRun($schedule)) {
            return;
        }
        
        /** @var \BitTools\SkyHub\Helper\Sales\Order $helper */
        $helper = $this->createObject(\BitTools\SkyHub\Helper\Sales\Order::class);
        
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $helper->getPendingOrdersFromSkyHub();
        
        /** @var \Magento\Framework\DB\Select $select */
        $select = $collection->getSelect()
            ->reset('columns')
            ->columns('entity_id');
        
        $limit = $this->context
            ->cronConfig()
            ->salesOrderStatus()
            ->queueCreateLimit();
        
        if ($limit) {
            $select->limit((int) $limit);
        }
        
        $orderIds = (array) $this->getQueueResource()
            ->getConnection()
            ->fetchCol($select);
        
        if (empty($orderIds)) {
            $schedule->setMessages(__('No order to queue.'));
            return;
        }
        
        $this->getQueueResource()->queue(
            $orderIds,
            \BitTools\SkyHub\Model\Entity::TYPE_SALES_ORDER_STATUS,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_IMPORT
        );
        
        $schedule->setMessages(__('Order IDs Queued: %s.', implode(',', $orderIds)));
    }
    
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed|void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(Schedule $schedule)
    {
        $limit = $this->cronConfig()->salesOrderStatus()->queueExecuteLimit();
        
        $orderIds = (array) $this->getQueueResource()->getPendingEntityIds(
            \BitTools\SkyHub\Model\Entity::TYPE_SALES_ORDER_STATUS,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_IMPORT,
            (int) $limit
        );
        
        if (empty($orderIds)) {
            $schedule->setMessages(__('No order in the queue to be processed.'));
            return;
        }
        
        /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order $integrator */
        $integrator = $this->createObject(\BitTools\SkyHub\Integration\Integrator\Sales\Order::class);
        
        /** @var \BitTools\SkyHub\Integration\Processor\Sales\Order\Status $processor */
        $processor = $this->createObject(\BitTools\SkyHub\Integration\Processor\Sales\Order\Status::class);
        
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->getOrderCollection()
            ->addFieldToFilter('entity_id', ['in' => $orderIds]);
        
        $this->initArea();
        
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            if (!$this->canRun($schedule, $order->getStoreId())) {
                continue;
            }
            
            /** @var array $orderData */
            $orderData = (array) $integrator->orderByOrderId($order->getId());
            
            $statusCode = $this->arrayExtract($orderData, 'status/code');
            $statusType = $this->arrayExtract($orderData, 'status/type');
            // $statusLabel = $this->arrayExtract($orderData, 'status/label');
            
            $result = $processor->processOrderStatus($statusCode, $statusType, $order);
            
            if (false == $result) {
                continue;
            }
            
            $this->getQueueResource()->removeFromQueue(
                $order->getId(),
                \BitTools\SkyHub\Model\Entity::TYPE_SALES_ORDER_STATUS
            );
        }
    }
    
    
    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function getOrderCollection()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->createObject(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        
        return $collection;
    }
    
    
    /**
     * @param Schedule $schedule
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function canRun(Schedule $schedule, $storeId = null)
    {
        if (!$this->cronConfig()->salesOrderStatus()->isEnabled($storeId)) {
            $schedule->setMessages(__('Sales Order Status Cron is Disabled'));
            return false;
        }
        
        return parent::canRun($schedule, $storeId);
    }
}
