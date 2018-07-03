<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog\Product\Variation\Type;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;
use Magento\Catalog\Model\Product;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;

class Configurable extends AbstractType
{
    
    use Functions;
    
    
    /** @var array */
    protected $configurableAttributes    = [];
    
    /** @var array */
    protected $configurableAttributesPrices = [];
    
    
    /**
     * @param Product          $product
     * @param ProductInterface $interface
     *
     * @return $this
     */
    public function create(Product $product, ProductInterface $interface)
    {
        $this->configurableAttributes       = [];
        $this->configurableAttributesPrices = [];
        
        $this->prepareProductVariationAttributes($product, $interface);
        
        /** @var array $children */
        $children = (array) $this->getChildrenProducts($product);
        
        if (empty($children)) {
            return $this;
        }
        
        /** @var Product $child */
        foreach ($children as $child) {
            $child->setData('parent_product', $product);
            
            /** @var ProductInterface\Variation $variation */
            $this->addVariation($child, $interface);
        }
        
        return $this;
    }
    
    
    /**
     * @param Product $product
     *
     * @return array
     */
    protected function getChildrenProducts(Product $product)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $usedProducts = $typeInstance->getUsedProducts($product);
        
        return $usedProducts;
    }
    
    
    /**
     * @param Product          $product
     * @param ProductInterface $interface
     *
     * @return $this
     */
    public function prepareProductVariationAttributes(Product $product, ProductInterface $interface)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($this->getConfigurableAttributes($product) as $attribute) {
            $interface->addVariationAttribute($attribute->getAttributeCode());
        }
        
        return $this;
    }
    
    
    /**
     * @param Product $product
     *
     * @return array
     */
    protected function getConfigurableAttributes(Product $product)
    {
        if (empty($this->configurableAttributes)) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
            $typeInstance = $product->getTypeInstance();
            
            /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $configurableAttributes */
            $configurableAttributes = $typeInstance->getUsedProductAttributes($product);
            
            foreach ($configurableAttributes as $attribute) {
                if (!$attribute || !$attribute->getAttributeId()) {
                    continue;
                }
                
                $this->configurableAttributes[$attribute->getId()] = $attribute;
            }
        }
        
        return (array) $this->configurableAttributes;
    }
    
    
    /**
     * @param Product                    $product
     * @param ProductInterface\Variation $variation
     *
     * @return $this
     */
    protected function addSpecificationsToVariation(Product $product, ProductInterface\Variation $variation)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute $configurableAttribute */
        foreach ($this->configurableAttributes as $configurableAttribute) {
            $code  = $configurableAttribute->getAttributeCode();
            $value = $this->productHelper->productAttributeRawValue($product, $code);
            
            try {
                $text = $this->eavOptionHelper->extractAttributeOptionValue($configurableAttribute, $value);
                // $text = $configurableAttribute->getSource()->getOptionText($value);
    
                if (!$text) {
                    continue;
                }
    
                $variation->addSpecification($code, $text);
            } catch (\Exception $e) {
            }
        }
        
        parent::addSpecificationsToVariation($product, $variation);
        
        return $this;
    }
    
    
    /**
     * @param Product                    $product
     * @param ProductInterface\Variation $variation
     *
     * @return $this
     */
    protected function addPricesToProductVariation(Product $product, ProductInterface\Variation $variation)
    {
        /** @var Product $parentProduct */
        if (!$parentProduct = $this->getParentProduct($product)) {
            $parentProduct = $product;
        }
        
        $parentProduct->setData('current_child', $product);
        
        /**
         * @var Mapping $mappedPrice
         * @var Mapping $mappedSpecialPrice
         */
        $mappedPrice        = $this->attributeMappingHelper->getMappedAttribute('price');
        $mappedSpecialPrice = $this->attributeMappingHelper->getMappedAttribute('promotional_price');
        
        /** @var \Magento\Eav\Model\Entity\Attribute $attributeSpecialPrice */
        $attributeSpecialPrice = $mappedSpecialPrice->getAttribute();
        
        /**
         * Add Price
         */
        $price = $mappedPrice->extractProductValue($product);
        
        if (!empty($price)) {
            $price = (float) $this->calculatePrice($parentProduct, (float) $price);
        } else {
            $price = null;
        }
        
        $variation->addSpecification($mappedPrice->getSkyhubCode(), $price);
        
        /**
         * Add Special Price
         */
        $specialPrice = $this->productHelper
            ->extractProductSpecialPrice($product, $attributeSpecialPrice, $price);
        
        if (!empty($specialPrice)) {
            $specialPrice = (float) $this->calculatePrice($parentProduct, (float) $specialPrice);
        } else {
            $specialPrice = null;
        }
        
        $variation->addSpecification($mappedSpecialPrice->getSkyhubCode(), $specialPrice);
        
        return $this;
    }
    
    
    /**
     * @param Product $configurableProduct
     * @param         $price
     *
     * @return float
     */
    protected function calculatePrice(Product $configurableProduct, $price)
    {
        /** @var array $priceData */
        foreach ($this->getConfigurableAttributePrices($configurableProduct) as $priceData) {
            $isPercent    = (bool)  $priceData['is_percent'];
            $pricingValue = (float) $priceData['pricing_value'];
            
            if (true === $isPercent) {
                $price += ($price * ($pricingValue/100));
            }
            
            if (false === $isPercent) {
                $price += $pricingValue;
            }
        }
        
        return (float) $price;
    }
    
    
    /**
     * @param Product $configurableProduct
     *
     * @return array
     */
    protected function getConfigurableAttributePrices(Product $configurableProduct)
    {
        $prices = [];
        
        /** @var Product $childProduct */
        if (!$childProduct = $this->getCurrentChildProduct($configurableProduct)) {
            return $prices;
        }
        
        /**
         * @var integer $attributeId
         * @var array   $pricesCollection
         */
        foreach ($this->extractConfigurableAttributePrices($configurableProduct) as $attributeId => $pricesCollection) {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->context->objectManager()->create(\Magento\Eav\Model\Entity\Attribute::class);
            $attribute->load($attributeId);
            
            $attributeValue = $this->productHelper->productAttributeRawValue($childProduct, $attribute);
            
            /** @var array $priceData */
            foreach ($pricesCollection as $priceData) {
                $valueIndex = $this->arrayExtract($priceData, 'value_index', 0.0000);
                
                if ($attributeValue != $valueIndex) {
                    continue;
                }
                
                $prices[] = $priceData;
            }
        }
        
        return $prices;
    }
    
    
    /**
     * @param Product $configurableProduct
     *
     * @return array
     */
    protected function extractConfigurableAttributePrices(Product $configurableProduct)
    {
        /**
         * @todo Check if this still is the correct way to retrieve configurable attributes.
         */
        if (empty($this->configurableAttributesPrices)) {
            /**
             * CASE 1: When a product is just saved and the is being integrated via observer [after save event].
             * @todo Check if this is correct in Magento 2;
             */
            $usedAttributes = (array) $configurableProduct->getData('configurable_attributes_data');
            
            if (empty($usedAttributes)) {
                /**
                 * CASE 2: Otherwise the product was loaded and being integrated via another way.
                 * @todo Check if this is correct in Magento 2;
                 */
                $usedAttributes = (array) $configurableProduct->getData('_cache_instance_used_attributes');
            }
            
            /** @var ConfigurableAttribute|array $usedAttribute */
            foreach ($usedAttributes as $usedAttribute) {
                $attributeId = null;
                $prices      = [];
                
                if ($usedAttribute instanceof ConfigurableAttribute) {
                    /**
                     * Refers to CASE 2 (product was loaded and integrated.)
                     */
                    $attributeId = $usedAttribute->getAttributeId();
                    $prices      = $usedAttribute->getData('prices');
                } elseif (isset($usedAttribute['attribute_id'])) {
                    /**
                     * Refers to CASE 1 (product was just saved and is being integrated via observer.)
                     */
                    $attributeId = $usedAttribute['attribute_id'];
                    $prices      = $usedAttribute['values'];
                }
                
                if (empty($attributeId) || empty($prices)) {
                    continue;
                }
                
                $this->configurableAttributesPrices[(int) $attributeId] = (array) $prices;
            }
        }
        
        return $this->configurableAttributesPrices;
    }
    
    
    /**
     * @param Product $product
     *
     * @return bool|Product
     */
    protected function getParentProduct(Product $product)
    {
        /** @var Product $parentProduct */
        $parentProduct = $product->getData('parent_product');
        
        if (!$parentProduct || !$parentProduct->getId()) {
            return false;
        }
        
        return $parentProduct;
    }
    
    
    /**
     * @param Product $parentProduct
     *
     * @return bool|Product
     */
    protected function getCurrentChildProduct(Product $parentProduct)
    {
        /** @var Product $childProduct */
        $childProduct = $parentProduct->getData('current_child');
        
        if (!$childProduct || !$childProduct->getId()) {
            return false;
        }
        
        return $childProduct;
    }
}
