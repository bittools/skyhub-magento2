<?php

namespace BitTools\SkyHub\Observer\Catalog\Product;

use BitTools\SkyHub\Observer\Catalog\AbstractCatalog;
use Magento\Framework\Event\Observer;

class DisableProduct extends AbstractCatalog
{
    
    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->canRun()) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getData('product');
        
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return;
        }
        
        $responseHandler = $this->productIntegrator->product($product->getSku());
        
        if ($responseHandler === false || ($responseHandler && $responseHandler->exception())) {
            return;
        }
        
        //disable the item and set 0 to stock items
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        
        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $product->getStockItem();
        
        if ($stockItem) {
            $stockItem->setQty(0)
                ->save();
        }
        
        /** Create or Update Product */
        $this->productIntegrator->update($product);
    }
}
