<?php

namespace BitTools\SkyHub\Cron\Config;

interface ConfigInterface
{
    
    /**
     * @return bool
     */
    public function isEnabled();
    
    
    /**
     * @param string $field
     *
     * @return mixed
     */
    public function getGroupConfig($field);
}
