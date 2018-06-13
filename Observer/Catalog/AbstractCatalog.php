<?php

namespace BitTools\SkyHub\Observer\Catalog;

use BitTools\SkyHub\Observer\AbstractObserver;

abstract class AbstractCatalog extends AbstractObserver
{
    
    /**
     * @param int $productId
     *
     * @return bool|\Magento\Catalog\Model\Product
     */
    protected function getProduct($productId)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return false;
        }
    
        return $product;
    }
}
