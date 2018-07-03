<?php

namespace BitTools\SkyHub\Helper\Catalog;

use BitTools\SkyHub\Helper\AbstractHelper;
use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\StoreConfig\Context as StoreConfigContext;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

class Product extends AbstractHelper
{
    
    /** @var ProductResourceFactory */
    protected $productResourceFactory;
    
    /** @var StoreConfigContext */
    protected $storeConfigContext;
    
    
    public function __construct(
        Context $context,
        StoreConfigContext $storeConfigContext,
        ProductResourceFactory $productResourceFactory
    ) {
        parent::__construct($context);
        
        $this->productResourceFactory = $productResourceFactory;
        $this->storeConfigContext     = $storeConfigContext;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param string|EntityAttribute $attribute
     *
     * @return array|bool|mixed|string
     */
    public function productAttributeRawValue(CatalogProduct $product, $attribute)
    {
        if ($attribute instanceof EntityAttribute) {
            $attribute = $attribute->getAttributeCode();
        }
        
        $data = $product->getData($attribute);
        
        if (empty($data)) {
            try {
                /**
                 * @var \Magento\Catalog\Model\ResourceModel\Product $resource
                 * @var \Magento\Store\Model\Store                   $store
                 */
                $resource = $this->productResourceFactory->create();
                $store    = $this->context->storeManager()->getStore();
                $data     = $resource->getAttributeRawValue($product->getId(), $attribute, $store);
                
                if (empty($data) && is_array($data)) {
                    $data = null;
                }
                
                return $data;
            } catch (\Exception $e) {
                $this->context->logger()->critical($e);
            }
        }
        
        return $data;
    }
    
    
    /**
     * @param CatalogProduct         $product
     * @param EntityAttribute|string $attribute
     *
     * @return float|null
     */
    public function extractProductPrice(CatalogProduct $product, $attribute = null)
    {
        if ($attribute instanceof EntityAttribute) {
            $attribute = $attribute->getAttributeCode();
        }
        
        if (empty($attribute)) {
            $attribute = 'price';
        }
        
        $price = $this->productAttributeRawValue($product, $attribute);
        
        if (!empty($price)) {
            return $price;
        }
        
        return null;
    }
    
    
    /**
     * @param CatalogProduct $product
     * @param null|string                $attributeCode
     * @param null|float                 $comparedPrice
     *
     * @return float|null
     */
    public function extractProductSpecialPrice(CatalogProduct $product, $attributeCode = null, $comparedPrice = null)
    {
        if (empty($attributeCode)) {
            $attributeCode = 'special_price';
        }
        
        $specialPrice = $this->productAttributeRawValue($product, $attributeCode);
        
        $fromDate = $this->extractProductSpecialFromDate($product);
        $toDate   = $this->extractProductSpecialToDate($product);
        
        if ($this->validateSpecialPrice($specialPrice, $comparedPrice, $fromDate, $toDate)) {
            return $specialPrice;
        }
        
        return null;
    }
    
    
    /**
     * @param CatalogProduct $product
     *
     * @return string
     */
    public function extractProductSpecialFromDate(CatalogProduct $product, $attributeCode = null)
    {
        if (empty($attributeCode)) {
            $attributeCode = 'special_from_date';
        }
        
        $value = $this->productAttributeRawValue($product, $attributeCode);
        
        return (string) $value;
    }
    
    
    /**
     * @param CatalogProduct $product
     *
     * @return string
     */
    public function extractProductSpecialToDate(CatalogProduct $product, $attributeCode = null)
    {
        if (empty($attributeCode)) {
            $attributeCode = 'special_to_date';
        }
        
        return (string) $this->productAttributeRawValue($product, $attributeCode);
    }
    
    
    /**
     * @param float       $specialPrice
     * @param float       $price
     * @param string|null $fromDate
     * @param string|null $toDate
     *
     * @return bool
     */
    public function validateSpecialPrice($specialPrice, $price = null, $fromDate = null, $toDate = null)
    {
        $specialPrice = (float) $specialPrice;
        
        if (empty($specialPrice)) {
            return false;
        }
        
        if (!is_null($price) && (((float) $price) <= $specialPrice)) {
            return false;
        }
        
        if (!empty($fromDate) && (time() < strtotime($fromDate))) {
            return false;
        }
        
        if (!empty($toDate) && (time() > strtotime($toDate))) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool
     */
    public function validateProductAttribute(EntityAttribute $attribute)
    {
        if (!$attribute) {
            return false;
        }
        
        if (!$attribute->getAttributeId()) {
            return false;
        }
        
        if (!$attribute->getAttributeCode()) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param CatalogProduct $product
     *
     * @return bool
     */
    public function hasAllowedVisibility(CatalogProduct $product)
    {
        return $this->storeConfigContext
            ->catalog()
            ->hasAllowedVisibility($product->getVisibility());
    }
}
