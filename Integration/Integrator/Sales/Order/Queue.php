<?php

namespace BitTools\SkyHub\Integration\Integrator\Sales\Order;

use BitTools\SkyHub\Integration\Integrator\Sales\AbstractSales;
use \Magento\Sales\Api\Data\OrderInterface;

class Queue extends AbstractSales
{
    
    /**
     * @return array|bool
     */
    public function nextOrder()
    {
        /** @var \SkyHub\Api\EntityInterface\Sales\Order\Queue $interface */
        $interface = $this->api()->queue()->entityInterface();
        $result    = $interface->orders();
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        $data = $result->toArray();
        
        if (empty($data)) {
            return false;
        }
        
        return (array) $data;
    }
    
    
    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function deleteByOrder(OrderInterface $order)
    {
        if (!$order->getIncrementId()) {
            return false;
        }
        
        return $this->delete($order->getIncrementId());
    }
    
    
    /**
     * @param string $orderCode
     *
     * @return bool
     */
    public function delete($orderCode)
    {
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $isDeleted */
        $isDeleted = $this->api()->queue()->delete($orderCode);
        return (bool) $isDeleted->success();
    }
}
