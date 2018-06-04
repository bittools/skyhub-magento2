<?php

namespace BitTools\SkyHub\Cron\Config\Catalog;

use BitTools\SkyHub\Cron\Config\AbstractCronConfig;

class Product extends AbstractCronConfig
{
    
    /** @var string */
    protected $group = 'cron_catalog_product';
    
    
    /**
     * @return integer
     */
    public function getQueueCreateLimit()
    {
        return (int) $this->getGroupConfig('queue_create_limit');
    }
    
    
    /**
     * @return integer
     */
    public function getQueueExecuteLimit()
    {
        return (int) $this->getGroupConfig('queue_execute_limit');
    }
}
