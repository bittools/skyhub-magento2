<?php

namespace BitTools\SkyHub\StoreConfig;

class ServiceConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'service';
    
    
    /**
     * @return string
     */
    public function getServiceBaseUri()
    {
        return (string) $this->getSkyHubModuleConfig('base_uri');
    }
    
    
    /**
     * @return string
     */
    public function getServiceEmail()
    {
        return (string) $this->getSkyHubModuleConfig('email');
    }
    
    
    /**
     * @return string
     */
    public function getServiceApiKey()
    {
        return (string) $this->getSkyHubModuleConfig('api_key');
    }
    
    
    /**
     * @return bool
     */
    public function isConfigurationOk()
    {
        if (!$this->getServiceBaseUri()) {
            return false;
        }
        
        if (!$this->getServiceEmail()) {
            return false;
        }
        
        if (!$this->getServiceApiKey()) {
            return false;
        }
        
        return true;
    }
}
