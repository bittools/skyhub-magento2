<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\Data as ConfigData;

class Data extends ConfigData
{
    
    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->get('attributes');
    }
    
    
    /**
     * @return array
     */
    public function getBlacklistedAttributes()
    {
        return $this->get('blacklist');
    }
    
    
    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    public function isAttributeCodeInBlacklist($attributeCode)
    {
        $blacklist = $this->getBlacklistedAttributes();
        return in_array($attributeCode, $blacklist);
    }
    
    
    /**
     * @param string $code
     *
     * @return array
     */
    public function getAttributeInstallConfig($code)
    {
        $attributes = $this->getAttributes();
        
        if (!isset($attributes[$code], $attributes[$code]['attribute_install_config'])) {
            return [];
        }
        
        return (array) $attributes[$code]['attribute_install_config'];
    }
}
