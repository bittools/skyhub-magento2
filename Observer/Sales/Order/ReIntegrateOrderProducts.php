<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use Magento\Framework\Event\Observer;

class ReIntegrateOrderProducts extends AbstractOrder
{
    
    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
    
        if (!$order || !$order->getEntityId()) {
            return;
        }
    
        /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation $validator */
        $validator = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation::class);
    
        $items      = $order->getAllVisibleItems();
        $productIds = [];
        
        /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Product $integrator */
        $integrator = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Integration\Integrator\Catalog\Product::class);;
    
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $item->getProduct();
            
            if (!$validator->canIntegrateProduct($product)) {
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
                $response = $integrator->createOrUpdate($product);
            
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
    
        /** @var \BitTools\SkyHub\Model\ResourceModel\Queue $queueResource */
        $queueResource = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Model\ResourceModel\Queue::class);
        
        /**
         * put the product on the line
         */
        $queueResource->queue(
            $productIds,
            \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_PRODUCT,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT
        );
    }
}
