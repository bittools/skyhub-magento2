<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\Data as ConfigData;

class Data extends ConfigData
{
    
    /**
     * @return array
     */
    public function getCatalogProductAttributes()
    {
        return $this->get('attributes/catalog_product');
    }


    /**
     * @return array
     */
    public function getBlacklistedAttributes()
    {
        return $this->get('blacklist');
    }
    
    
    /**
     * @return array
     */
    public function getCatalogProductBlacklistedAttributes()
    {
        return $this->get('blacklist/catalog_product');
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
    public function getAttributeInstallConfig($code)
    {
        $attributes = $this->getCatalogProductAttributes();
        
        if (!isset($attributes[$code], $attributes[$code]['attribute_install_config'])) {
            return [];
        }
        
        return (array) $attributes[$code]['attribute_install_config'];
    }
}
