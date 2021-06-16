<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\CacheInterface;

class Data extends ConfigData
{
    /** @var ScopeConfigInterface */
    protected $config;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        ScopeConfigInterface $config,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
        $this->config = $config;
    }
    
    /**
     * @return array
     */
    public function getEntityAttributes($entityType)
    {
        return $this->get("attributes/{$entityType}");
    }
    
    /**
     * @return array
     */
    public function getEntityBlacklistedAttributes($entityType)
    {
        $blacklist = $this->config->getValue("bittools_skyhub/blacklist_{$entityType}/attributes");
        if (!$blacklist) {
            return [];
        }

        $blacklist = json_decode($blacklist, true);
        if (!$blacklist) {
            return [];
        }

        $return = [];
        foreach($blacklist as $value) {
            $return[$value['field']] = $value['field'];
        }
        return $return;
    }
    
    /**
     * @param string $attributeCode
     * @param string $entityType
     *
     * @return bool
     */
    public function isAttributeCodeInBlacklist($attributeCode, $entityType = 'catalog_product')
    {
        $attributes  = $this->getEntityBlacklistedAttributes($entityType);
        return isset($attributes[$attributeCode]);
    }
    
    
    /**
     * @param string $code
     *
     * @return array
     */
    public function getAttributeInstallConfig($code, $entityType = 'catalog_product')
    {
        $attributes = $this->getEntityAttributes($entityType);
        
        if (!isset($attributes[$code], $attributes[$code]['attribute_install_config'])) {
            return [];
        }

        $result = (array) $attributes[$code]['attribute_install_config'];
        if (isset($attributes[$code]['options'])) {
            $result['options'] = $attributes[$code]['options'];
        }

        return $result;
    }
}
