<?php

namespace BitTools\SkyHub\Observer\Sales\Order\Invoice;

use BitTools\SkyHub\Api\Data\OrderInterface;
use BitTools\SkyHub\Cron\Queue\Sales\Order\Queue;
use BitTools\SkyHub\Observer\Sales\AbstractSales;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class IntegrateInvoiceKey extends AbstractSales
{

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->context->registryManager()->registry(Queue::QUEUE_PROCESS)) {
            return;
        }

        /** @var OrderInterface $relation */
        $relation = $observer->getData('order_relation');

        if (!$relation || !$relation->getId() || !$relation->getInvoiceKey()) {
            return;
        }

        /** @var StoreInterface $store */
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
