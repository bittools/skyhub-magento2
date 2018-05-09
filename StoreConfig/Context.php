<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Context extends AbstractConfig
{
    
    /** @var GeneralConfig */
    protected $general;
    
    /** @var ServiceConfig */
    protected $service;
    
    /** @var LogConfig */
    protected $log;
    
    /** @var CatalogConfig */
    protected $catalog;
    
    
    /**
     * Service constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GeneralConfig $generalConfig,
        ServiceConfig $serviceConfig,
        LogConfig $logConfig,
        CatalogConfig $catalogConfig
    )
    {
        parent::__construct($scopeConfig);
        
        $this->general = $generalConfig;
        $this->service = $serviceConfig;
        $this->log     = $logConfig;
        $this->catalog = $catalogConfig;
    }
    
    
    /**
     * @return GeneralConfig
     */
    public function general()
    {
        return $this->general;
    }
    
    
    /**
     * @return ServiceConfig
     */
    public function service()
    {
        return $this->service;
    }
    
    
    /**
     * @return LogConfig
     */
    public function log()
    {
        return $this->log;
    }
    
    
    /**
     * @return CatalogConfig
     */
    public function catalog()
    {
        return $this->catalog;
    }
}
