<?php

namespace BitTools\SkyHub\StoreConfig;

class ServiceConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'service';
    
    
    /**
     * @return string
     */
    public function getServiceBaseUri($storeId = null)
    {
        return (string) $this->getSkyHubModuleConfig('base_uri', null, $storeId);
    }
    
    
    /**
     * @return string
     */
    public function getServiceEmail($storeId = null)
    {
        return (string) $this->getSkyHubModuleConfig('email', null, $storeId);
    }
    
    
    /**
     * @return string
     */
    public function getServiceApiKey($storeId = null)
    {
        $key = (string) $this->getSkyHubModuleConfig('api_key', null, $storeId);
        $key = $this->encryptor->decrypt($key);
        
        return $key;
    }
    
    
    /**
     * @var int|null $storeId
     *
     * @return bool
     */
    public function isConfigurationOk($storeId = null)
    {
        if (!$this->getServiceBaseUri($storeId)) {
            return false;
        }
        
        if (!$this->getServiceEmail($storeId)) {
            return false;
        }
        
        if (!$this->getServiceApiKey($storeId)) {
            return false;
        }
        
        return true;
    }
}
