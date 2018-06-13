<?php

namespace BitTools\SkyHub\Observer\Catalog\Category;

use BitTools\SkyHub\Observer\AbstractObserver;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class DeleteCategory extends AbstractObserver
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->storeIterator->iterate($this, 'processDeleteCategory', $observer);
    }
    
    
    /**
     * @param Observer       $observer
     * @param StoreInterface $store
     */
    public function processDeleteCategory(Observer $observer, StoreInterface $store)
    {
        if (!$this->canRun($store->getId())) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getData('category');
        
        if (!$this->categoryValidation->canIntegrateCategory($category)) {
            return;
        }
        
        /** Create or Update Product */
        $this->categoryIntegrator->delete($category->getId());
    }
}
