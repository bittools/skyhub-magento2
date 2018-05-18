<?php

namespace BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue\Sales;

use BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue;

class OrderStatus extends Queue
{
    
    /** @var string */
    protected $entityType = 'sales_order_status';
    
    
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->join(
            [
                'e' => $this->getTable('sales_order')
            ],
            'e.entity_id = queue.entity_id'
        );
        
        parent::_beforeLoad();
        return $this;
    }
}
