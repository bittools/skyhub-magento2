<?php

namespace BitTools\SkyHub\Model\ResourceModel\Queue\Collection;

use BitTools\SkyHub\Model\Entity;

class Product extends \BitTools\SkyHub\Model\ResourceModel\Queue\Collection
{
    
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->setEntityTypeFilter(Entity::TYPE_CATALOG_PRODUCT);
        
        parent::_beforeLoad();
        return $this;
    }
}
