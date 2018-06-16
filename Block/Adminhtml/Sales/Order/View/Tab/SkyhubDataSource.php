<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class SkyhubDataSource extends AbstractOrder implements TabInterface
{
    
    /** @var string */
    protected $_template = 'order/view/tab/skyhub_data_source.phtml';
    
    
    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEncodedJsonDataSource()
    {
        if (!$this->getOrder()) {
            return false;
        }
        
        if (!$this->getOrder()->getExtensionAttributes()) {
            return false;
        }
        
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $info */
        $info = $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
        
        $decoded = json_decode($info->getDataSource());
        $pretty  = json_encode($decoded, JSON_PRETTY_PRINT);
        
        return $pretty;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('SkyHub Data Source');
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }
    
    
    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }
    
    
    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}
