<?php

namespace BitTools\SkyHub\Model\Sales\Quote\Address\Total;

class Discount extends AbstractTotal
{
    
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
    
        $discount = (float) $quote->getData('skyhub_discount_amount');
    
        if (!$discount) {
            return $this;
        }
    
        $discount     = abs($discount);
        $baseDiscount = $this->convertToBasePrice($discount);
        
        $total->addTotalAmount('discount', $discount * (-1));
        $total->addBaseTotalAmount('discount', $baseDiscount * (-1));
        
        return $this;
    }
}
