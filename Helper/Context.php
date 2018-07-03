<?php

namespace BitTools\SkyHub\Helper;

use BitTools\SkyHub\Model\Config\SkyhubAttributes\Data as SkyHubConfig;
use BitTools\SkyHub\StoreConfig\Context as ConfigContext;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    
    /** @var SkyHubConfig */
    protected $skyhubConfig;

    /** @var ConfigContext */
    protected $configContext;
    
    /** @var StoreManagerInterface */
    protected $storeManager;
    
    /** @var ManagerInterface */
    protected $eventManager;
    
    /** @var ObjectManagerInterface */
    protected $objectManager;
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var State */
    protected $state;

    /** @var Registry */
    protected $registryManager;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    
    
    public function __construct(
        SkyHubConfig $skyhubConfig,
        ConfigContext $configContext,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        State $state,
        Registry $registry,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->skyhubConfig    = $skyhubConfig;
        $this->configContext   = $configContext;
        $this->storeManager    = $storeManager;
        $this->eventManager    = $eventManager;
        $this->objectManager   = $objectManager;
        $this->logger          = $logger;
        $this->state           = $state;
        $this->registryManager = $registry;
        $this->scopeConfig     = $scopeConfig;
    }
    
    
    /**
     * @return ManagerInterface
     */
    public function eventManager()
    {
        return $this->eventManager;
    }
    
    
    /**
     * @return ObjectManagerInterface
     */
    public function objectManager()
    {
        return $this->objectManager;
    }
    
    
    /**
     * @return StoreManagerInterface
     */
    public function storeManager()
    {
        return $this->storeManager;
    }
    
    
    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }
    
    
    /**
     * @return SkyHubConfig
     */
    public function skyhubConfig()
    {
        return $this->skyhubConfig;
    }
    
    
    /**
     * @return State
     */
    public function appState()
    {
        return $this->state;
    }


    /**
     * @return ConfigContext
     */
    public function configContext()
    {
        return $this->configContext;
    }


    /**
     * @return Registry
     */
    public function registryManager()
    {
        return $this->registryManager;
    }


    /**
     * @return ScopeConfigInterface
     */
    public function scopeConfig()
    {
        return $this->scopeConfig;
    }
}
