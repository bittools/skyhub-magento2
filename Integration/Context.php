<?php

/**
 * BitTools Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  BitTools
 * @package   BitTools_SkyHub
 *
 * @copyright Copyright (c) 2021 B2W Digital - BitTools Platform.
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
 */

namespace BitTools\SkyHub\Integration;

use BitTools\SkyHub\Helper\Context as HelperContext;
use BitTools\SkyHub\Integration\ServiceMultipartFactory;
use Magento\Framework\ObjectManager\ContextInterface;

/**
 * Context class
 */
class Context implements ContextInterface
{
    
    /** @var Service */
    protected $service;
    
    /** @var ServiceFactory */
    protected $serviceFactory;
    
    /** @var HelperContext */
    protected $helperContext;

    /** @var ServiceMultipartFactory */
    protected $serviceMultipartFactory;

    /** @var ServiceMultipart */
    protected $serviceMultipart;
    
    
    public function __construct(
        HelperContext $helperContext,
        ServiceFactory $serviceFactory,
        ServiceMultipartFactory $serviceMultipartFactory
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->serviceMultipartFactory = $serviceMultipartFactory;
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
     * @var boolean $new
     *
     * @return Service
     */
    public function serviceMultiPart($new = false)
    {
        if (!$this->serviceMultipart || true === $new) {
            $this->serviceMultipart = $this->serviceMultipartFactory->create();
        }
        
        return $this->serviceMultipart;
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
