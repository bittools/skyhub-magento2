<?php

namespace BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue\Catalog;

use BitTools\SkyHub\Ui\Component\DataProvider\SearchResult\Queue;

class Product extends Queue
{
    
    /** @var string */
    protected $entityType = 'catalog_product';
    
    
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->join(
            [
                'e' => $this->getTable('catalog_product_entity')
            ],
            'e.entity_id = queue.entity_id',
            [
                'sku',
                'type_id',
                'attribute_set_id'
            ]
        );
        
        parent::_beforeLoad();
        return $this;
    }
}
