<?php

namespace BitTools\SkyHub\Observer;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Helper\Context;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    use Functions;
    
    
    /** @var Context */
    protected $context;
    
    
    public function __construct(Context $context)
    {
        $this->context = $context;
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
