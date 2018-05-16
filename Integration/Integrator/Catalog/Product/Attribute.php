<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog\Product;

use BitTools\SkyHub\Integration\Context;
use BitTools\SkyHub\Integration\Integrator\Catalog\AbstractCatalog;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use BitTools\SkyHub\Integration\Transformer\Catalog\Product\AttributeFactory as ProductAttributeTransformerFactory;
use SkyHub\Api\Handler\Response\HandlerDefault;
use SkyHub\Api\Handler\Response\HandlerException;

class Attribute extends AbstractCatalog
{
    
    /** @var string */
    protected $eventType = 'catalog_product_attribute';
    
    /** @var ProductAttributeTransformerFactory */
    protected $transformerFactory;
    
    
    public function __construct(Context $context, ProductAttributeTransformerFactory $transformerFactory)
    {
        parent::__construct($context);
        
        $this->transformerFactory = $transformerFactory;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool|HandlerDefault|HandlerException
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrUpdate(EntityAttribute $attribute)
    {
        $exists = $this->productAttributeExists($attribute->getId());
        
        $eventParams = [
            'attribute' => $attribute
        ];
        
        $this->context
            ->helperContext()
            ->eventManager()
            ->dispatch('bseller_skyhub_catalog_product_attribute_integrate_before', $eventParams);
        
        if (true == $exists) {
            /** Update Product Attribute */
            $response = $this->update($attribute);
            $eventParams['method'] = 'update';
        } else {
            /** @var bool|HandlerDefault|HandlerException $response */
            $response = $this->create($attribute);
            $eventParams['method'] = 'create';
            
            if ($response && $response->success()) {
                $this->registerProductAttributeEntity($attribute->getId());
            }
        }
        
        $eventParams['response'] = $response;
    
        $this->context
            ->helperContext()
            ->eventManager()
            ->dispatch('bseller_skyhub_catalog_product_attribute_integrate_after', $eventParams);
        
        return $response;
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool|HandlerDefault|HandlerException
     */
    public function create(EntityAttribute $attribute)
    {
        if (!$this->canIntegrateAttribute($attribute)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product\Attribute $interface */
        $interface = $this->productAttributeTransformer()->convert($attribute);
        return $interface->create();
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool|HandlerDefault|HandlerException
     */
    public function update(EntityAttribute $attribute)
    {
        if (!$this->canIntegrateAttribute($attribute)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Product\Attribute $interface */
        $interface = $this->productAttributeTransformer()->convert($attribute);
        return $interface->update();
    }
    
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return bool
     */
    protected function canIntegrateAttribute(EntityAttribute $attribute)
    {
        return (bool) ($attribute->getId() && $attribute->getAttributeCode() && $attribute->getDefaultFrontendLabel());
    }
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Transformer\Catalog\Product\Attribute
     */
    protected function productAttributeTransformer()
    {
        /** @var \BitTools\SkyHub\Integration\Transformer\Catalog\Product\Attribute $transformer */
        $transformer = $this->transformerFactory->create();
        return $transformer;
    }
}
