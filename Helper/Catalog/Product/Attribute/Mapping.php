<?php

namespace BitTools\SkyHub\Helper\Catalog\Product\Attribute;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes\Mapping\Collection as AttributesMappingCollection;

class Mapping
{
    
    /** @var Context */
    protected $context;
    
    /** @var array */
    protected $mappedAttributes = [];
    
    
    public function __construct(Context $context)
    {
        $this->context = $context;
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
            
            /** @var \BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping $mappedAttribute */
            foreach ($this->getMappedAttributesCollection() as $mappedAttribute) {
                $this->mappedAttributes[$mappedAttribute->getSkyhubCode()] = $mappedAttribute;
            }
        }
        
        return $this;
    }
    
    
    /**
     * @return AttributesMappingCollection
     */
    public function getMappedAttributesCollection()
    {
        /** @var AttributesMappingCollection $collection */
        $collection = $this->context->objectManager()->create(AttributesMappingCollection::class);
        $collection->setMappedAttributesFilter();
        
        return $collection;
    }
}
