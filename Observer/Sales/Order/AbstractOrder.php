<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use BitTools\SkyHub\Observer\AbstractObserver;

abstract class AbstractOrder extends AbstractObserver
{
    
    /** @var \BitTools\SkyHub\Model\StoreIteratorInterface */
    protected $storeIterator;
    
    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order */
    protected $orderIntegrator;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Product */
    protected $productIntegrator;
    
    /** @var \BitTools\SkyHub\Api\QueueRepositoryInterface */
    protected $queueRepository;
    
    /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation */
    protected $productValidation;
    
    /** @var \BitTools\SkyHub\Model\ResourceModel\QueueFactory */
    protected $queueResourceFactory;
    
    
    public function __construct(
        \BitTools\SkyHub\Helper\Context $context,
        \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \BitTools\SkyHub\Integration\Integrator\Sales\Order $orderIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Product $productIntegrator,
        \BitTools\SkyHub\Api\QueueRepositoryInterface $queueRepository,
        \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation $productValidation,
        \BitTools\SkyHub\Model\ResourceModel\QueueFactory $queueResourceFactory
    )
    {
        parent::__construct($context);
        
        $this->storeIterator        = $storeIterator;
        $this->orderRepository      = $orderRepository;
        $this->orderIntegrator      = $orderIntegrator;
        $this->productIntegrator    = $productIntegrator;
        $this->queueRepository      = $queueRepository;
        $this->productValidation    = $productValidation;
        $this->queueResourceFactory = $queueResourceFactory;
    }
    
    
    /**
     * @param string $configStatus
     * @param string $currentStatus
     *
     * @return bool
     */
    protected function statusMatches($configStatus, $currentStatus)
    {
        if ($currentStatus == $configStatus) {
            return true;
        }
        
        return false;
    }
}
