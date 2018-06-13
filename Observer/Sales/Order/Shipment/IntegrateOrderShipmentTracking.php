<?php

namespace BitTools\SkyHub\Observer\Sales\Order\Shipment;

use BitTools\SkyHub\Observer\Sales\AbstractSales;
use Magento\Framework\Event\Observer;

class IntegrateOrderShipmentTracking extends AbstractSales
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $observer->getData('track');
        
        if (!$track || !$track->getEntityId()) {
            return;
        }
        
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($track->getOrderId());
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
            return;
        }
        
        $items = [];
        
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'sku' => (string) $item->getSku(),
                'qty' => (int)    $item->getQtyOrdered(),
            ];
        }
        
        $shippingMethod = $order->getShippingMethod();
        
        try {
            $params = [
                $order->getId(),
                $items,
                $track->getNumber(),
                $track->getTitle(),
                $shippingMethod,     // Track method like SEDEX...
                ''                   // Tracking URL (www.correios.com.br)
            ];
            
            $this->storeIterator->call($this->orderIntegrator, 'shipment', $params, $order->getStore());
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
    }
}
