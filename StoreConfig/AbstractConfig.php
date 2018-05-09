<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class AbstractConfig
{
    
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * Service constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    
    /**
     * @param string   $field
     * @param string   $group
     * @param string   $section
     * @param int|null $storeId
     *
     * @return mixed
     */
    protected function getStoreConfig(
        $field,
        $group,
        $section,
        $scopeCode = null,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    )
    {
        $path  = implode('/', [$section, $group, $field]);
        $value = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        
        return $value;
    }
    
    
    /**
     * @param string      $field
     * @param string      $group
     * @param string|null $scopeCode
     * @param string      $scopeType
     *
     * @return mixed
     */
    protected function getSkyHubModuleConfig(
        $field,
        $group = null,
        $scopeCode = null,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    )
    {
        if (empty($group)) {
            $group = $this->group;
        }
        
        return $this->getStoreConfig($field, $group, 'bittools_skyhub', $scopeCode, $scopeType);
    }
}
