<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Store\Model\ScopeInterface;

class ServiceConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'service';
    
    
    /**
     * @var int|null $storeId
     *
     * @return string
     */
    public function getServiceBaseUri($scopeCode = null)
    {
        return (string) $this->getSkyHubModuleConfig('base_uri', null, $scopeCode, ScopeInterface::SCOPE_STORES);
    }
    
    
    /**
     * @var int|null $storeId
     *
     * @return string
     */
    public function getServiceEmail($scopeCode = null)
    {
        return (string) $this->getSkyHubModuleConfig('email', null, $scopeCode, ScopeInterface::SCOPE_STORES);
    }
    
    
    /**
     * @var int|null $storeId
     *
     * @return string
     */
    public function getServiceApiKey($scopeCode = null)
    {
        $key = (string) $this->getSkyHubModuleConfig('api_key', null, $scopeCode, ScopeInterface::SCOPE_STORES);
        $key = $this->encryptor->decrypt($key);
        
        return $key;
    }
    
    
    /**
     * @var int|null $storeId
     *
     * @return bool
     */
    public function isConfigurationOk($scopeCode = null)
    {
        if (!$this->getServiceBaseUri($scopeCode)) {
            return false;
        }
        
        if (!$this->getServiceEmail($scopeCode)) {
            return false;
        }
        
        if (!$this->getServiceApiKey($scopeCode)) {
            return false;
        }
        
        return true;
    }
}
