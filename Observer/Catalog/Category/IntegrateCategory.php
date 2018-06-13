<?php

namespace BitTools\SkyHub\Observer\Catalog\Category;

use BitTools\SkyHub\Observer\AbstractObserver;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class IntegrateCategory extends AbstractObserver
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->storeIterator->iterate($this, 'processIntegrateCategory', $observer);
    }
    
    
    /**
     * @param Observer       $observer
     * @param StoreInterface $store
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processIntegrateCategory(Observer $observer, StoreInterface $store)
    {
        if (!$this->canRun($store->getId())) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getData('category');
        
        if (!$this->categoryValidation->canIntegrateCategory($category)) {
            return;
        }
        
        /** Create or Update Category */
        $this->categoryIntegrator->createOrUpdate($category);
    }
}
