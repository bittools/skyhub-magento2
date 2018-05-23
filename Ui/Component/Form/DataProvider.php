<?php

namespace BitTools\SkyHub\Ui\Component\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    
    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        /** We've overridden this method because this class does not use a collection to be rendered. */
    }
    
    
    /**
     * @return null
     */
    public function getData()
    {
        /** This method also does not need any data to be loaded. */
        return null;
    }
}
