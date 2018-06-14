<?php

namespace BitTools\SkyHub\Helper\Sales;

use BitTools\SkyHub\Helper\AbstractHelper;
use Magento\Sales\Model\Order as SalesOrder;

class Order extends AbstractHelper
{

    /**
     * @param string $skyhubCode
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderId($skyhubCode)
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Order $orderResource */
        $orderResource = $this->objectManager()->create(\BitTools\SkyHub\Model\ResourceModel\Order::class);
        $orderId       = $orderResource->getEntityIdBySkyhubCode($skyhubCode);

        return $orderId;
    }


    /**
     * @param string $code
     *
     * @return int
     */
    public function getNewOrderIncrementId($code)
    {
        $useDefaultIncrementId = $this->context->configContext()->salesOrderImport()->useDefaultIncrementId();

        if (!$useDefaultIncrementId) {
            return $code;
        }

        return null;
    }


    /**
     * @param int $orderId (entity_id)
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderIncrementId($orderId)
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Order $orderResource */
        $orderResource = $this->objectManager()->create(\BitTools\SkyHub\Model\ResourceModel\Order::class);
        $skyhubCode    = $orderResource->getSkyhubCodeByOrderId($orderId);

        return $skyhubCode;
    }
    
    
    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingOrdersFromSkyHub()
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Order $orderResource */
        $orderResource = $this->objectManager()->create(\BitTools\SkyHub\Model\ResourceModel\Order::class);
        
        $deniedStates = [
            SalesOrder::STATE_CANCELED,
            SalesOrder::STATE_CLOSED,
            SalesOrder::STATE_COMPLETE,
        ];

        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->objectManager()->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $collection->join(['bso' => $orderResource->getMainTable()], 'bso.order_id = main_table.entity_id');
        $collection->addFieldToFilter('state', ['nin' => $deniedStates]);

        return $collection;
    }
}
