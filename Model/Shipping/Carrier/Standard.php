<?php

namespace BitTools\SkyHub\Model\Shipping\Carrier;

use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Standard extends Freeshipping
{

    /** @var string */
    const CODE = 'bittools_skyhub_standard';


    /** @var string */
    protected $_code = self::CODE;


    /** @var \Magento\Quote\Model\Quote */
    protected $quote;


    /**
     * @param RateRequest $rateRequest
     *
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $rateRequest)
    {
        if (!$this->isAvailable($rateRequest)) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getQuote($rateRequest);

        $methodCode   = $quote->getData('fixed_shipping_method_code');
        $methodTitle  = $quote->getData('fixed_shipping_title');
        $methodAmount = (float) $quote->getData('fixed_shipping_amount');

        if (!$methodCode || !$methodTitle) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($methodCode);
        $method->setMethodTitle($methodTitle);

        $method->setPrice($methodAmount);
        $method->setCost($methodAmount);

        $result->append($method);

        return $result;
    }


    protected function isAvailable(RateRequest $rateRequest)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->getQuote($rateRequest)) {
            return false;
        }

        return true;
    }


    /**
     * @param RateRequest $rateRequest
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote(RateRequest $rateRequest)
    {
        if ($this->quote) {
            return $this->quote;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            if (!$item || !$item->getQuote()) {
                continue;
            }

            $this->quote = $item->getQuote();
        }

        return $this->quote;
    }
}
