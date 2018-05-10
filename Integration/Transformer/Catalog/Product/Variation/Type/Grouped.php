<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog\Product\Variation\Type;

use Magento\Catalog\Model\Product;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductInterface;

class Grouped extends AbstractType
{
    
    /**
     * @param Product          $product
     * @param ProductInterface $interface
     *
     * @return $this
     */
    public function create(Product $product, ProductInterface $interface)
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
        $typeInstance = $product->getTypeInstance();
        
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection */
        $collection = $typeInstance->getAssociatedProductCollection($product);
        
        /**
         * @var int     $optionId
         * @var Product $childProduct
         */
        foreach ($collection as $productId => $childProduct) {
            /** @var ProductInterface\Variation $variation */
            $this->addVariation($childProduct, $interface);
        }
        
        return $this;
    }
}
