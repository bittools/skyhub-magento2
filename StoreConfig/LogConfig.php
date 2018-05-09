<?php

namespace BitTools\SkyHub\StoreConfig;

class LogConfig extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'log';
    
    
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getSkyHubModuleConfig('enabled');
    }
    
    
    /**
     * @return string
     */
    public function getFilename()
    {
        return (string) $this->getSkyHubModuleConfig('filename');
    }
}
