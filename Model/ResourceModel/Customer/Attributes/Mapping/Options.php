<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Options extends AbstractDb
{
    const MAIN_TABLE = 'bittools_skyhub_customer_attributes_mapping_options';

    /**
     * Initialize database relation.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bittools_skyhub_customer_attributes_mapping_options', 'id');
    }
}
