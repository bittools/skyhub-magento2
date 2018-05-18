<?php

namespace BitTools\SkyHub\Model\Config\Source\Queue;

use BitTools\SkyHub\Model\Config\Source\AbstractSource;
use BitTools\SkyHub\Model\Queue;

class Status extends AbstractSource
{
    
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Queue::STATUS_PENDING => __('Pending'),
            Queue::STATUS_FAIL    => __('Fail'),
            Queue::STATUS_RETRY   => __('Retry'),
        ];
    }
}
