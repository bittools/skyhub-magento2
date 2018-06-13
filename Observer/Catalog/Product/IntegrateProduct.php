<?php

namespace BitTools\SkyHub\Observer\Catalog\Product;

use BitTools\SkyHub\Observer\Catalog\AbstractCatalog;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class IntegrateProduct extends AbstractCatalog
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->storeIterator->iterate($this, 'prepareIntegrationProduct', $observer);
    }
    
    
    /**
     * @param Observer       $observer
     * @param StoreInterface $store
     *
     * @throws \Exception
     */
    public function prepareIntegrationProduct(Observer $observer, StoreInterface $store)
    {
        if (!$this->canRun($store->getId())) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getData('product');
        $this->processIntegrationProduct($product);
    }
    
    
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool                           $forceQueue
     *
     * @throws \Exception
     */
    protected function processIntegrationProduct(\Magento\Catalog\Model\Product $product, $forceQueue = false)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $this->context
            ->objectManager()
            ->create(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
        
        $parentIds = (array) $typeInstance->getParentIdsByChild($product->getId());
        
        /** @var integer $parentId */
        foreach ($parentIds as $parentId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($parentId);
            
            if (!$product) {
                continue;
            }
            
            $this->processIntegrationProduct($product, true);
        }
        
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return;
        }
        
        $hasActiveIntegrateOnSaveFlag = $this->context->configContext()->catalog()->hasActiveIntegrateOnSaveFlag();
        if ($hasActiveIntegrateOnSaveFlag && $this->hasStockOrPriceUpdate($product)) {
            /** Create or Update Product */
            $this->productIntegrator->createOrUpdate($product);
        }
        
        if ($forceQueue) {
            /** @var \BitTools\SkyHub\Model\ResourceModel\Queue $queueResource */
            $queueResource = $this->queueResourceFactory->create();
    
            $queueResource->queue(
                $product->getId(),
                \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_PRODUCT,
                \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT
            );
        }
    }
    
    
    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    protected function hasStockOrPriceUpdate(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getOrigData('price') != $product->getData('price')) {
            return true;
        }
        
        if ($product->getOrigData('special_price') != $product->getData('special_price')) {
            return true;
        }
        
        if ($product->getOrigData('promotional_price') != $product->getData('promotional_price')) {
            return true;
        }
        
        if ($product->getStockData('qty') != $product->getStockData('original_inventory_qty')) {
            return true;
        }
        
        return false;
    }
}
