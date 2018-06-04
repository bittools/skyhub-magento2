<?php

namespace BitTools\SkyHub\Cron\Config\Sales;

use BitTools\SkyHub\Cron\Config\AbstractCronConfig;

class Order extends AbstractCronConfig
{
    
    /** @var string */
    protected $group = 'cron_sales_order_import';
    
    
    /**
     * @return int
     */
    public function getLimit()
    {
        return (int) $this->getGroupConfig('limit');
    }
}
