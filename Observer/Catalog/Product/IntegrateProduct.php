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
        $this->storeIterator->iterate($this, 'prepareIntegrationProduct', [$observer]);
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
        $parentIds = (array) $this->typeConfigurableFactory->create()->getParentIdsByChild($product->getId());
        
        /** @var integer $parentId */
        foreach ($parentIds as $parentId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($parentId);
            
            if (!$product) {
                continue;
            }

            $product->setData('force_integration', true);
            
            $this->processIntegrationProduct($product, true);
        }
        
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return;
        }
        
        $hasActiveIntegrateOnSaveFlag = $this->context->configContext()->catalog()->hasActiveIntegrateOnSaveFlag();
        if ($hasActiveIntegrateOnSaveFlag && ($this->hasStockOrPriceUpdate($product) || $product->getData('force_integration'))) {
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
        
        if ($product->getOrigData('special_from_date') != $product->getSpecialFromDate()) {
            return true;
        }
        
        if ($product->getOrigData('special_to_date') != $product->getSpecialToDate()) {
            return true;
        }
    
        /**
         * This may stop working further once the method getStockData is deprecated.
         * @todo Change the logic to grab the current and prior stock qty.
         */
        $origData = (array) $product->getOrigData('quantity_and_stock_status');
        
        $currentQty = (float) $product->getData('stock_data/qty');
        $priorQty   = (float) $origData['qty'] ?: $currentQty;
        
        if ($currentQty != $priorQty) {
            return true;
        }
        
        return false;
    }
}
