<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog\Product\Variation\Type;

use BitTools\SkyHub\Integration\Transformer\Catalog\AbstractProduct;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;
use Magento\Catalog\Model\Product;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductEntityInterface;

abstract class AbstractType extends AbstractProduct implements TypeInterface
{
    
    /**
     * @param Product                $product
     * @param ProductEntityInterface $interface
     *
     * @return ProductEntityInterface\Variation
     */
    protected function addVariation(Product $product, ProductEntityInterface $interface)
    {
        /** @var ProductEntityInterface\Variation $variation */
        $variation = $interface->addVariation($product->getSku(), $this->getStockQty($product));
        
        /**
         * EAN Attribute
         *
         * @var Mapping $mapping
         */
        $mapping = $this->attributeMappingHelper->getMappedAttribute('ean');
        
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        if ($mapping->getId() && $mapping->getAttribute()->getId()) {
            $ean = $mapping->extractProductValue($product);
            $variation->setEan($ean);
        }
        
        /**
         * Product Images.
         */
        $this->addImagesToVariation($product, $variation);
        
        /**
         * Product Variations.
         */
        $this->addSpecificationsToVariation($product, $variation);
        
        $this->context->eventManager()->dispatch('bseller_skyhub_product_variation_create_after', [
            'product'   => $product,
            'variation' => $variation,
        ]);
        
        return $variation;
    }
    
    
    /**
     * @param Product $product
     * @param ProductEntityInterface\Variation          $variation
     *
     * @return $this
     */
    protected function addSpecificationsToVariation(Product $product, ProductEntityInterface\Variation $variation)
    {
        $this->addMappedAttributesToProductVariation($product, $variation);
        $this->addPricesToProductVariation($product, $variation);
        
        return $this;
    }
    
    
    /**
     * @param Product $product
     * @param ProductEntityInterface\Variation          $variation
     *
     * @return $this
     */
    protected function addPricesToProductVariation(Product $product, ProductEntityInterface\Variation $variation)
    {
        /**
         * @var Mapping $mappedPrice
         * @var Mapping $mappedSpecialPrice
         */
        $mappedPrice        = $this->attributeMappingHelper->getMappedAttribute('price');
        $mappedSpecialPrice = $this->attributeMappingHelper->getMappedAttribute('promotional_price');
        
        /**
         * @var \Magento\Eav\Model\Entity\Attribute $attributePrice
         * @var \Magento\Eav\Model\Entity\Attribute $attributeSpecialPrice
         */
        $attributePrice        = $mappedPrice->getAttribute();
        $attributeSpecialPrice = $mappedSpecialPrice->getAttribute();
        
        $price = $this->productHelper->extractProductPrice($product, $attributePrice);
        
        if (!empty($price)) {
            $variation->addSpecification($mappedPrice->getSkyhubCode(), (float) $price);
        }
        
        $specialPrice = $this->productHelper->extractProductSpecialPrice($product, $attributeSpecialPrice, $price);
        
        if (!empty($specialPrice)) {
            $variation->addSpecification($mappedSpecialPrice->getSkyhubCode(), (float) $specialPrice);
        }
        
        return $this;
    }
    
    
    /**
     * @param Product                           $product
     * @param ProductEntityInterface\Variation  $variation
     *
     * @return $this
     */
    protected function addMappedAttributesToProductVariation(
        Product $product,
        ProductEntityInterface\Variation $variation
    ) {
        /** @var Mapping $mappedAttribute */
        foreach ($this->getFixedMappedAttributes() as $mappedAttribute) {
            $code  = $mappedAttribute->getAttribute()->getAttributeCode();
            $value = $mappedAttribute->extractProductValue($product);
            
            if (empty($code) || empty($value)) {
                continue;
            }
            
            $variation->addSpecification($code, $value);
        }
        
        return $this;
    }
    
    
    /**
     * @return array
     */
    protected function getFixedMappedAttributes()
    {
        return [
            'weight' => $this->attributeMappingHelper->getMappedAttribute('weight'),
            'height' => $this->attributeMappingHelper->getMappedAttribute('height'),
            'length' => $this->attributeMappingHelper->getMappedAttribute('length'),
            'width'  => $this->attributeMappingHelper->getMappedAttribute('width'),
        ];
    }
    
    
    /**
     * @param Product $product
     *
     * @return float
     */
    protected function getStockQty(Product $product)
    {
        return (float) $this->stockState->getStockQty($product->getId());
    }
    
    
    /**
     * @param Product                          $product
     * @param ProductEntityInterface\Variation $variation
     *
     * @return $this
     */
    protected function addImagesToVariation(Product $product, ProductEntityInterface\Variation $variation)
    {
        $images = (array) $this->getProductGalleryImages($product);
        
        /** @var array $image */
        foreach ($images as $image) {
            $url = $this->arrayExtract($image, 'url');
            
            if (empty($url)) {
                continue;
            }
            
            $variation->addImage($url);
        }
        
        return $this;
    }
}
