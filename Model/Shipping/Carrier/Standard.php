<?php

namespace BitTools\SkyHub\Model\Shipping\Carrier;

use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Standard extends Freeshipping
{

    /** @var string */
    protected $_code = 'bseller_skyhub_standard';


    /**
     * @param RateRequest $rateRequest
     *
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $rateRequest)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier('freeshipping');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('freeshipping');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
    }


    protected function getQuote()
    {

    }
}
