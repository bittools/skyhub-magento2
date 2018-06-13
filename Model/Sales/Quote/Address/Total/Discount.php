<?php

namespace BitTools\SkyHub\Model\Sales\Quote\Address\Total;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        
        return $this;
    }
}
