<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Helper\Catalog\Product\Attribute\Mapping;
use BitTools\SkyHub\StoreConfig\Context;
use BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes\Mapping\Collection as AttributesMappingCollection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ResourceProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\Product\Type as SimpleProductType;
use Magento\Framework\Registry;

class ProductValidation
{
    
    /** @var ResourceProductFactory */
    protected $resourceFactory;
    
    /** @var Context */
    protected $configContext;
    
    /** @var ObjectManagerInterface */
    protected $objectManager;
    
    /** @var Registry */
    protected $registry;
    
    /** @var Mapping */
    protected $attributesMappingHelper;
    
    
    public function __construct(
        Context $configContext,
        ResourceProductFactory $resourceFactory,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        Mapping $attributesMappingHelper
    ) {
        $this->resourceFactory         = $resourceFactory;
        $this->configContext           = $configContext;
        $this->objectManager           = $objectManager;
        $this->registry                = $registry;
        $this->attributesMappingHelper = $attributesMappingHelper;
    }
    
    
    /**
     * @param ProductInterface $product
     * @param bool             $bypassVisibleCheck
     *
     * @return bool
     */
    public function canIntegrateProduct(ProductInterface $product, $bypassVisibleCheck = false)
    {
        if ($this->attributesMappingHelper->hasPendingAttributesForMapping()) {
            return false;
        }
        
        $allowedTypes = [
            SimpleProductType::TYPE_SIMPLE,
            Configurable::TYPE_CODE,
            // Grouped::TYPE_CODE,
        ];
        
        if (!in_array($product->getTypeId(), $allowedTypes)) {
            return false;
        }
        
        if (!$product->getId()) {
            return false;
        }
        
        if (!$product->getSku()) {
            return false;
        }
        
        if (!$product->hasData('visibility')) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
            $resource   = $this->resourceFactory->create();
            $visibility = $resource->getAttributeRawValue(
                $product->getId(),
                'visibility',
                $product->getStore()
            );
            
            $product->setData('visibility', $visibility);
        }
        
        if (!$bypassVisibleCheck && !$this->hasAllowedVisibility($product)) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function hasAllowedVisibility(ProductInterface $product)
    {
        $productVisibilities = $this->configContext->catalog()->getProductVisibilities();
        
        if (in_array($product->getVisibility(), $productVisibilities)) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @return bool
     * @deprecated
     */
    public function canShowAttributesNotificationBlock()
    {
        return $this->attributesMappingHelper->hasPendingAttributesForMapping();
    }
    
    
    /**
     * @return AttributesMappingCollection
     */
    public function getPendingAttributesCollection()
    {
        $key = 'notification_pending_attributes_collection';
        
        if (!$this->registry->registry($key)) {
            /** @var AttributesMappingCollection $collection */
            $collection = $this->objectManager->create(AttributesMappingCollection::class);
            $collection->setPendingAttributesFilter();
    
            $this->registry->register($key, $collection, true);
        }
        
        return $this->registry->registry($key);
    }
}
