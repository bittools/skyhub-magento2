<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Model\Entity as EntityModel;
use BitTools\SkyHub\Model\ResourceModel\Entity as EntityResourceModel;
use BitTools\SkyHub\Integration\Integrator\AbstractIntegrator;

abstract class AbstractCatalog extends AbstractIntegrator
{
    
    /**
     * @return EntityResourceModel
     */
    protected function getEntityResource()
    {
        /** @var EntityResourceModel $resource */
        $resource = $this->context->objectManager()->get('BitTools\SkyHub\Model\ResourceModel\Entity');
        return $resource;
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function registerProductEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, EntityModel::TYPE_CATALOG_PRODUCT);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function registerProductAttributeEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, EntityModel::TYPE_CATALOG_PRODUCT_ATTRIBUTE);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function registerCategoryEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->createEntity((int) $id, EntityModel::TYPE_CATALOG_CATEGORY);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function productExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, EntityModel::TYPE_CATALOG_PRODUCT);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function productAttributeExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, EntityModel::TYPE_CATALOG_PRODUCT_ATTRIBUTE);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function categoryExists($id)
    {
        return (bool) $this->getEntityResource()
            ->entityExists((int) $id, EntityModel::TYPE_CATALOG_CATEGORY);
    }
    
    
    /**
     * @param integer $id
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function updateProductEntity($id)
    {
        return (bool) $this->getEntityResource()
            ->updateEntity((int) $id, EntityModel::TYPE_CATALOG_PRODUCT);
    }
}
