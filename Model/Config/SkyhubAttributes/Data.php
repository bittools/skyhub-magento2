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
    public function getBlacklist()
    {
        return $this->get('blacklist');
    }
}
