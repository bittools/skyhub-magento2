<?php

namespace BitTools\SkyHub\Observer\Catalog\Product\Attribute;

use BitTools\SkyHub\Observer\AbstractObserver;
use Magento\Framework\Event\Observer;
use Magento\Store\Api\Data\StoreInterface;

class IntegrateProductAttribute extends AbstractObserver
{
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->storeIterator->iterate($this, 'processIntegrateProductAttribute', $observer);
    }
    
    
    /**
     * @param Observer       $observer
     * @param StoreInterface $store
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processIntegrateProductAttribute(Observer $observer, StoreInterface $store)
    {
        if (!$this->canRun($store->getId())) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $observer->getData('attribute');
        
        if (!$attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
            return;
        }
        
        $this->productAttributeIntegrator->createOrUpdate($attribute);
    }
}
