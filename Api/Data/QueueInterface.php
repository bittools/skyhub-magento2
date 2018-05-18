<?php

namespace BitTools\SkyHub\Api\Data;

/**
 * @method $this setReference(\string $reference)
 * @method $this setEntityType(\string $type)
 * @method $this setStatus(\int $status)
 * @method $this setMessages(\string $message)
 * @method $this setCanProcess(\boolean $flag)
 * @method $this setProcessAfter(\string $datetime)
 * @method $this setCreatedAt(\string $datetime)
 * @method $this setUpdatedAt(\string $datetime)
 *
 * @method string  getReference()
 * @method string  getEntityType()
 * @method int     getStatus()
 * @method string  getMessages()
 * @method boolean getCanProcess()
 * @method string  getProcessAfter()
 * @method string  getCreatedAt()
 * @method string  getUpdatedAt()
 */
interface QueueInterface
{
    
    /**
     * @param int    $entityId
     * @param string $entityType
     * @param bool   $canProcess
     * @param null   $processAfter
     *
     * @return mixed
     */
    public function queue($entityId, $entityType, $canProcess = true, $processAfter = null);
}
