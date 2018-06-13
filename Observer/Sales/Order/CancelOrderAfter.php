<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use Magento\Framework\Event\Observer;

class CancelOrderAfter extends AbstractOrder
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
}
