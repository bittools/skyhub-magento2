<?php

namespace BitTools\SkyHub\Integration;

use BitTools\SkyHub\Helper\Context as HelperContext;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    
    /** @var Service */
    protected $service;
    
    /** @var HelperContext */
    protected $helperContext;
    
    
    public function __construct(
        HelperContext $helperContext,
        Service $service
    )
    {
        $this->service       = $service;
        $this->helperContext = $helperContext;
    }
    
    
    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function eventManager()
    {
        return $this->helperContext->eventManager();
    }
    
    
    /**
     * @return Service
     */
    public function service()
    {
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
