<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Integration\Context;
use Magento\Catalog\Model\Product as ProductModel;
use BitTools\SkyHub\Integration\Transformer\Catalog\ProductFactory as ProductTransformerFactory;

class Product extends AbstractCatalog
{
    
    /** @var string */
    protected $eventType = 'catalog_product';
    
    /** @var ProductValidation */
    protected $validator;
    
    /** @var ProductTransformerFactory */
    protected $transformerFactory;
    
    
    public function __construct(
        Context $context,
        ProductValidation $productValidation,
        ProductTransformerFactory $transformerFactory
    )
    {
        parent::__construct($context);
        
        $this->validator          = $productValidation;
        $this->transformerFactory = $transformerFactory;
    }
    
    
    /**
     * @param ProductModel $product
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function create(ProductModel $product)
    {
        if (!$this->validator->canIntegrateProduct($product)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product $interface */
        $interface = $this->productTransformer()->convert($product);
        
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
     * @param ProductModel $product
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     *
     * @throws \Exception
     */
    public function update(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->validator->canIntegrateProduct($product)) {
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
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Transformer\Catalog\Product
     */
    protected function productTransformer()
    {
        return $this->transformerFactory->create();
    }
}
