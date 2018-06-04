<?php

namespace BitTools\SkyHub\Cron\Config\Sales\Order;

use BitTools\SkyHub\Cron\Config\AbstractCronConfig;

class Status extends AbstractCronConfig
{
    
    protected $group = 'cron_sales_order_status';
    
    
    /**
     * @return int
     */
    public function queueCreateLimit()
    {
        return (int) $this->getGroupConfig('queue_create_limit');
    }
    
    
    /**
     * @return int
     */
    public function queueExecuteLimit()
    {
        return (int) $this->getGroupConfig('queue_execute_limit');
    }
}
