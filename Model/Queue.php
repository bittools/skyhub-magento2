<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model;

use BitTools\SkyHub\Api\Data\QueueInterface;
use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Model\ResourceModel\Queue as ResourceModel;
use Magento\Framework\Model\AbstractModel;

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
class Queue extends AbstractModel implements QueueInterface
{
    
    use Functions;
    
    
    const STATUS_PENDING       = 1;
    const STATUS_FAIL          = 2;
    const STATUS_RETRY         = 3;
    
    const PROCESS_TYPE_IMPORT  = 1;
    const PROCESS_TYPE_EXPORT  = 2;
    
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
    
    
    /**
     * @param int           $entityId
     * @param string        $entityType
     * @param bool          $canProcess
     * @param null|string   $processAfter
     * @return $this
     */
    public function queue($entityId, $entityType, $canProcess = true, $processAfter = null)
    {
        $this->setEntityId($entityId)
            ->setEntityType($entityType)
            ->setCanProcess((bool) $canProcess)
            ->setProcessAfter($processAfter);
        
        return $this;
    }
    
    
    /**
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getProcessAfter()) {
            $this->setProcessAfter($this->now());
        }
        
        parent::beforeSave();
        
        return $this;
    }
}
