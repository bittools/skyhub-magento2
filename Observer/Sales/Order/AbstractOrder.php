<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use BitTools\SkyHub\Observer\AbstractObserver;

abstract class AbstractOrder extends AbstractObserver
{
    
    /**
     * @return \BitTools\SkyHub\Integration\Integrator\Sales\Order
     */
    protected function orderIntegrator()
    {
        return $this->context->objectManager()->create(\BitTools\SkyHub\Integration\Integrator\Sales\Order::class);
    }
    
    
    /**
     * @return \BitTools\SkyHub\Model\StoreIteratorInterface
     */
    protected function getStoreIterator()
    {
        return $this->context->objectManager()->get(\BitTools\SkyHub\Model\StoreIteratorInterface::class);
    }
    
    
    /**
     * @param string $configStatus
     * @param string $currentStatus
     *
     * @return bool
     */
    protected function statusMatches($configStatus, $currentStatus)
    {
        if ($currentStatus == $configStatus) {
            return true;
        }
        
        return false;
    }
}
