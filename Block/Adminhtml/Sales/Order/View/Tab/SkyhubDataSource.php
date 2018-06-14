<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class SkyhubDataSource extends \Magento\Backend\Block\Template implements TabInterface
{
    
    /** @var string */
    protected $_template = 'order/view/tab/skyhub_data_source.phtml';
    
    /** @var \Magento\Framework\Registry */
    protected $coreRegistry = null;
    
    /** @var \Magento\Sales\Helper\Admin */
    protected $adminHelper;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->coreRegistry = $registry;
        $this->adminHelper  = $adminHelper;
    }
    
    
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
    
    /**
     * @return bool|string
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
        return __('SkyHub Data Source');
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
