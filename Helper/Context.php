<?php

namespace BitTools\SkyHub\Helper;

use BitTools\SkyHub\Model\Config\SkyhubAttributes\Data as SkyHubConfig;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    
    /** @var SkyHubConfig */
    protected $skyhubConfig;
    
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
    
    
    public function __construct(
        SkyHubConfig $skyhubConfig,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        State $state
    )
    {
        $this->skyhubConfig  = $skyhubConfig;
        $this->storeManager  = $storeManager;
        $this->eventManager  = $eventManager;
        $this->objectManager = $objectManager;
        $this->logger        = $logger;
        $this->state         = $state;
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
}
