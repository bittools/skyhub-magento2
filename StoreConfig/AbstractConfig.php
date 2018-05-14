<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

abstract class AbstractConfig
{
    
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    
    /** @var EncryptorInterface */
    protected $encryptor;
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * Service constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor   = $encryptor;
    }
    
    
    /**
     * @param string      $field
     * @param string      $group
     * @param string      $section
     * @param string|null $scopeCode
     * @param string      $scope
     *
     * @return mixed
     */
    protected function getStoreConfig(
        $field,
        $group,
        $section,
        $scopeCode = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    )
    {
        $path  = implode('/', [$section, $group, $field]);
        $value = $this->scopeConfig->getValue($path, $scope, $scopeCode);
        
        return $value;
    }
    
    
    /**
     * @param string      $field
     * @param string      $group
     * @param string|null $scopeCode
     * @param string      $scope
     *
     * @return mixed
     */
    protected function getSkyHubModuleConfig(
        $field,
        $group = null,
        $scopeCode = null,
        $scope = StoreScopeInterface::SCOPE_STORE
    )
    {
        if (empty($group)) {
            $group = $this->group;
        }
        
        return $this->getStoreConfig($field, $group, 'bittools_skyhub', $scopeCode, $scope);
    }
    
    
    /**
     * @param string      $field
     * @param string      $group
     * @param string|null $scopeCode
     * @param string      $scope
     *
     * @return array
     */
    protected function getSkyHubModuleConfigAsArray(
        $field,
        $group = null,
        $scopeCode = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    )
    {
        $values      = $this->getSkyHubModuleConfig($field, $group, $scopeCode, $scope);
        $arrayValues = explode(',', $values);
        
        return $arrayValues;
    }
}
