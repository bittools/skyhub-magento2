<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\Data as ConfigData;

class Data extends ConfigData
{
    
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
        return $this->get("blacklist/{$entityType}");
    }
    
    /**
     * @param string $attributeCode
     * @param string $entityType
     *
     * @return bool
     */
    public function isAttributeCodeInBlacklist($attributeCode, $entityType = 'catalog_product')
    {
        $blacklist  = $this->getBlacklistedAttributes();
        $attributes = isset($blacklist[$entityType]) ? $blacklist[$entityType] : [];

        return in_array($attributeCode, $attributes);
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
