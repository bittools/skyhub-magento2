<?php

namespace BitTools\SkyHub\Observer\Sales\Order\Invoice;

use BitTools\SkyHub\Observer\Sales\AbstractSales;
use Magento\Framework\Event\Observer;

class IntegrateInvoiceKey extends AbstractSales
{
    
    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
        $relation = $observer->getData('order_relation');
        
        if (!$relation || !$relation->getId()) {
            return;
        }
        
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        $params = [
            $relation->getOrderId(),
            $relation->getInvoiceKey()
        ];
        
        try {
            /** @var false $result */
            $result = $this->storeIterator->call($this->orderIntegrator, 'invoice', $params, $relation->getStore());
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
            $relation->setData('error_message', $e->getMessage());
        }
        
        if (!$result) {
            $relation->setData('error_message', __('The invoice key could be created.'));
        }
    }
}
