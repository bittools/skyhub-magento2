<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel\Queue;

use BitTools\SkyHub\Model\Queue;
use BitTools\SkyHub\Model\ResourceModel\Queue as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    
    /** @var string */
    const FIELD_ENTITY_TYPE = 'entity_type';
    
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Queue::class, ResourceModel::class);
    }
    
    
    /**
     * @param string $type
     *
     * @return $this
     */
    public function setEntityTypeFilter($type)
    {
        $this->addFieldToFilter(self::FIELD_ENTITY_TYPE, $type);
        return $this;
    }
}
