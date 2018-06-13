<?php

namespace BitTools\SkyHub\Observer\Sales;

use BitTools\SkyHub\Observer\AbstractObserver;

abstract class AbstractSales extends AbstractObserver
{
    
    /**
     * @param string $configStatus
     * @param string $currentStatus
     *
     * @return bool
     */
    protected function statusMatches($configStatus, $currentStatus)
    {
        if ($currentStatus == $configStatus) {
            return true;
        }
        
        return false;
    }
}
