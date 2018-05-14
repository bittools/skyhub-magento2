<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use \Magento\Catalog\Model\Category as CategoryModel;

class CategoryValidation
{
    
    /**
     * @param CategoryModel $category
     *
     * @return bool
     */
    public function canIntegrateCategory(CategoryModel $category)
    {
        if (!$category->getId()) {
            return false;
        }
        
        return true;
    }
}
