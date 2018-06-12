<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use BitTools\SkyHub\Observer\AbstractObserver;
use Magento\Framework\Event\Observer;

class CancelOrderAfter extends AbstractObserver
{
    
    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getData('order');
    
        if (!$order || !$order->getEntityId()) {
            return;
        }
        
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        $store = $this->getStore($order->getStoreId());
    
        $this->getStoreIterator()->call($this->orderIntegrator(), 'cancel', [$order->getEntityId()], $store);
    }
    
    
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
}
