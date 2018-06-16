<?php

namespace BitTools\SkyHub\Helper\Sales\Order\Created;

use Magento\Sales\Api\Data\OrderInterface;

class Message
{
    
    /**
     * @param OrderInterface $order
     * @param string         $skyhubCode
     *
     * @return \Magento\Framework\Phrase
     */
    public function getOrderCreationMessage(OrderInterface $order, $skyhubCode)
    {
        if (true === $order->getData('is_created')) {
            return __(
                'The order code %1 was successfully created. Order ID %2.',
                $skyhubCode,
                $order->getIncrementId()
            );
        }
        
        if (true === $order->getData('is_updated')) {
            return __(
                'The order code %1 already exists and had its status updated. Order ID %2.',
                $skyhubCode,
                $order->getIncrementId()
            );
        }
        
        return __(
            'The order code %1 already exists and did not need to be updated. Order ID %2.',
            $skyhubCode,
            $order->getIncrementId()
        );
    }
    
    
    /**
     * @param string $skyhubCode
     *
     * @return \Magento\Framework\Phrase
     */
    public function getNonExistentOrderMessage($skyhubCode)
    {
        return __('The order reference "%1" does not exist in Skyhub.', $skyhubCode);
    }
    
    
    /**
     * @param string $skyhubCode
     *
     * @return \Magento\Framework\Phrase
     */
    public function getNotCreatedOrderMessage($skyhubCode)
    {
        return __('The order reference "%1" could not be created. See the logs for more details.', $skyhubCode);
    }
}
