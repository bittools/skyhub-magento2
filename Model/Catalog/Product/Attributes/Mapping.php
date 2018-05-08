<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\Catalog\Product\Attributes;

use BitTools\SkyHub\Api\Data\ProductAttributeMappingInterface;
use BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes\Mapping as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class Mapping extends AbstractModel implements ProductAttributeMappingInterface
{
    
    const DATA_TYPE_STRING   = 'string';
    const DATA_TYPE_BOOLEAN  = 'boolean';
    const DATA_TYPE_DECIMAL  = 'decimal';
    const DATA_TYPE_INTEGER  = 'integer';
    
    
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
