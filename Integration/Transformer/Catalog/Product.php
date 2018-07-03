<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog;

use BitTools\SkyHub\Helper\Catalog\Product\Attribute as AttributeHelper;
use SkyHub\Api\EntityInterface\Catalog\Product as ProductEntityInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping as AttributesMapping;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

class Product extends AbstractProduct
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
        $this->attributesHelper->initProductAttributes();
        
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
        switch ($product->getTypeId()) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                /** @var Product\Variation\Type\Configurable $variation */
                $variation = $this->context->objectManager()->create(Product\Variation\Type\Configurable::class);
                $variation->create($product, $interface);
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                /** @var Product\Variation\Type\Grouped $variation */
                $variation = $this->context->objectManager()->create(Product\Variation\Type\Grouped::class);
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
        $images = (array) $this->getProductGalleryImages($product);
        
        /** @var array $image */
        foreach ($images as $image) {
            $url = $this->arrayExtract($image, 'url');
            
            if (empty($url)) {
                continue;
            }
            
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
        $remainingAttributes   = (array) $this->attributesHelper
            ->getProductAttributes([], array_keys($processedAttributeIds));
        
        /** @var EntityAttribute $specificationAttribute */
        foreach ($remainingAttributes as $attribute) {
            /**
             * If the specification attribute is not valid then skip.
             *
             * @var EntityAttribute $attribute
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
            } catch (\Exception $e) {
                $this->context->helperContext()->logger()->critical($e);
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool
     */
    public function validateSpecificationAttribute(EntityAttribute $attribute)
    {
        if ($this->skyhubConfig->isAttributeCodeInBlacklist($attribute->getAttributeCode())) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param ProductEntityInterface $interface
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareMappedAttributes(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes\Mapping\Collection $collection */
        $collection = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes\Mapping\Collection::class);
        
        /** @var AttributesMapping $mappedAttribute */
        foreach ($collection as $mappedAttribute) {
            /** @var string $code */
            $code   = (string) $mappedAttribute->getSkyhubCode();
            $method = 'set'.preg_replace('/[^a-zA-Z]/', null, ucwords($code)); /** @todo Check this ucwords() method. */
            
            if (!method_exists($interface, $method)) {
                continue;
            }
            
            switch ($code) {
                case 'qty':
                case 'price':
                case 'promotional_price':
                    continue;
                default:
                    /** @var AttributeHelper $helper */
                    $helper = $this->context->objectManager()->get(AttributeHelper::class);
                    
                    /** @var EntityAttribute|bool $attribute */
                    if (!$attribute = $helper->getAttributeById($mappedAttribute->getAttributeId())) {
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
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareProductQty(CatalogProduct $product, ProductEntityInterface $interface)
    {
        /** @var AttributesMapping $mappedAttribute */
        /**
         * @todo Confirm if it's really unnecessary.
        $mappedAttribute = $this->getMappedAttribute('qty');

        if (!$mappedAttribute || !$mappedAttribute->getId()) {
            return $this;
        }
        */
        
        $qty = (float) $this->stockState->getStockQty($product->getId());
        $interface->setQty($qty);
        
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
         * @var AttributesMapping $mappedPrice
         * @var AttributesMapping $mappedPromoPrice
         */
        $mappedPrice      = $this->attributeMappingHelper->getMappedAttribute('price');
        $mappedPromoPrice = $this->attributeMappingHelper->getMappedAttribute('promotional_price');
        
        $priceCode        = $mappedPrice->getAttribute()->getAttributeCode();
        $specialPriceCode = $mappedPromoPrice->getAttribute()->getAttributeCode();
        
        /**
         * Add Price.
         */
        $price = $this->productHelper->extractProductPrice($product, $priceCode);
        
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
        $specialPrice = $this->productHelper->extractProductSpecialPrice($product, $specialPriceCode, $price);
        
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
     * @param CatalogProduct  $product
     * @param EntityAttribute $attribute
     *
     * @return $this
     */
    protected function addProcessedAttribute(CatalogProduct $product, EntityAttribute $attribute = null)
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
     * @param CatalogProduct  $product
     * @param EntityAttribute $attribute
     * @param null|string                     $type
     *
     * @return array|bool|float|int|mixed|string
     */
    public function getProductAttributeValue(CatalogProduct $product, EntityAttribute $attribute, $type = null)
    {
        if (!$attribute) {
            return false;
        }
        
        $value = $this->extractProductData($product, $attribute);
        $value = $this->castValue($value, $type);
        
        return $value;
    }
    
    
    /**
     * @param CatalogProduct  $product
     * @param EntityAttribute $attribute
     *
     * @return array|bool|mixed|string
     */
    public function extractProductData(CatalogProduct $product, EntityAttribute $attribute)
    {
        $data = $this->productHelper->productAttributeRawValue($product, $attribute);
        
        if ((false === $data) || is_null($data)) {
            return false;
        }
        
        switch ($attribute->getAttributeCode()) {
            case 'status':
                if ($data == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                    return true;
                }
                
                if ($data == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                    return false;
                }
                
                break;
        }
        
        /**
         * Attribute is from type select.
         */
        if (in_array($attribute->getFrontend()->getInputType(), ['select', 'multiselect'])) {
            try {
                $data = $this->eavOptionHelper->extractAttributeOptionValue($attribute, $data, $this->getStore());
            } catch (\Exception $e) {
                $this->context->helperContext()->logger()->critical($e);
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
            case AttributesMapping::DATA_TYPE_INTEGER:
                return (int) $value;
                break;
            case AttributesMapping::DATA_TYPE_DECIMAL:
                return (float) $value;
                break;
            case AttributesMapping::DATA_TYPE_BOOLEAN:
                return (bool) $value;
                break;
            case AttributesMapping::DATA_TYPE_STRING:
            default:
                return (string) $value;
        }
    }
    
    
    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore()
    {
        return $this->context
            ->helperContext()
            ->storeManager()
            ->getStore();
    }
}
