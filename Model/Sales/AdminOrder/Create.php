<?php

namespace BitTools\SkyHub\Model\Sales\AdminOrder;

use BitTools\SkyHub\Functions;
use Magento\Catalog\Model\Product;

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

        $qty = (float) $this->arrayExtract($productData, 'qty');

        $this->registerCurrentData($product, $productData);

        switch ($product->getTypeId()) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                $this->addProductConfigurable($product, $productData);
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $this->addProductGrouped($product, $productData);
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
            default:
                $config = ['qty' => $qty];
                $this->addProduct($product, $config);
        }

        return true;
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
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addProductConfigurable(Product $product, array $productData = [])
    {
        $qty = (float) $this->arrayExtract($productData, 'qty');

        /**
         * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable                                    $typeInstance
         * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $attributes
         */
        $typeInstance    = $product->getTypeInstance();
        $attributes      = $typeInstance->getConfigurableAttributes($product);
        $superAttributes = [];
        $children        = (array) $this->arrayExtract($productData, 'children');

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
        foreach ($attributes as $attribute) {
            /** @var array $child */
            foreach ($children as $child) {
                $childId     = (int) $this->arrayExtract($child, 'product_id');
                $attributeId = $attribute->getAttributeId();
                $value       = $this->getProductResource()
                    ->getAttributeRawValue($childId, $attributeId, $product->getStore());

                if (!$value) {
                    continue;
                }

                $superAttributes[$attributeId] = $value;
            }
        }

        $config = [
            'qty'             => $qty,
            'config'          => $productData,
            'super_attribute' => $superAttributes,
        ];

        $this->addProduct($product, $config);

        return true;
    }


    /**
     * @param Product $product
     * @param array   $productData
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addProductGrouped(Product $product, array $productData = [])
    {
        $children = (array) $this->arrayExtract($productData, 'children');
        $qty      = (float) $this->arrayExtract($productData, 'qty');

        $childrenIds = [];

        /** @var array $child */
        foreach ($children as $child) {
            $childId = $this->arrayExtract($child, 'product_id');

            if (!$childId || !$this->validateProductId($childId)) {
                continue;
            }

            $childrenIds[$childId] = $qty;
        }

        $params = [
            'config'      => $productData,
            'super_group' => $childrenIds,
        ];

        $this->addProduct($product, $params);

        return $this;
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
