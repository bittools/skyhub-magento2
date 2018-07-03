<?php

namespace BitTools\SkyHub\Model\Sales\Quote\Address\Total;

abstract class AbstractTotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    /** @var \Magento\Directory\Model\CurrencyFactory */
    protected $currencyFactory;
    
    /** @var \Magento\Directory\Api\Data\CurrencyInformationInterface */
    protected $currencyInformation;
    
    
    public function __construct(
        \Magento\Directory\Api\Data\CurrencyInformationInterface $currencyInformation,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->currencyFactory     = $currencyFactory;
        $this->currencyInformation = $currencyInformation;
    }
    
    
    /**
     * @param float $price
     *
     * @return float
     *
     * @throws \Exception
     */
    protected function convertToBasePrice($price)
    {
        try {
            /** @var \Magento\Directory\Model\Currency $currency */
            $currency  = $this->currencyFactory->create();
            $basePrice = $currency->convert($price, $this->currencyInformation->getBaseCurrencyCode());
        } catch (\Exception $e) {
            return null;
        }
        
        return (float) $basePrice;
    }
}
