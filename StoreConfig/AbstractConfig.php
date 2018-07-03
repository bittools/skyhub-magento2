<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;

abstract class AbstractConfig
{
    
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    
    /** @var EncryptorInterface */
    protected $encryptor;

    /** @var StoreManager */
    protected $storeManager;
    
    /** @var string */
    protected $group = 'general';
    
    
    /**
     * Service constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface   $encryptor
     * @param StoreManager         $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        StoreManager $storeManager
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->encryptor    = $encryptor;
        $this->storeManager = $storeManager;
    }


    /**
     * @param string   $field
     * @param string   $group
     * @param string   $section
     * @param int|null $scopeCode
     * @param string   $scopeType
     *
     * @return mixed
     */
    public function getStoreConfig($field, $group, $section, $scopeCode = null, $scopeType = null)
    {
        if (!$scopeType) {
            $scopeType = ScopeInterface::SCOPE_STORES;
        }

        $path = implode('/', [$section, $group, $field]);

        try {
            $scopeCode = $this->storeManager->getStore($scopeCode)->getId();
            $value     = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);

            return $value;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * @param string      $field
     * @param string      $group
     * @param null|int    $scopeCode
     * @param string      $scopeType
     *
     * @return mixed
     */
    public function getSkyHubModuleConfig($field, $group = null, $scopeCode = null, $scopeType = null)
    {
        if (empty($group)) {
            $group = $this->group;
        }

        return $this->getStoreConfig($field, $group, 'bittools_skyhub', $scopeCode, $scopeType);
    }

    
    /**
     * @param string      $field
     * @param string      $group
     * @param int|null    $scopeCode
     * @param string      $scopeType
     *
     * @return array
     */
    public function getSkyHubModuleConfigAsArray($field, $group = null, $scopeCode = null, $scopeType = null)
    {
        $values      = $this->getSkyHubModuleConfig($field, $group, $scopeCode, $scopeType);
        $arrayValues = explode(',', $values);
        
        return $arrayValues;
    }
}
