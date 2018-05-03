<?php

namespace BitTools\SkyHub\Traits\Catalog\Product;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;

trait Attribute
{
    
    /** @var AttributeCollection */
    protected $attributeCollection;
    
    /** @var array */
    protected $productAttributes   = [];
    
    /** @var array */
    protected $attributesWhitelist = [];
    
    /** @var array */
    protected $entityTypes         = [];
    
    /** @var Product */
    protected $product;
    
    
    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    protected function getAttributeByCode($attributeCode)
    {
        $this->initProductAttributes();
        
        if (!isset($this->productAttributes[$attributeCode])) {
            return false;
        }
        
        return $this->productAttributes[$attributeCode];
    }
    
    
    /**
     * @param int $attributeId
     *
     * @return bool|EntityAttribute
     */
    protected function getAttributeById($attributeId)
    {
        $this->initProductAttributes();
        
        /** @var EntityAttribute $attribute */
        foreach ($this->productAttributes as $attribute) {
            if ($attributeId == $attribute->getId()) {
                return $attribute;
            }
        }
        
        return false;
    }
    
    
    /**
     * @return array
     */
    protected function getAllAttributeIds()
    {
        $attributeIds = [];
        
        /** @var EntityAttribute $attribute */
        foreach ($this->productAttributes as $attribute) {
            $attributeIds[$attribute->getId()] = $attribute;
        }
        
        return $attributeIds;
    }
    
    
    /**
     * @return array
     */
    protected function initProductAttributes()
    {
        if (!empty($this->productAttributes)) {
            return $this->productAttributes;
        }
        
        /** @var EntityAttribute $attribute */
        foreach ($this->getProductAttributesCollection() as $attribute) {
            $this->productAttributes[$attribute->getAttributeCode()] = $attribute;
        }
        
        return $this->productAttributes;
    }
    
    
    /**
     * @return AttributeCollection
     */
    protected function getProductAttributesCollection()
    {
        /** @var AttributeCollection $collection */
        $collection = Mage::getResourceModel('eav/entity_attribute_collection');
        $collection->setEntityTypeFilter($this->getEntityType(Product::ENTITY));
        
        return $collection;
    }
    
    
    /**
     * @return array
     */
    protected function getIntegrableProductAttributes()
    {
        $integrable = [];
        
        /** @var EntityAttribute $attribute */
        foreach ($this->getProductAttributesCollection() as $attribute) {
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
    protected function getProductAttributes(array $ids = [], array $excludeIds = [])
    {
        $this->initProductAttributes();
        
        $attributes = [];
        
        /**
         * @var string          $code
         * @var EntityAttribute $attribute
         */
        foreach ($this->productAttributes as $code => $attribute) {
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
    protected function getEntityType($code)
    {
        if (isset($this->entityTypes[$code])) {
            return $this->entityTypes[$code];
        }
        
        /** @var \Magento\Eav\Model\Entity\Type $type */
        $type = Mage::getModel('eav/entity_type');
        $type->loadByCode($code);
        
        $this->entityTypes[$code] = $type;
        
        return $type;
    }
    
    
    /**
     * @return array
     */
    protected function getProductAttributeBlacklist()
    {
        /** @var BSeller_SkyHub_Model_Config $config */
        $config = Mage::getSingleton('bseller_skyhub/config');
        return $config->getBlacklistedAttributes();
    }
    
    
    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    protected function isAttributeCodeInBlacklist($attributeCode)
    {
        /** @var BSeller_SkyHub_Model_Config $config */
        $config = Mage::getSingleton('bseller_skyhub/config');
        return $config->isAttributeCodeInBlacklist($attributeCode);
    }
    
    
    /**
     * @param string $code
     * @param array  $attributeData
     *
     * @return EntityAttribute
     *
     * @throws Mage_Core_Exception
     */
    protected function createProductAttribute($code, array $attributeData)
    {
        $groupName = 'BSeller SkyHub';
        
        /** @var BSeller_SkyHub_Model_Resource_Eav_Entity_Attribute_Set $resource */
        $resource = Mage::getResourceModel('bseller_skyhub/eav_entity_attribute_set');
        $result   = $resource->setupEntityAttributeGroups(
            $this->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId(),
            $groupName
        );
        
        if (!$result) {
            Mage::throwException(__('The attribute group could not be created.'));
        }
        
        $attributeData['group'] = $groupName;
        
        /** @var Mage_Eav_Model_Entity_Setup $installer */
        $installer = Mage::getModel('eav/entity_setup', 'core_setup');
        $installer->startSetup();
        $installer->addAttribute(Product::ENTITY, $code, $attributeData);
        $installer->endSetup();
        
        return $this->loadProductAttribute($code);
    }
    
    
    /**
     * @param string $groupName
     *
     * @return $this
     */
    protected function initSkyHubAttributeGroup($groupName)
    {
        /** @var AttributeSetCollection $setCollection */
        $setCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
        $setCollection->setEntityTypeFilter($this->getEntityType(Product::ENTITY)->getId());
        
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            try {
                /** @var \Magento\Eav\Model\Entity\Attribute\Group $group */
                $group = Mage::getModel('eav/entity_attribute_group');
                $group->setAttributeSetId($attributeSet->getId())
                    ->setAttributeGroupName($groupName)
                    ->setSortOrder(900)
                ;
                
                $group->save();
            } catch (\Exception $e) {
                throw new $e;
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param $code
     *
     * @return EntityAttribute
     *
     * @throws \Exception
     */
    protected function loadProductAttribute($code)
    {
        /** @var EntityAttribute $attribute */
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Product::ENTITY, $code);
        return $attribute;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool
     */
    protected function validateAttributeForIntegration(EntityAttribute $attribute)
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
