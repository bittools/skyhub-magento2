<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Integration\Context;
use Magento\Catalog\Model\Product as ProductModel;

class Product extends AbstractCatalog
{
    
    /** @var string */
    protected $eventType = 'catalog_product';
    
    /** @var ProductValidation */
    protected $productValidation;
    
    
    public function __construct(Context $context, ProductValidation $productValidation)
    {
        parent::__construct($context);
        $this->productValidation = $productValidation;
    }
    
    
    /**
     * @param ProductModel $product
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function createOrUpdate(ProductModel $product)
    {
        $exists = $this->productExists($product->getId());
        
        if (true == $exists) {
            /**
             * Update Product
             *
             * @var bool|\SkyHub\Api\Handler\Response\HandlerInterface $response
             */
            $response = $this->update($product);
            
            if ($response && $response->success()) {
                $this->updateProductEntity($product->getId());
                return $response;
            }
        }
        
        /** Create Product */
        $response = $this->create($product);
        
        if ($response && $response->success()) {
            $this->registerProductEntity($product->getId());
        }
        
        return $response;
    }
    
    
    /**
     * @param ProductModel $product
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function create(ProductModel $product)
    {
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->productTransformer()
            ->convert($product);
        
        $this->eventMethod = 'create';
        $this->eventParams = [
            'product'   => $product,
            'interface' => $interface,
        ];
        
        $this->beforeIntegration();
        $response = $interface->create();
        $this->eventParams[] = $response;
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function update(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->productValidation->canIntegrateProduct($product)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->productTransformer()
            ->convert($product);
        
        $this->eventMethod = 'update';
        $this->eventParams = [
            'product'   => $product,
            'interface' => $interface,
        ];
        
        $this->beforeIntegration();
        $response = $interface->update();
        $this->eventParams[] = $response;
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param string $sku
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function product($sku)
    {
        if (!$this->validateSku($sku)) {
            return false;
        }
        
        $this->eventMethod = 'product';
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->api()
            ->product()
            ->entityInterface();
        $interface->setSku($sku);
        
        $this->beforeIntegration();
        $response = $interface->product();
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param null|bool $statusFilter
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function products($statusFilter = null)
    {
        if (!is_null($statusFilter) || !is_bool($statusFilter)) {
            return false;
        }
        
        $this->eventMethod = 'products';
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->api()
            ->product()
            ->entityInterface();
        
        $this->beforeIntegration();
        $interface->setStatus($statusFilter);
        $this->afterIntegration();
        
        return $interface->products();
    }
    
    
    /**
     * @return \SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function urls()
    {
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->api()
            ->product()
            ->entityInterface();
        
        $this->eventMethod = 'urls';
        
        $this->beforeIntegration();
        $response = $interface->urls();
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param $sku
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function delete($sku)
    {
        if (!$this->validateSku($sku)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->api()
            ->product()
            ->entityInterface();
        $interface->setSku($sku);
        
        $this->eventMethod = 'delete';
        
        $this->beforeIntegration();
        $response = $interface->delete();
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param string $sku
     *
     * @return bool
     */
    public function validateSku($sku)
    {
        if (empty($sku)) {
            return false;
        }
        
        return true;
    }
}
