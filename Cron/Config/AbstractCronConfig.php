<?php

namespace BitTools\SkyHub\Cron\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractCronConfig extends \BitTools\SkyHub\StoreConfig\AbstractConfig implements ConfigInterface
{
    
    /** @var string */
    protected $group = '';
    
    /** @var string */
    protected $enabledField = 'enabled';
    
    
    /**
     * @param int|null $scopeCode
     *
     * @return bool
     */
    public function isEnabled($scopeCode = null)
    {
        return (bool) $this->getSkyHubModuleConfig($this->enabledField, $this->group, $scopeCode);
    }
    
    
    /**
     * @param string   $field
     * @param int|null $scopeCode
     *
     * @return mixed
     */
    public function getGroupConfig($field, $scopeCode = null)
    {
        return $this->getSkyHubModuleConfig($field, $this->group, $scopeCode);
    }
}
