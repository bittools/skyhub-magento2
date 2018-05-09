<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Integration\Integrator\AbstractIntegrator;

abstract class AbstractCatalog extends AbstractIntegrator
{
    
    /**
     * @return BSeller_SkyHub_Model_Resource_Entity
     */
    protected function getEntityResource()
    {
        return Mage::getResourceSingleton('bseller_skyhub/entity');
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function registerProductEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_PRODUCT);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function registerProductAttributeEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_PRODUCT_ATTRIBUTE);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function registerCategoryEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_CATEGORY);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function productExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_PRODUCT);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function productAttributeExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_PRODUCT_ATTRIBUTE);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function categoryExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_CATEGORY);
    }
    
    
    /**
     * @param int $id
     *
     * @return bool
     */
    protected function updateProductEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->updateEntity((int) $id, BSeller_SkyHub_Model_Entity::TYPE_CATALOG_PRODUCT);
    }
}
