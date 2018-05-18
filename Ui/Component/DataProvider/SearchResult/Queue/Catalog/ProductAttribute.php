<?php

namespace BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue\Catalog;

use BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue;

class ProductAttribute extends Queue
{
    
    /** @var string */
    protected $entityType = 'catalog_product_attribute';
    
    
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->join(
            [
                'e' => $this->getTable('eav_attribute')
            ],
            'e.attribute_id = queue.entity_id'
        );
    
        parent::_beforeLoad();
        
        return $this;
    }
}
