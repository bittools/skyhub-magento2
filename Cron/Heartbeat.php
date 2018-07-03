<?php

namespace BitTools\SkyHub\Cron;

use Magento\Cron\Model\Schedule;

class Heartbeat extends AbstractCron
{

    /** @var string */
    const JOB_CODE = 'bittools_skyhub_heartbeat';
    
    /** @var Schedule */
    protected $lastHeartbeat;


    /**
     * @param Schedule $schedule
     *
     * @return bool
     */
    public function execute(Schedule $schedule)
    {
        $schedule->setMessages(__("The module's heart is beating correctly!"));
        return true;
    }


    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isHeartbeatConfigured()
    {
        return (bool) $this->getLastHeartbeat();
    }
    
    
    /**
     * @return bool|float|int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastHeartbeatTimeInMinutes()
    {
        /** @var Schedule $schedule */
        $schedule = $this->getLastHeartbeat();
    
        if (!$schedule) {
            return false;
        }
    
        $lastExecution = strtotime($schedule->getExecutedAt());
        $duration      = (time()-$lastExecution)/60;
        
        return $duration;
    }


    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isHeartbeatOlderThanOneHour()
    {
        /** @var Schedule $schedule */
        $schedule = $this->getLastHeartbeat();

        if (!$schedule) {
            return true;
        }
        
        if (false === $this->getLastHeartbeatTimeInMinutes()) {
            return true;
        }

        if ($this->getLastHeartbeatTimeInMinutes() > 60) {
            return true;
        }

        return false;
    }


    /**
     * @return bool|Schedule
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastHeartbeat()
    {
        if ($this->lastHeartbeat) {
            return $this->lastHeartbeat;
        }
        
        $scheduleId = $this->getLastHeartbeatScheduleId();

        if (!$scheduleId) {
            return false;
        }

        /** @var Schedule $schedule */
        $this->lastHeartbeat = $this->createObject(Schedule::class);
        $this->lastHeartbeat->load($scheduleId);

        return $this->lastHeartbeat;
    }


    /**
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastHeartbeatScheduleId()
    {
        /** @var \Magento\Cron\Model\ResourceModel\Schedule $resource */
        $resource = $this->createObject(\Magento\Cron\Model\ResourceModel\Schedule::class);

        /** @var \Magento\Framework\DB\Select $select */
        $select = $resource->getConnection()
            ->select()
            ->from($resource->getMainTable(), 'schedule_id')
            ->where('job_code = ?', self::JOB_CODE)
            ->where('status = ?', Schedule::STATUS_SUCCESS)
            ->order('executed_at DESC')
            ->limit(1);

        $scheduleId = $resource->getConnection()->fetchOne($select);
        return $scheduleId;
    }
}
