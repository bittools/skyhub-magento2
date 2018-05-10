<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog;

use BitTools\SkyHub\Integration\Transformer\AbstractTransformer;
use Magento\Catalog\Model\Category as CatalogCategory;

class Category extends AbstractTransformer
{
    
    /**
     * @param CatalogCategory $category
     *
     * @return \SkyHub\Api\EntityInterface\Catalog\Category
     */
    public function convert(CatalogCategory $category)
    {
        /** @var \BitTools\SkyHub\Helper\Catalog\Category $helper */
        $helper = $this->context->objectManager()->create(\BitTools\SkyHub\Helper\Catalog\Category::class);
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Category $interface */
        $interface = $this->context->api()->category()->entityInterface();
        $interface->setCode($category->getId())
            ->setName($helper->extractProductCategoryPathString($category));
        
        return $interface;
    }
}
