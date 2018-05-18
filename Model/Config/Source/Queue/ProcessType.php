<?php

namespace BitTools\SkyHub\Model\Config\Source\Queue;

use BitTools\SkyHub\Model\Config\Source\AbstractSource;
use BitTools\SkyHub\Model\Queue;

class ProcessType extends AbstractSource
{
    
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Queue::PROCESS_TYPE_IMPORT => __('Import Process'),
            Queue::PROCESS_TYPE_EXPORT => __('Export Process'),
        ];
    }
}
