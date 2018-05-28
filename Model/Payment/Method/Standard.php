<?php

namespace BitTools\SkyHub\Model\Payment\Method;

use Magento\Payment\Model\Method\Free as FreePayment;
use Magento\Quote\Api\Data\CartInterface;

class Standard extends FreePayment
{

    /** @var string */
    protected $_code = 'bseller_skyhub_standard';

    /** @var bool */
    protected $_canUseCheckout = false;

    /** @var bool */
    protected $_isOffline = true;

    /** @var bool */
    protected $_canUseInternal = false;


    /**
     * @param CartInterface|null $quote
     *
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }
}
