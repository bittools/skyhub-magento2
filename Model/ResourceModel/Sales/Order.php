<?php

namespace BitTools\SkyHub\Model\ResourceModel\Sales;

/**
 * @deprecated
 */
class Order extends \Magento\Sales\Model\ResourceModel\Order
{
    
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
            ->where('bittools_skyhub_code = ?', $code)
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
            ->from($this->getMainTable(), 'bittools_skyhub_code')
            ->where('entity_id = ?', $orderId)
            ->limit(1);
        
        return $this->getConnection()->fetchOne($select);
    }
}
