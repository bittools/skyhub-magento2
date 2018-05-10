<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog;

use BitTools\SkyHub\Integration\Transformer\AbstractTransformer;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductEntityInterface;
use Magento\Catalog\Model\Product as CatalogProduct;

class Product extends AbstractTransformer
{
    
    /**
     * @param CatalogProduct $product
     *
     * @return ProductEntityInterface
     *
     * @throws \Exception
     */
    public function convert(CatalogProduct $product)
    {
        $this->initProductAttributes();
        
        /** @var ProductEntityInterface $interface */
        $interface = $this->context->api()->product()->entityInterface();
        $this->prepareMappedAttributes($product, $interface)
            ->prepareSpecificationAttributes($product, $interface)
            ->prepareProductCategories($product, $interface)
            ->prepareProductImages($product, $interface)
            ->prepareProductVariations($product, $interface);
        
        $this->context->eventManager()->dispatch('bseller_skyhub_product_convert_after', [
            'product'   => $product,
            'interface' => $interface,
        ]);
        
        return $interface;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function prepareProductVariations(CatalogProduct $product, ProductEntityInterface $interface)
    {
        switch($product->getTypeId()) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                /** @var BSeller_SkyHub_Model_Transformer_Catalog_Product_Variation_Type_Configurable $variation */
                $variation = Mage::getModel('bseller_skyhub/transformer_catalog_product_variation_type_configurable');
                $variation->create($product, $interface);
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                /** @var BSeller_SkyHub_Model_Transformer_Catalog_Product_Variation_Type_Grouped $variation */
                $variation = Mage::getModel('bseller_skyhub/transformer_catalog_product_variation_type_grouped');
                $variation->create($product, $interface);
                break;
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE:
                /** @todo Create the bundle integration if applicable. */
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
                /** @todo Create the virtual integration if applicable. */
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
            default:
                break;
        }
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function prepareProductImages(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /**
         * @todo Make sure this instructions are working properly.
         *
         * @var array $gallery
         */
        $gallery = $product->getMediaGalleryEntries();
        
        if (!$gallery || !count($gallery)) {
            return $this;
        }
        
        /** @var \Magento\Catalog\Model\Product\Gallery\Entry $image */
        foreach ($gallery as $image) {
            $url = $image->getData('url');
            $interface->addImage($url);
        }
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function prepareProductCategories(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $product->getCategoryCollection();
        $categories->addAttributeToSelect([
            'name',
        ]);
        
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categories as $category) {
            /** @var \BitTools\SkyHub\Helper\Catalog\Category $helper */
            $helper = $this->context->objectManager()->get(\BitTools\SkyHub\Helper\Catalog\Category::class);
            
            $interface->addCategory(
                $category->getId(),
                $helper->extractProductCategoryPathString($category)
            );
        }
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     */
    public function prepareSpecificationAttributes(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /**
         * Let's get the processed attributes to exclude'em from the specification list.
         */
        $processedAttributeIds = (array) $product->getData('processed_attributes');
        $remainingAttributes   = (array) $this->getProductAttributes([], array_keys($processedAttributeIds));
        
        /** @var Mage_Eav_Model_Entity_Attribute $specificationAttribute */
        foreach ($remainingAttributes as $attribute) {
            /**
             * If the specification attribute is not valid then skip.
             *
             * @var Mage_Eav_Model_Entity_Attribute $attribute
             */
            if (!$attribute || !$this->validateSpecificationAttribute($attribute)) {
                continue;
            }
            
            try {
                $value = $this->extractProductData($product, $attribute);
                
                if (empty($value)) {
                    continue;
                }
                
                //                $interface->addSpecification($attribute->getFrontend()->getLabel(), $value);
                $interface->addSpecification($attribute->getAttributeCode(), $value);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     *
     * @return bool
     */
    public function validateSpecificationAttribute(Mage_Eav_Model_Entity_Attribute $attribute)
    {
        if ($this->isAttributeCodeInBlacklist($attribute->getAttributeCode())) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     */
    public function prepareMappedAttributes(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /** @var BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping $mappedAttribute */
        foreach ($this->getMappedAttributes() as $mappedAttribute) {
            /** @var string $code */
            $code   = (string) $mappedAttribute->getSkyhubCode();
            $method = 'set'.preg_replace('/[^a-zA-Z]/', null, uc_words($code));
            
            if (!method_exists($interface, $method)) {
                continue;
            }
            
            switch ($code) {
                case 'qty':
                case 'price':
                case 'promotional_price':
                    continue;
                default:
                    /** @var Mage_Eav_Model_Entity_Attribute|bool $attribute */
                    if (!$attribute = $this->getAttributeById($mappedAttribute->getAttributeId())) {
                        $attribute = $mappedAttribute->getAttribute();
                    }
                    
                    if (!$attribute) {
                        continue;
                    }
                    
                    $value = $this->getProductAttributeValue($product, $attribute, $mappedAttribute->getCastType());
                    
                    $this->addProcessedAttribute($product, $attribute);
                    
                    call_user_func([$interface, $method], $value);
            }
        }
        
        $this->prepareProductQty($product, $interface);
        $this->prepareProductPrices($product, $interface);
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     */
    protected function prepareProductQty(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /** @var BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping $mappedAttribute */
        $mappedAttribute = $this->getMappedAttribute('qty');
        
        if (!$mappedAttribute || !$mappedAttribute->getId()) {
            return $this;
        }
        
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($product);
        
        $value = (float) $stockItem->getQty();
        
        $interface->setQty($value);
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     */
    protected function prepareProductPrices(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /**
         * @var BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping $mappedPrice
         * @var BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping $mappedPromoPrice
         */
        $mappedPrice      = $this->getMappedAttribute('price');
        $mappedPromoPrice = $this->getMappedAttribute('promotional_price');
        
        $priceCode        = $mappedPrice->getAttribute()->getAttributeCode();
        $specialPriceCode = $mappedPromoPrice->getAttribute()->getAttributeCode();
        
        /**
         * Add Price.
         */
        $price = $this->extractProductPrice($product, $priceCode);
        
        if (!empty($price)) {
            $price = (float) $price;
        } else {
            null;
        }
        
        $interface->setPrice($price);
        
        $this->addProcessedAttribute($product, $mappedPrice->getAttribute());
        
        /**
         * Add Promotional Price.
         */
        $specialPrice = $this->extractProductSpecialPrice($product, $specialPriceCode, $price);
        
        if (!empty($specialPrice)) {
            $specialPrice = (float) $specialPrice;
        } else {
            $specialPrice = null;
        }
        
        $interface->setPromotionalPrice($specialPrice);
        
        $this->addProcessedAttribute($product, $mappedPromoPrice->getAttribute());
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct      $product
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     *
     * @return $this
     */
    protected function addProcessedAttribute(
        CatalogProduct $product,
        Mage_Eav_Model_Entity_Attribute $attribute = null
    )
    {
        if (!$attribute) {
            return $this;
        }
        
        $processedAttributes = (array) $product->getData('processed_attributes');
        $processedAttributes[$attribute->getId()] = $attribute;
        
        $product->setData('processed_attributes', $processedAttributes);
        
        return $this;
    }
    
    
    /**
     * @param CatalogProduct      $product
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param null|string                     $type
     *
     * @return array|bool|float|int|mixed|string
     */
    public function getProductAttributeValue(
        CatalogProduct $product,
        Mage_Eav_Model_Entity_Attribute $attribute,
        $type = null
    )
    {
        if (!$attribute) {
            return false;
        }
        
        $value = $this->extractProductData($product, $attribute);
        $value = $this->castValue($value, $type);
        
        return $value;
    }
    
    
    /**
     * @param CatalogProduct      $product
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     *
     * @return array|bool|mixed|string
     */
    public function extractProductData(CatalogProduct $product, Mage_Eav_Model_Entity_Attribute $attribute)
    {
        $data = $this->productAttributeRawValue($product, $attribute);
        
        if ((false === $data) || is_null($data)) {
            return false;
        }
        
        switch ($attribute->getAttributeCode()) {
            case 'status':
                if ($data == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    return true;
                }
                
                if ($data == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    return false;
                }
                
                break;
        }
        
        /**
         * Attribute is from type select.
         */
        if (in_array($attribute->getFrontend()->getInputType(), ['select', 'multiselect'])) {
            try {
                $data = $this->extractAttributeOptionValue($attribute, $data, $this->getStore());
            } catch (\Exception $e) {
                // Mage::logException($e);
            }
        }
        
        if ((false !== $data) && !is_null($data)) {
            return $data;
        }
        
        return false;
    }
    
    
    /**
     * @param string $value
     * @param string $type
     *
     * @return bool|float|int|string
     */
    protected function castValue($value, $type)
    {
        switch ($type) {
            case BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping::DATA_TYPE_INTEGER:
                return (int) $value;
                break;
            case BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping::DATA_TYPE_DECIMAL:
                return (float) $value;
                break;
            case BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping::DATA_TYPE_BOOLEAN:
                return (bool) $value;
                break;
            case BSeller_SkyHub_Model_Catalog_Product_Attributes_Mapping::DATA_TYPE_STRING:
            default:
                return (string) $value;
        }
    }
    
    
    /**
     * @return Mage_Core_Model_Store
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getStore()
    {
        return Mage::app()->getStore();
    }
}
