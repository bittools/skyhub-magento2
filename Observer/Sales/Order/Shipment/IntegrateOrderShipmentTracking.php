<?php

namespace BitTools\SkyHub\Observer\Sales\Order\Shipment;

use BitTools\SkyHub\Cron\Queue\Sales\Order\Queue;
use BitTools\SkyHub\Observer\Sales\AbstractSales;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment\Track;

class IntegrateOrderShipmentTracking extends AbstractSales
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->context->registryManager()->registry(Queue::QUEUE_PROCESS)) {
            return;
        }

        /** @var Track $track */
        $track = $observer->getData('track');

        if (!$track || !$track->getEntityId()) {
            return;
        }

        try {
            /** @var Order $order */
            $order = $this->orderRepository->get($track->getOrderId());
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
            return;
        }

        $items = [];

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'sku' => (string)$item->getSku(),
                'qty' => (int)$item->getQtyOrdered(),
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
