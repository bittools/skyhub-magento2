<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel\Catalog\Product\Attributes;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Mapping extends AbstractDb
{
    /**
     * Initialize database relation.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bittools_skyhub_product_attributes_mapping', 'id');
    }
}
