<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\Customer\Attributes\Mapping;

use BitTools\SkyHub\Api\Data\CustomerAttributeMappingOptionsInterface;
use Magento\Framework\Model\AbstractModel;
use BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping\Options as ResourceModel;

class Options extends AbstractModel implements CustomerAttributeMappingOptionsInterface
{

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
