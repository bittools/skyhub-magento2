<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use BitTools\SkyHub\Model\ResourceModel\Queue;
use BitTools\SkyHub\Observer\Sales\AbstractSales;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class ReIntegrateOrderProducts extends AbstractSales
{
    /**
     * @param Observer $observer
     *
     * @throws Exception
     * @return void
     *
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        if (!$order || !$order->getEntityId()) {
            return;
        }

        $items = $order->getAllVisibleItems();
        $productIds = [];

        /** @var Item $item */
        foreach ($items as $item) {
            /** @var ProductInterface $product */
            $product = $item->getProduct();

            if (!$this->productValidation->canIntegrateProduct($product)) {
                continue;
            }

            $success = true;
            $integrateProductOnSave = $this->context
                ->configContext()
                ->catalog()
                ->hasActiveIntegrateOnSaveFlag();

            if ($integrateProductOnSave) {
                /**
                 * integrate all order items on skyhub (mainly to update stock qty)
                 */
                $response = $this->productIntegrator->createOrUpdate($product);

                if ($response && $response->exception()) {
                    $success = false;
                }
            }

            if (!$success || !$integrateProductOnSave) {
                $productIds[] = $product->getId();
            }
        }

        if (empty($productIds)) {
            return;
        }

        /** @var Queue $queueResource */
        $queueResource = $this->queueResourceFactory->create();

        /** Queue product. */
        $queueResource->queue(
            $productIds,
            \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_PRODUCT,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT
        );
    }
}
