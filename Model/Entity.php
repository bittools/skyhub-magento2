<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model;

use BitTools\SkyHub\Model\ResourceModel\Entity as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class Entity extends AbstractModel
{
    
    const TYPE_CATALOG_PRODUCT_ATTRIBUTE = 'catalog_product_attribute';
    const TYPE_CATALOG_PRODUCT           = 'catalog_product';
    const TYPE_CATALOG_CATEGORY          = 'catalog_category';
    const TYPE_SALES_ORDER               = 'sales_order';
    const TYPE_SALES_ORDER_STATUS        = 'sales_order_status';
    
    
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
