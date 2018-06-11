<?php

namespace BitTools\SkyHub\Integration\Integrator\Sales;

use BitTools\SkyHub\Integration\Integrator\AbstractIntegrator;

abstract class AbstractSales extends AbstractIntegrator
{
    
    /**
     * @param string $skyhubCode
     *
     * @return int
     */
    protected function getOrderId($skyhubCode)
    {
        try {
            /** @var \BitTools\SkyHub\Model\ResourceModel\Order $resource */
            $resource = $this->context->objectManager()->get(\BitTools\SkyHub\Model\ResourceModel\Order::class);
            $orderId  = $resource->getEntityIdBySkyhubCode($skyhubCode);
        } catch (\Exception $e) {
            $this->context->helperContext()->logger()->critical($e);
        }
        
        return $orderId;
    }
    
    
    /**
     * @param int $orderId
     *
     * @return string
     */
    protected function getOrderIncrementId($orderId)
    {
        try {
            /** @var \BitTools\SkyHub\Model\ResourceModel\Order $resource */
            $resource   = $this->context->objectManager()->get(\BitTools\SkyHub\Model\ResourceModel\Order::class);
            $skyhubCode = $resource->getSkyhubCodeByOrderId((int) $orderId);
        } catch (\Exception $e) {
            $this->context->helperContext()->logger()->critical($e);
        }
        
        return trim($skyhubCode);
    }
}
