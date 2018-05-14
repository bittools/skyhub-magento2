<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Entity extends AbstractDb
{
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var StoreManagerInterface */
    protected $storeManager;
    
    
    /**
     * Entity constructor.
     *
     * @param Context               $context
     * @param null|string           $connectionName
     * @param LoggerInterface       $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context, $connectionName);
        
        $this->logger       = $logger;
        $this->storeManager = $storeManager;
    }
    
    
    /**
     * Initialize database relation.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->getTable('bittools_skyhub_entity_id'), 'id');
    }
    
    
    /**
     * @param integer $entityId
     * @param string  $entityType
     * @param integer $storeId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createEntity($entityId, $entityType, $storeId = null)
    {
        $entityExists = $this->entityExists($entityId, $entityType);
        
        if ($entityExists) {
            return false;
        }
        
        try {
            $this->beginTransaction();
            $this->getConnection()->insert($this->getMainTable(), [
                'entity_id'   => (int)    $entityId,
                'entity_type' => (string) $entityType,
                'store_id'    => (int)    $this->getStoreId($storeId),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $this->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->rollBack();
        }
        
        return false;
    }
    
    
    /**
     * @param integer $entityId
     * @param string  $entityType
     * @param integer $storeId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateEntity($entityId, $entityType, $storeId = null)
    {
        $entityExists = $this->entityExists($entityId, $entityType);
        
        if (!$entityExists) {
            return false;
        }
        
        $data = array(
            'updated_at'  => time(),
        );
        
        $where = array(
            'entity_id = ?'   => (int)    $entityId,
            'entity_type = ?' => (string) $entityType,
            'store_id = ?'    => (int)    $this->getStoreId($storeId),
        );
        
        try {
            $this->beginTransaction();
            $this->getConnection()->update($this->getMainTable(), $data, $where);
            $this->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->rollBack();
        }
        
        return false;
    }
    
    
    /**
     * @param integer $entityId
     * @param string  $entityType
     * @param integer $storeId
     *
     * @return bool|int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function entityExists($entityId, $entityType, $storeId = null)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('entity_id = ?', (int) $entityId)
            ->where('entity_type = ?', (string) $entityType)
            ->where('store_id = ?', (int) $this->getStoreId($storeId))
            ->limit(1);
        
        try {
            $result = $this->getConnection()->fetchOne($select);
            
            if ($result) {
                return (int) $result;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        
        return false;
    }
    
    
    /**
     * @param $entityType
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function truncateEntityType($entityType)
    {
        $this->getConnection()
            ->query("DELETE FROM {$this->getMainTable()} WHERE entity_type = '{$entityType}'");
        return $this;
    }
    
    
    /**
     * @param int $storeId
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getId();
    }
}
