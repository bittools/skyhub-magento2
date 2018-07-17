<?php

namespace BitTools\SkyHub\Helper\Customer\Attribute;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping\Collection as AttributesMappingCollection;
use BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping\Options\Collection as AttributesMappingOptionsCollection;
use Magento\Framework\Registry;

class Mapping
{
    
    /** @var Context */
    protected $context;
    
    /** @var Registry */
    protected $registry;
    
    /** @var array */
    protected $mappedAttributes = [];
    
    
    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        $this->context  = $context;
        $this->registry = $registry;
    }
    
    
    /**
     * @param string $skyhubCode
     *
     * @return mixed|null
     */
    public function getMappedAttribute($skyhubCode)
    {
        $this->initMappedAttributes();
        
        if (isset($this->mappedAttributes[$skyhubCode])) {
            return $this->mappedAttributes[$skyhubCode];
        }
        
        return null;
    }
    
    
    /**
     * @return array
     */
    public function getMappedAttributes()
    {
        $this->initMappedAttributes();
        
        return (array) $this->mappedAttributes;
    }
    
    
    /**
     * @return $this
     */
    protected function initMappedAttributes()
    {
        if (empty($this->mappedAttributes)) {
            $this->mappedAttributes = [];
            
            /** @var \BitTools\SkyHub\Model\Customer\Attributes\Mapping $mappedAttribute */
            foreach ($this->getMappedAttributesCollection() as $mappedAttribute) {
                $this->mappedAttributes[$mappedAttribute->getSkyhubCode()] = $mappedAttribute;
            }
        }
        
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function hasPendingAttributesForMapping()
    {
        return (bool) ($this->getPendingAttributesCollection()->getSize() > 0);
    }
    
    
    /**
     * @return AttributesMappingCollection
     */
    public function getPendingAttributesCollection()
    {
        $key = 'notification_pending_attributes_collection';
        
        if (!$this->registry->registry($key)) {
            /** @var AttributesMappingCollection $collection */
            $collection = $this->getAttributesMappingCollection()
                ->setPendingAttributesFilter();
            
            $this->registry->register($key, $collection, true);
        }
        
        return $this->registry->registry($key);
    }
    
    
    /**
     * @return AttributesMappingCollection
     */
    public function getMappedAttributesCollection()
    {
        $collection = $this->getAttributesMappingCollection()
            ->setMappedAttributesFilter();
        
        return $collection;
    }
    
    
    /**
     * @return AttributesMappingCollection
     */
    public function getAttributesMappingCollection()
    {
        /** @var AttributesMappingCollection $collection */
        $collection = $this->context
            ->objectManager()
            ->create(AttributesMappingCollection::class);
        
        return $collection;
    }

    public function getAttributeMappingOptionMagentoValue($customerAttributesMappingId, $skyhubCode) {
        /** @var AttributesMappingCollection $collection */
        $collection = $this->context
            ->objectManager()
            ->create(AttributesMappingOptionsCollection::class);

        $collection->addFieldToFilter('customer_attributes_mapping_id', $customerAttributesMappingId)
            ->addFieldToFilter('skyhub_code', $skyhubCode);

        return $collection->getFirstItem();
    }
}
