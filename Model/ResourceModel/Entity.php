<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel;

use BitTools\SkyHub\Functions;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use BitTools\SkyHub\Helper\Context as HelperContext;

class Entity extends AbstractDb
{
    
    use Functions;
    
    /** @var string  */
    const MAIN_TABLE = 'bittools_skyhub_entity_id';
    
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var HelperContext */
    protected $helperContext;
    
    
    /**
     * Entity constructor.
     *
     * @param Context         $context
     * @param LoggerInterface $logger
     * @param HelperContext   $helperContext
     */
    public function __construct(Context $context, LoggerInterface $logger, HelperContext $helperContext)
    {
        parent::__construct($context);
        
        $this->logger        = $logger;
        $this->helperContext = $helperContext;
    }
    
    
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
                'created_at'  => $this->now(),
                'updated_at'  => $this->now(),
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
        
        $data = [
            'updated_at' => $this->now(),
        ];
        
        $where = [
            'entity_id = ?'   => (int)    $entityId,
            'entity_type = ?' => (string) $entityType,
            'store_id = ?'    => (int)    $this->getStoreId($storeId),
        ];
        
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
            ->where('store_id IN (?)', (array) $this->getStoreIdFilter($storeId))
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
     * @param string $entityType
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
     * @param int|null $storeId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreIdFilter($storeId = null)
    {
        return [
            Store::DEFAULT_STORE_ID,
            $this->getStoreId($storeId)
        ];
    }


    /**
     * @param int|null $storeId
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId($storeId = null)
    {
        return $this->helperContext->storeManager()->getStore($storeId)->getId();
    }
}
