<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog\Product\Variation\Type;

use Magento\Catalog\Model\Product;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductEntityInterface;

interface TypeInterface
{
    
    /**
     * @param Product                $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     */
    public function create(Product $product, ProductEntityInterface $interface);
}
