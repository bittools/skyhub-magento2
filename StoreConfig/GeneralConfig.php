<?php

namespace BitTools\SkyHub\StoreConfig;

class GeneralConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return (bool) $this->getSkyHubModuleConfig('enabled');
    }
    
    
    /**
     * @return boolean
     */
    public function hasActiveIntegrateOnSaveFlag()
    {
        return (bool) $this->getSkyHubModuleConfig('immediately_integrate_product_on_save_price_stock_change');
    }
}
