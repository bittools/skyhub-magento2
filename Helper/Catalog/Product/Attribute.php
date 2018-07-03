<?php

namespace BitTools\SkyHub\Helper\Catalog\Product;

use BitTools\SkyHub\Helper\AbstractHelper;
use BitTools\SkyHub\Helper\Context;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EntityAttributeCollection;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Eav\Setup\EavSetupFactory;

class Attribute extends AbstractHelper
{
    
    /** @var AttributeSetCollection */
    protected $attributeCollection;
    
    /** @var array */
    protected $productAttributes   = [];
    
    /** @var array */
    protected $attributesWhitelist = [];
    
    /** @var array */
    protected $entityTypes         = [];
    
    /** @var CatalogProduct */
    protected $product;
    
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
    public function getAttributeById($attributeId)
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
    public function getAllAttributeIds()
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
    public function initProductAttributes()
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
     * @return EntityAttributeCollection
     */
    public function getProductAttributesCollection()
    {
        /** @var EntityAttributeCollection $collection */
        $collection = $this->context->objectManager()->create(EntityAttributeCollection::class);
        $collection->setEntityTypeFilter($this->getEntityType(CatalogProduct::ENTITY));
        
        return $collection;
    }
    
    
    /**
     * @return array
     */
    public function getIntegrableProductAttributes()
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
    public function getProductAttributes(array $ids = [], array $excludeIds = [])
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
    public function createProductAttribute($code, array $attributeData)
    {
        $groupName = 'BitTools SkyHub';
        $groupCode = 'bittools-skyhub';

        /** @var \BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute\Set $resource */
        $resource = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute\Set::class);

        $entityId = $this->getEntityType(CatalogProduct::ENTITY)->getId();
        $result   = $resource->setupEntityAttributeGroups($entityId, $groupName, $groupCode);
        
        if (!$result) {
            throw new \Exception(__('The attribute group could not be created.'));
        }
        
        $attributeData['group'] = $groupName;
        
        /** @var \Magento\Eav\Setup\EavSetup $installer */
        $installer = $this->eavSetupFactory->create();
        ;
        $installer->addAttribute(CatalogProduct::ENTITY, $code, $attributeData);
        
        return $this->loadProductAttribute($code);
    }
    
    
    /**
     * @param string $groupName
     *
     * @return $this
     */
    public function initSkyHubAttributeGroup($groupName)
    {
        /** @var AttributeSetCollection $collection */
        $collection = $this->context->objectManager()->create(AttributeSetCollection::class);
        ;
        $collection->setEntityTypeFilter($this->getEntityType(CatalogProduct::ENTITY)->getId());
        
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        foreach ($collection as $attributeSet) {
            try {
                /** @var \Magento\Eav\Model\Entity\Attribute\Group Mage_Eav_Model_Entity_Attribute_Group $group */
                $group = $this->context->objectManager()->create(\Magento\Eav\Model\Entity\Attribute\Group::class);
                $group->setAttributeSetId($attributeSet->getId())
                    ->setAttributeGroupName($groupName)
                    ->setSortOrder(900);
                
                $group->save();
            } catch (\Exception $e) {
                $this->context->logger()->critical($e);
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param string $code
     *
     * @return EntityAttribute
     *
     * @throws \Exception
     */
    public function loadProductAttribute($code)
    {
        /** @var EntityAttribute $attribute */
        $attribute = $this->context->objectManager()->create(EntityAttribute::class);
        $attribute->loadByCode(CatalogProduct::ENTITY, $code);
        
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
