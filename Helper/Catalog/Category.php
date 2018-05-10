<?php

namespace BitTools\SkyHub\Helper\Catalog;

use Magento\Catalog\Model\Category as CatalogCategory;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;

class Category
{
    
    /** @var CategoryResourceFactory */
    protected $resourceCategoryFactory;
    
    
    /**
     * Category constructor.
     *
     * @param CategoryResourceFactory $resourceCategoryFactory
     */
    public function __construct(CategoryResourceFactory $resourceCategoryFactory)
    {
        $this->resourceCategoryFactory = $resourceCategoryFactory;
    }
    
    
    /**
     * @param CatalogCategory $category
     * @param null            $store
     *
     * @return string
     */
    public function extractProductCategoryPathString(CatalogCategory $category, $store = null)
    {
        $ids            = $this->getCategoryPathIds($category, $store);
        $categoryPieces = [];
        
        foreach ($ids as $id) {
            $name = $category->getName();
            
            if (!$name || !($id == $category->getId())) {
                $name = $this->getResource()->getAttributeRawValue($id, 'name', $store);
            }
            
            $categoryPieces[] = $name;
        }
        
        return implode(' > ', $categoryPieces);
    }
    
    
    /**
     * @param CatalogCategory $category
     * @param string|null     $scopeCode
     *
     * @return array
     */
    public function getCategoryPathIds(CatalogCategory $category, $scopeCode = null)
    {
        $ids     = array_reverse(explode('/', $category->getPath()));
        $pathIds = [];
        
        /** @var int $id */
        foreach ($ids as $id) {
            /**
             * @todo Check how to apply this filter to ignore the root category from category paths.
             */
            /*
            if ($id == $scopeCode->getRootCategoryId()) {
                break;
            }
            */
            
            $pathIds[] = (int) $id;
        }
        
        return (array) array_reverse($pathIds);
    }
    
    
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Category
     */
    protected function getResource()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category $resource */
        $resource = $this->resourceCategoryFactory->create();
        return $resource;
    }
}
