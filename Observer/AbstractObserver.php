<?php

namespace BitTools\SkyHub\Observer;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Helper\Context;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    use Functions;
    
    
    /** @var Context */
    protected $context;
    
    /** @var \BitTools\SkyHub\Model\StoreIteratorInterface */
    protected $storeIterator;
    
    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;
    
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order */
    protected $orderIntegrator;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Product */
    protected $productIntegrator;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Product\Attribute */
    protected $productAttributeIntegrator;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Category */
    protected $categoryIntegrator;
    
    /** @var \BitTools\SkyHub\Api\QueueRepositoryInterface */
    protected $queueRepository;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation */
    protected $productValidation;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\CategoryValidation */
    protected $categoryValidation;
    
    /** @var \BitTools\SkyHub\Model\ResourceModel\QueueFactory */
    protected $queueResourceFactory;
    
    
    public function __construct(
        \BitTools\SkyHub\Helper\Context $context,
        \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \BitTools\SkyHub\Integration\Integrator\Sales\Order $orderIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Product $productIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Product\Attribute $productAttributeIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Category $categoryIntegrator,
        \BitTools\SkyHub\Api\QueueRepositoryInterface $queueRepository,
        \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation $productValidation,
        \BitTools\SkyHub\Integration\Integrator\Catalog\CategoryValidation $categoryValidation,
        \BitTools\SkyHub\Model\ResourceModel\QueueFactory $queueResourceFactory
    ) {
        $this->context                    = $context;
        $this->storeIterator              = $storeIterator;
        $this->orderRepository            = $orderRepository;
        $this->productRepository          = $productRepository;
        $this->orderIntegrator            = $orderIntegrator;
        $this->productIntegrator          = $productIntegrator;
        $this->productAttributeIntegrator = $productAttributeIntegrator;
        $this->categoryIntegrator         = $categoryIntegrator;
        $this->queueRepository            = $queueRepository;
        $this->productValidation          = $productValidation;
        $this->categoryValidation         = $categoryValidation;
        $this->queueResourceFactory       = $queueResourceFactory;
    }
    
    
    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function canRun($storeId = null)
    {
        if (!$this->context->configContext()->general()->isModuleEnabled($storeId)) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param null|int $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        return $this->context->storeManager()->getStore($storeId);
    }
}
