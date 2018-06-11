<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel;

class Order extends AbstractResourceModel
{
    
    /** @var string */
    const MAIN_TABLE = 'bittools_skyhub_orders';
    
    /** @var string */
    const ID_FIELD   = 'id';
    
    
    /**
     * Initialize database relation.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD);
    }
    
    
    /**
     * @param string   $skyhubCode
     * @param int|null $storeId
     *
     * @return bool|int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderId($skyhubCode, $storeId = null)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'order_id')
            ->where('code = ?', $skyhubCode)
            ->where('store_id IN (?)', $this->getDefaultStoreIdsFilter($storeId))
            ->limit(1);
        
        $result = $this->getConnection()->fetchOne($select);
        
        if (!$result) {
            return false;
        }
        
        return (int) $result;
    }
    
    
    /**
     * @param string   $skyhubCode
     * @param null|int $storeId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderExists($skyhubCode, $storeId = null)
    {
        $orderId = $this->getOrderId($skyhubCode, $storeId);
        return (bool) $orderId;
    }


    /**
     * @param null|int $storeId
     *
     * @return array
     */
    public function getDefaultStoreIdsFilter($storeId = null)
    {
        $storeIds = [0];

        if (empty($storeId)) {
            return $storeIds;
        }

        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        $storeIds = array_merge($storeIds, $storeId);

        return $storeIds;
    }
    
    
    /**
     * @param string $incrementId
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityIdByIncrementId($incrementId)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('increment_id = ?', $incrementId)
            ->limit(1);
        
        return $this->getConnection()->fetchOne($select);
    }
    
    
    /**
     * @param string $code
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityIdBySkyhubCode($code)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('code = ?', $code)
            ->limit(1);
        
        return $this->getConnection()->fetchOne($select);
    }
    
    
    /**
     * @param int $orderId
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSkyhubCodeByOrderId($orderId)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'code')
            ->where('order_id = ?', $orderId)
            ->limit(1);
        
        return $this->getConnection()->fetchOne($select);
    }
}
