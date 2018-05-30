<?php

namespace BitTools\SkyHub\Model\Backend\Session;

class Quote extends \Magento\Backend\Model\Session\Quote
{

    /**
     * @return $this
     */
    public function clear()
    {
        $this->_quote = null;
        $this->_order = null;
        $this->_store = null;

        $this->clearStorage();

        return $this;
    }
}
