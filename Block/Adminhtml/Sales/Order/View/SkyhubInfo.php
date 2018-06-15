<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class SkyhubInfo extends AbstractOrder
{
    
    /**
     * @return null|string
     */
    public function getSkyhubCode()
    {
        if (!$this->canDisplay()) {
            return null;
        }
        
        return $this->getSkyhubInfo()->getCode();
    }
    
    
    /**
     * @return null|string
     */
    public function getSkyhubChannel()
    {
        if (!$this->canDisplay()) {
            return null;
        }
    
        return $this->getSkyhubInfo()->getChannel();
    }
    
    
    /**
     * @return \BitTools\SkyHub\Api\Data\OrderInterface|null
     */
    public function getSkyhubInfo()
    {
        try {
            return $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
        } catch (\Exception $e) {
        }
        
        return null;
    }
    
    
    /**
     * @return bool
     */
    public function canDisplay()
    {
        try {
            if (!$this->getOrder() || !$this->getOrder()->getEntityId()) {
                return false;
            }
            
            if (!$this->getOrder()->getExtensionAttributes()) {
                return false;
            }
            
            if (!$this->getOrder()->getExtensionAttributes()->getSkyhubInfo()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
}
