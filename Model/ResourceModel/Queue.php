<?php


namespace BitTools\SkyHub\Model\ResourceModel;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Helper\Context as HelperContext;
use BitTools\SkyHub\Model\Queue as QueueModel;
use Magento\Store\Model\Store;

class Queue extends AbstractResourceModel
{
    
    use Functions;
    
    
    const MAIN_TABLE = 'bittools_skyhub_queue';
    
    
    /** @var HelperContext */
    protected $helperContext;
    
    
    /**
     * Initialize database relation.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
    
    
    /**
     * @param           $entityIds
     * @param           $entityType
     * @param int       $processType
     * @param bool      $canProcess
     * @param null      $processAfter
     * @param int|null  $storeId
     *
     * @return $this
     */
    public function queue(
        $entityIds,
        $entityType,
        $processType = QueueModel::PROCESS_TYPE_EXPORT,
        $storeId = Store::DEFAULT_STORE_ID,
        $canProcess = true,
        $processAfter = null
    ) {
        $entityIds = $this->filterEntityIds((array) $entityIds);
        
        if (empty($entityIds)) {
            return $this;
        }
        
        $items = [];
        
        $deleteSets = array_chunk($entityIds, 1000);
        
        foreach ($deleteSets as $deleteIds) {
            $this->beginTransaction();
            
            try {
                $where = $this->getCondition($deleteIds, $entityType, $storeId);
                $this->getConnection()->delete($this->getMainTable(), $where);
                
                $this->commit();
            } catch (\Exception $e) {
                $this->logger()->critical($e);
                $this->rollBack();
            }
        }
        
        foreach ($entityIds as $entityId) {
            $items[] = [
                'entity_id'     => (int) $entityId,
                'entity_type'   => (string) $entityType,
                'status'        => QueueModel::STATUS_PENDING,
                'process_type'  => (int) $processType,
                'can_process'   => (bool) $canProcess,
                'process_after' => empty($processAfter) ? $this->now() : $processAfter,
                'store_id'      => (int) $this->getStoreId($storeId),
                'created_at'    => $this->now(),
            ];
        }
        
        /** @var array $item */
        foreach ($items as $item) {
            $this->beginTransaction();
            
            try {
                $this->getConnection()->insert($this->getMainTable(), $item);
                $this->commit();
            } catch (\Exception $e) {
                $this->logger()->critical($e);
                $this->rollBack();
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param      $entityType
     * @param int  $processType
     * @param null $limit
     * @param null $storeId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingEntityIds(
        $entityType,
        $processType = QueueModel::PROCESS_TYPE_EXPORT,
        $limit = null,
        $storeId = null
    ) {
        $integrableStatuses = [
            QueueModel::STATUS_PENDING,
            QueueModel::STATUS_RETRY
        ];
        
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('status IN (?)', $integrableStatuses)
            ->where('can_process = ?', 1)
            ->where('process_type = ?', (int) $processType)
            ->where('store_id IN (?)', $this->getStoreIds($storeId))
            ->where('process_after <= ?', $this->now())
            ->where('entity_type = ?', (string) $entityType);
        
        if (!is_null($limit)) {
            $select->limit((int) $limit);
        }
        
        $ids = $this->getConnection()->fetchCol($select);
        
        return (array) $ids;
    }
    
    
    /**
     * @param      $entityIds
     * @param      $entityType
     * @param null $storeId
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeFromQueue($entityIds, $entityType, $storeId = null)
    {
        $entityIds = $this->filterEntityIds((array) $entityIds);
        
        if (empty($entityIds)) {
            return $this;
        }
        
        $where = $this->getCondition($entityIds, $entityType, $storeId);
        $this->getConnection()->delete($this->getMainTable(), $where);
        
        return $this;
    }
    
    
    /**
     * @param int|array $queueIds
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByQueueIds($queueIds)
    {
        $queueIds = (array) $this->filterEntityIds($queueIds);
        
        if (empty($queueIds)) {
            return $this;
        }
        
        $queueIds = implode(',', $queueIds);
        $where    = new \Zend_Db_Expr("id IN ({$queueIds})");
        
        $this->getConnection()->delete($this->getMainTable(), $where);
        
        return $this;
    }
    
    
    /**
     * @param integer|array $entityIds
     * @param string        $entityType
     * @param string|null   $message
     * @param integer       $storeId
     *
     * @return $this
     */
    public function setFailedEntityIds($entityIds, $entityType, $message = null, $storeId = null)
    {
        $this->updateQueueStatus($entityIds, $entityType, QueueModel::STATUS_FAIL, $message, $storeId);
        return $this;
    }
    
    
    /**
     * @param integer|array $entityIds
     * @param string        $entityType
     * @param string|null   $message
     * @param integer       $storeId
     *
     * @return $this
     */
    public function setPendingEntityIds($entityIds, $entityType, $message = null, $storeId = null)
    {
        $this->updateQueueStatus($entityIds, $entityType, QueueModel::STATUS_PENDING, $message, $storeId);
        return $this;
    }
    
    
    /**
     * @param integer|array $entityIds
     * @param string        $entityType
     * @param string|null   $message
     * @param integer       $storeId
     *
     * @return $this
     */
    public function setRetryEntityIds($entityIds, $entityType, $message = null, $storeId = null)
    {
        $this->updateQueueStatus($entityIds, $entityType, QueueModel::STATUS_RETRY, $message, $storeId);
        return $this;
    }
    
    
    /**
     * @param int|array   $entityIds
     * @param string      $entityType
     * @param int         $status
     * @param string|null $message
     * @param integer     $storeId
     *
     * @return $this
     */
    public function updateQueueStatus($entityIds, $entityType, $status, $message = null, $storeId = null)
    {
        $binds = [
            'status'   => $status,
            'messages' => $message,
        ];
        
        $this->updateQueues($entityIds, $entityType, $binds, $storeId);
        return $this;
    }
    
    
    /**
     * @param integer|array $entityIds
     * @param string        $entityType
     * @param array         $binds
     * @param integer       $storeId
     *
     * @return $this
     */
    public function updateQueues($entityIds, $entityType, array $binds = [], $storeId = null)
    {
        $entityIds = $this->filterEntityIds((array) $entityIds);
        
        if (empty($entityIds)) {
            return $this;
        }
        
        $where = $this->getCondition($entityIds, $entityType, $storeId);
        
        try {
            $this->getConnection()->update($this->getMainTable(), $binds, $where);
        } catch (\Exception $e) {
            $this->logger()->critical($e);
        }
        
        return $this;
    }
    
    
    /**
     * @param array $entityIds
     *
     * @return array
     */
    protected function filterEntityIds(array $entityIds)
    {
        $entityIds = (array) array_filter($entityIds, function (&$value) {
            $value = (int) $value;
            return $value;
        });
        
        return $entityIds;
    }
    
    
    /**
     * @param array  $entityIds
     * @param string $entityType
     * @param int    $storeId
     *
     * @return \Zend_Db_Expr
     */
    protected function getCondition(array $entityIds, $entityType, $storeId = null)
    {
        $entityIds  = implode(',', $entityIds);
        $storeIds   = implode(',', $this->getStoreIds($storeId));
        $conditions = [
            "entity_id IN ({$entityIds})",
            "entity_type = '{$entityType}'",
            "store_id IN ({$storeIds})"
        ];
        
        return new \Zend_Db_Expr(implode(' AND ', $conditions));
    }
    
    
    /**
     * @param int $storeId
     *
     * @return array
     */
    protected function getStoreIds($storeId = null)
    {
        $storeId = (int) $this->getStoreId($storeId);
        
        $storeIds = [0, $storeId];
        $storeIds = array_unique($storeIds);
        
        return $storeIds;
    }
    
    
    /**
     * @param null|int $storeId
     *
     * @return integer
     */
    protected function getStoreId($storeId = null)
    {
        return $this->getStore($storeId)->getId();
    }
    
    
    /**
     * @param null|int $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    protected function getStore($storeId = null)
    {
        try {
            return $this->storeManager()->getStore($storeId);
        } catch (\Exception $e) {
            $this->logger()->critical($e);
        }
        
        return $this->storeManager()->getDefaultStoreView();
    }
}
