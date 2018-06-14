<?php

namespace BitTools\SkyHub\Model\Sales\Quote\Address\Total;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    /** @var \Magento\Directory\Model\CurrencyFactory */
    protected $currencyFactory;
    
    /** @var \Magento\Directory\Api\Data\CurrencyInformationInterface */
    protected $currencyInformation;
    
    
    public function __construct(
        \Magento\Directory\Api\Data\CurrencyInformationInterface $currencyInformation,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        $this->setCode('discount');
        
        $this->currencyFactory     = $currencyFactory;
        $this->currencyInformation = $currencyInformation;
    }
    
    
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('SkyHub Discount');
    }
    
    
    /**
     * @param \Magento\Quote\Model\Quote                          $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total            $total
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
    
        $skyhubDiscount = (float) $quote->getData('skyhub_discount_amount');
    
        if (!$skyhubDiscount) {
            return $this;
        }
        
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->currencyFactory->create();
    
        $skyhubDiscount     = abs($skyhubDiscount);
        $skyhubBaseDiscount = $currency->convert($skyhubDiscount, $this->currencyInformation->getBaseCurrencyCode());
        
        $total->addTotalAmount('discount', $skyhubDiscount * (-1));
        $total->addBaseTotalAmount('discount', $skyhubBaseDiscount * (-1));
        
        return $this;
    }
}
