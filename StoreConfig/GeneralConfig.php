<?php

namespace BitTools\SkyHub\StoreConfig;

class GeneralConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * @return boolean
     */
    public function isModuleEnabled($storeId = null)
    {
        return (bool) $this->getSkyHubModuleConfig('enabled', null, $storeId);
    }
}
