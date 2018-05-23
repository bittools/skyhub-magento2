<?php

namespace BitTools\SkyHub\Integration\Integrator\Sales;

class Order extends AbstractSales
{
    
    /**
     * @param int   $page
     * @param int   $perPage
     * @param null  $saleSystem
     * @param array $statuses
     *
     * @return array|bool
     */
    public function orders($page = 1, $perPage = 30, $saleSystem = null, array $statuses = [])
    {
        $result = $this->getEntityInterface(true)->orders($page, $perPage, $saleSystem, $statuses);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        $orders = $result->toArray();
        
        if (empty($orders) || !isset($orders['orders'])) {
            return false;
        }
        
        return (array) $orders['orders'];
    }
    
    
    /**
     * @param integer $orderId
     *
     * @return array|bool
     */
    public function orderByOrderId($orderId)
    {
        $incrementId = $this->getOrderIncrementId((int) $orderId);
        
        if (empty($incrementId)) {
            return false;
        }
        
        return $this->order($incrementId);
    }
    
    
    /**
     * @param int|string $orderReference
     *
     * @return array|bool
     */
    public function order($orderReference)
    {
        /** @var  $result */
        $result = $this->getEntityInterface(true)->order($orderReference);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        $order = $result->toArray();
        
        return (array) $order;
    }
    
    
    /**
     * @param int    $orderId
     * @param string $invoiceKey
     *
     * @return bool
     */
    public function invoice($orderId, $invoiceKey)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $result */
        $result = $this->getEntityInterface(true)->invoice($incrementId, $invoiceKey);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param int $orderId
     *
     * @return bool
     */
    public function cancel($orderId)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $result */
        $result = $this->getEntityInterface(true)->cancel($incrementId);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param int $orderId
     *
     * @return bool
     */
    public function delivery($orderId)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $result */
        $result = $this->getEntityInterface(true)->delivery($incrementId);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param int $orderId
     *
     * @return array|bool
     */
    public function shipmentLabels($orderId)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $result */
        $result = $this->getEntityInterface(true)->shipmentLabels($incrementId);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        return $result->toArray();
    }
    
    
    /**
     * @param string $orderId
     * @param array  $items
     * @param string $trackCode
     * @param string $trackCarrier
     * @param string $trackMethod
     * @param string $trackUrl
     *
     * @return array|bool
     */
    public function shipment($orderId, array $items, $trackCode, $trackCarrier, $trackMethod, $trackUrl)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        
        /** @var \SkyHub\Api\Handler\Response\HandlerInterface $result */
        $result = $this->getEntityInterface(true)
            ->shipment($incrementId, $items, $trackCode, $trackCarrier, $trackMethod, $trackUrl);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        return $result->toArray();
    }
    
    
    /**
     * @param int    $orderId
     * @param string $datetime
     * @param string $observation
     *
     * @return bool
     */
    public function shipmentException($orderId, $datetime, $observation)
    {
        $incrementId = $this->getOrderIncrementId($orderId);
        $result = $this->getEntityInterface(true)
            ->shipmentException($incrementId, $datetime, $observation);
        
        if ($result->exception() || $result->invalid()) {
            return false;
        }
        
        /** @var \SkyHub\Api\Handler\Response\HandlerDefault $result */
        return true;
    }
    
    
    /**
     * @return \SkyHub\Api\EntityInterface\Sales\Order
     */
    protected function getEntityInterface($new = false)
    {
        return $this->api((bool) $new)->order()->entityInterface();
    }
}
