<?php

namespace BitTools\SkyHub\Cron\Config;

abstract class AbstractCronConfig extends \BitTools\SkyHub\StoreConfig\AbstractConfig implements ConfigInterface
{
    
    /** @var string */
    protected $group = '';
    
    /** @var string */
    protected $enabledField = 'enabled';
    
    
    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool) $this->getSkyHubModuleConfig($this->enabledField, $this->group, $storeId);
    }
    
    
    /**
     * @param string   $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getGroupConfig($field, $storeId = null)
    {
        return $this->getSkyHubModuleConfig($field, $this->group, $storeId);
    }
}
