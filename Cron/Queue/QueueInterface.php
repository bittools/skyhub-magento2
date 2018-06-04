<?php

namespace BitTools\SkyHub\Cron\Queue;

use Magento\Cron\Model\Schedule;

interface QueueInterface
{
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed
     */
    public function create(Schedule $schedule);
    
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed
     */
    public function execute(Schedule $schedule);
}
