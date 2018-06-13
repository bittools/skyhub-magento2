<?php

namespace BitTools\SkyHub\Observer\Catalog\Product;

use BitTools\SkyHub\Observer\Catalog\AbstractCatalog;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class DeleteProduct extends AbstractCatalog
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->storeIterator->iterate($this, 'processDeleteProduct', $observer);
    }
    
    
    /**
     * @param Observer       $observer
     * @param StoreInterface $store
     */
    public function processDeleteProduct(Observer $observer, StoreInterface $store)
    {
        if (!$this->canRun($store->getId())) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getData('product');
        
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return;
        }
        
        /** Create or Update Product */
        $this->productIntegrator->delete($product->getSku());
    }
}
