<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Store\Model\ScopeInterface;

class GeneralConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * @var int|null $scopeCode
     *
     * @return boolean
     */
    public function isModuleEnabled($scopeCode = null)
    {
        return (bool) $this->getSkyHubModuleConfig('enabled', $this->group, $scopeCode);
    }
}
