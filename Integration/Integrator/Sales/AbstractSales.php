<?php

namespace BitTools\SkyHub\Integration\Integrator\Sales;

use BitTools\SkyHub\Integration\Context;
use BitTools\SkyHub\Model\ResourceModel\Sales\Order as OrderResource;
use BitTools\SkyHub\Integration\Integrator\AbstractIntegrator;
use BitTools\SkyHub\Model\ResourceModel\Sales\OrderFactory;

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
            /** @var OrderResource $resource */
            $resource = $this->context->objectManager()->get(OrderResource::class);
            $orderId  = $resource->getEntityIdBySkyhubCode($skyhubCode);
        } catch (\Exception $e) {
        
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
            /** @var OrderResource $resource */
            $resource   = $this->context->objectManager()->get(OrderResource::class);
            $skyhubCode = $resource->getSkyhubCodeByOrderId((int) $orderId);
        } catch (\Exception $e) {
        
        }
        
        return $skyhubCode;
    }
}
