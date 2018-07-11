<?php

namespace BitTools\SkyHub\Helper\Catalog\Product;

use BitTools\SkyHub\Helper\AbstractHelper;
use BitTools\SkyHub\Helper\Context;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EntityAttributeCollection;
use Magento\Customer\Model\Customer as Customer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Eav\Setup\EavSetupFactory;

class Attribute extends AbstractHelper
{
    
    /** @var AttributeSetCollection */
    protected $attributeCollection;
    
    /** @var array */
    protected $customerAttributes   = [];
    
    /** @var array */
    protected $attributesWhitelist = [];
    
    /** @var array */
    protected $entityTypes         = [];
    
    /** @var EavSetupFactory */
    protected $eavSetupFactory;
    
    
    public function __construct(Context $context, EavSetupFactory $eavSetupFactory)
    {
        parent::__construct($context);
        
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    
    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    public function getAttributeByCode($attributeCode)
    {
        $this->initCustomerAttributes();
        
        if (!isset($this->customerAttributes[$attributeCode])) {
            return false;
        }
        
        return $this->customerAttributes[$attributeCode];
    }
    
    
    /**
     * @param int $attributeId
     *
     * @return bool|EntityAttribute
     */
    public function getAttributeById($attributeId)
    {
        $this->initCustomerAttributes();
        
        /** @var EntityAttribute $attribute */
        foreach ($this->customerAttributes as $attribute) {
            if ($attributeId == $attribute->getId()) {
                return $attribute;
            }
        }
        
        return false;
    }
    
    
    /**
     * @return array
     */
    public function getAllAttributeIds()
    {
        $attributeIds = [];
        
        /** @var EntityAttribute $attribute */
        foreach ($this->customerAttributes as $attribute) {
            $attributeIds[$attribute->getId()] = $attribute;
        }
        
        return $attributeIds;
    }
    
    
    /**
     * @return array
     */
    public function initCustomerAttributes()
    {
        if (!empty($this->customerAttributes)) {
            return $this->customerAttributes;
        }
        
        /** @var EntityAttribute $attribute */
        foreach ($this->getCustomerAttributesCollection() as $attribute) {
            $this->customerAttributes[$attribute->getAttributeCode()] = $attribute;
        }
        
        return $this->customerAttributes;
    }
    
    
    /**
     * @return EntityAttributeCollection
     */
    public function getCustomerAttributesCollection()
    {
        /** @var EntityAttributeCollection $collection */
        $collection = $this->context->objectManager()->create(EntityAttributeCollection::class);
        return $collection;
    }
    
    
    /**
     * @return array
     */
    public function getIntegrableCustomerAttributes()
    {
        $integrable = [];
        
        /** @var EntityAttribute $attribute */
        foreach ($this->getCustomerAttributesCollection() as $attribute) {
            if (!$this->validateAttributeForIntegration($attribute)) {
                continue;
            }
            
            $integrable[$attribute->getId()] = $attribute;
        }
        
        return $integrable;
    }
    
    
    /**
     * @param array $ids
     * @param array $excludeIds
     *
     * @return array
     */
    public function getCustomerAttributes(array $ids = [], array $excludeIds = [])
    {
        $this->initCustomerAttributes();
        
        $attributes = [];
        
        /**
         * @var string          $code
         * @var EntityAttribute $attribute
         */
        foreach ($this->customerAttributes as $code => $attribute) {
            if (!empty($ids) && !in_array($attribute->getId(), $ids)) {
                continue;
            }
            
            if (!empty($excludeIds) && in_array($attribute->getId(), $excludeIds)) {
                continue;
            }
            
            $attributes[$code] = $attribute;
        }
        
        return $attributes;
    }
    
    
    /**
     * @param string $code
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType($code)
    {
        if (isset($this->entityTypes[$code])) {
            return $this->entityTypes[$code];
        }
        
        /** @var \Magento\Eav\Model\Entity\Type $type */
        $type = $this->context->objectManager()->create(\Magento\Eav\Model\Entity\Type::class);
        $type->loadByCode($code);
        
        $this->entityTypes[$code] = $type;
        
        return $type;
    }
    
    
    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    public function isAttributeCodeInBlacklist($attributeCode)
    {
        return $this->context
            ->skyhubConfig()
            ->isAttributeCodeInBlacklist($attributeCode);
    }
    
    
    /**
     * @param string $code
     * @param array  $attributeData
     *
     * @return EntityAttribute
     *
     * @throws \Exception
     */
    public function createCustomerAttribute($code, array $attributeData)
    {
        /**
         * @todo
         */
    }
    
    /**
     * @param string $code
     *
     * @return EntityAttribute
     *
     * @throws \Exception
     */
    public function loadCustomerAttribute($code)
    {
        /** @var EntityAttribute $attribute */
        $attribute = $this->context->objectManager()->create(EntityAttribute::class);
        $attribute->loadByCode(Customer::ENTITY, $code);
        
        return $attribute;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool
     */
    public function validateAttributeForIntegration(EntityAttribute $attribute)
    {
        if (!$attribute->getStoreLabel()) {
            return false;
        }
        
        if ($this->isAttributeCodeInBlacklist($attribute->getAttributeCode())) {
            return false;
        }
        
        return true;
    }
}
