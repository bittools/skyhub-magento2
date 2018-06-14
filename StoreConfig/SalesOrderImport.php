<?php

namespace BitTools\SkyHub\StoreConfig;

class SalesOrderImport extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'cron_sales_order_import';
    
    
    /**
     * @param mixed $scopeCode
     *
     * @return bool
     */
    public function isEnabled($scopeCode = null)
    {
        return (bool) $this->getSkyHubModuleConfig('enabled', $this->group, $scopeCode);
    }
    
    
    /**
     * @param mixed $scopeCode
     *
     * @return int
     */
    public function getLimit($scopeCode = null)
    {
        return (int) $this->getSkyHubModuleConfig('limit', $this->group, $scopeCode);
    }
    
    
    /**
     * @param mixed $scopeCode
     *
     * @return bool
     */
    public function useDefaultIncrementId($scopeCode = null)
    {
        return (bool) $this->getSkyHubModuleConfig('use_default_increment_id', $this->group, $scopeCode);
    }
}
