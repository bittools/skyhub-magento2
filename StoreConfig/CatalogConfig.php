<?php

namespace BitTools\SkyHub\StoreConfig;

class CatalogConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'catalog';
    
    
    /**
     * @return boolean
     */
    public function hasActiveIntegrateOnSaveFlag()
    {
        return (bool) $this->getSkyHubModuleConfig('immediately_integrate_product_after_sensitive_change');
    }
    
    
    /**
     * @return array
     */
    public function getProductVisibilities()
    {
        return $this->getSkyHubModuleConfigAsArray('product_visibility');
    }
}
