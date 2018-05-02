<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model;

use BitTools\SkyHub\Model\ResourceModel\Queue as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel
{
    
    const STATUS_PENDING       = 1;
    const STATUS_FAIL          = 2;
    const STATUS_RETRY         = 3;
    
    const PROCESS_TYPE_IMPORT  = 1;
    const PROCESS_TYPE_EXPORT  = 2;
    
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
