<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class InvoiceKey extends AbstractOrder implements TabInterface
{
    
    /** @var string */
    protected $_template = 'order/view/tab/invoice_key.phtml';
    
    
    /**
     * @return bool|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInvoiceKey()
    {
        if (!$this->getOrder()) {
            return false;
        }
        
        if (!$this->getOrder()->getExtensionAttributes()) {
            return false;
        }
        
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $info */
        $info = $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
        
        return $info->getInvoiceKey();
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Invoice Key');
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }
    
    
    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canShowTab()
    {
        $invoices = $this->getOrder()->getInvoiceCollection()->getSize();
    
        if (!$invoices) {
            return false;
        }
    
        return true;
    }
    
    
    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }
    
    
    /**
     * Submit URL getter
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('bittools_skyhub/sales_order_invoicekey/assign', [
            'order_id' => $this->getOrder()->getId()
        ]);
    }
    
    
    /**
     * @return string
     */
    public function getContainerId()
    {
        return 'invoice_key_container';
    }
    
    
    /**
     * @{inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $onclick    = "submitAndReloadArea($('{$this->getContainerId()}').parentNode, '{$this->getSubmitUrl()}')";
        $buttonData = [
            'label' => __('Submit Invoice Key Number'),
            'class' => 'action-save action-primary',
            'onclick' => $onclick
        ];
        
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $button->setData($buttonData);
        
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }
}
