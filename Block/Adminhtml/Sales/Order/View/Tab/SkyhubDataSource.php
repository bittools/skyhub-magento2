<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class SkyhubDataSource extends AbstractOrder implements TabInterface
{
    
    /** @var string */
    protected $_template = 'order/view/tab/skyhub_data_source.phtml';


    /**
     * @param bool $pretty
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEncodedJsonDataSource($pretty = false)
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

        if ($pretty) {
            $decoded  = json_encode($decoded, JSON_PRETTY_PRINT);
        }

        return $decoded;
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
        if (!$this->getEncodedJsonDataSource()) {
            return false;
        }

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
