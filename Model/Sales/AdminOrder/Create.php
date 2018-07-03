<?php

namespace BitTools\SkyHub\Model\Sales\AdminOrder;

use BitTools\SkyHub\Functions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductTypeSimple;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{

    use Functions;


    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProductByData(array $data = [])
    {
        $productData = (array) $this->arrayExtract($data, 'product');
        $productId   = (int)   $this->arrayExtract($productData, 'product_id');

        if (!$productId) {
            return false;
        }

        /** @var Product $product */
        $product = $this->getProduct($productId);
        
        if (!$product->getId()) {
            return false;
        }
        
        $this->registerCurrentData($product, $productData);
    
        $config = $this->prepareProductConfig($product, $productData);
        $this->addProduct($product, $config);

        return true;
    }
    
    
    /**
     * @param Product $product
     * @param array   $productData
     *
     * @return array
     */
    protected function prepareProductConfig(Product $product, array $productData)
    {
        $config = [];
        
        switch ($product->getTypeId()) {
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $config['config']      = $productData;
                $config['super_group'] = $this->getGroupedSuperGroup($productData);
                
                break;
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                $config['config']          = $productData;
                $config['super_attribute'] = $this->getConfigurableSuperAttributes($product, $productData);
                
            case ProductTypeSimple::TYPE_SIMPLE:
                /** It's applied to configurable product too. */
            default:
                $qty           = (float) $this->arrayExtract($productData, 'qty');
                $config['qty'] = $qty;
        }
    
        $finalPrice = (float) $this->arrayExtract($productData, 'final_price');
    
        if ($finalPrice) {
            $config['custom_price'] = $finalPrice;
        }
        
        return $config;
    }


    /**
     * @param Product $product
     * @param array   $productData
     *
     * @return $this
     */
    protected function registerCurrentData(Product $product, array $productData)
    {
        $key = 'skyhub_product_configuration';
        $product->setData($key, (array) $productData);

        return $this;
    }
    
    
    /**
     * @param Product $product
     * @param array   $productData
     *
     * @return array
     */
    protected function getConfigurableSuperAttributes(Product $product, array $productData = [])
    {
        $superAttributes = [];
        
        /**
         * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable                                    $typeInstance
         * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $attributes
         */
        $typeInstance = $product->getTypeInstance();
        $attributes   = $typeInstance->getConfigurableAttributes($product);
        $children     = (array) $this->arrayExtract($productData, 'children');
    
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
        foreach ($attributes as $attribute) {
            /** @var array $child */
            foreach ($children as $child) {
                /** @var \Magento\Store\Model\Store $store */
                $store       = $product->getStore();
                $childId     = (int) $this->arrayExtract($child, 'product_id');
                $attributeId = $attribute->getAttributeId();
                
                /** Extract the value from product. */
                $value = $this->getProductResource()->getAttributeRawValue($childId, $attributeId, $store);
            
                if (!$value) {
                    continue;
                }
            
                $superAttributes[$attributeId] = $value;
            }
        }
        
        return $superAttributes;
    }
    
    
    /**
     * @param array $productData
     *
     * @return array
     */
    protected function getGroupedSuperGroup(array $productData = [])
    {
        $superGroup = [];
        
        $children = (array) $this->arrayExtract($productData, 'children');
        $qty      = (float) $this->arrayExtract($productData, 'qty');
        
        /** @var array $child */
        foreach ($children as $child) {
            $childId = $this->arrayExtract($child, 'product_id');
        
            if (!$childId || !$this->validateProductId($childId)) {
                continue;
            }
    
            $superGroup[$childId] = $qty;
        }
        
        return $superGroup;
    }


    /**
     * @param int $productId
     *
     * @return Product
     */
    protected function getProduct($productId)
    {
        try {
            /** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
            $repository = $this->_objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

            /** @var Product $product */
            $product = $repository->getById($productId);
        } catch (\Exception $e) {
            $product = $this->objectFactory->create(Product::class);
        }

        return $product;
    }


    /**
     * @param int $productId
     *
     * @return bool
     */
    protected function validateProductId($productId)
    {
        $result = $this->getProductResource()->getProductsSku((array) $productId);

        return !empty($result);
    }


    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */
    protected function getProductResource()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Product::class);
        return $resource;
    }
}
