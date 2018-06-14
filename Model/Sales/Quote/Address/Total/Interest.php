<?php

namespace BitTools\SkyHub\Model\Sales\Quote\Address\Total;

class Interest extends AbstractTotal
{
    
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('SkyHub Interest');
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
    
        $interest = (float) $quote->getData('skyhub_interest');
    
        if (!$interest) {
            return $this;
        }
    
        $interest     = abs($interest);
        $baseInterest = $this->convertToBasePrice($interest);
    
        $total->addTotalAmount('tax', $interest);
        $total->addBaseTotalAmount('tax', $baseInterest);
        
        return $this;
    }
}
