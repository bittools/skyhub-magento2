<?php

namespace BitTools\SkyHub\Integration;

use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Framework\ObjectManager\ContextInterface;

class Context implements ContextInterface
{
    
    /** @var Service */
    protected $service;
    
    /** @var ServiceFactory */
    protected $serviceFactory;
    
    /** @var HelperContext */
    protected $helperContext;
    
    
    public function __construct(
        HelperContext $helperContext,
        ServiceFactory $serviceFactory
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->helperContext  = $helperContext;
    }
    
    
    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function eventManager()
    {
        return $this->helperContext->eventManager();
    }
    
    
    /**
     * @var boolean $new
     *
     * @return Service
     */
    public function service($new = false)
    {
        if (!$this->service || true === $new) {
            $this->service = $this->serviceFactory->create();
        }
        
        return $this->service;
    }
    
    
    /**
     * @return \SkyHub\Api
     */
    public function api()
    {
        return $this->service()->api();
    }
    
    
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function objectManager()
    {
        return $this->helperContext->objectManager();
    }
    
    
    /**
     * @return HelperContext
     */
    public function helperContext()
    {
        return $this->helperContext;
    }
}
