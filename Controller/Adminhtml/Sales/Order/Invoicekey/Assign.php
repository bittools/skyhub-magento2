<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Invoicekey;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Assign extends AbstractController
{
    
    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;
    
    /** @var \BitTools\SkyHub\Api\OrderRepositoryInterface */
    protected $orderRelationRepository;
    
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \BitTools\SkyHub\Helper\Context $helperContext,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \BitTools\SkyHub\Api\OrderRepositoryInterface $orderRelationRepository
    ) {
        parent::__construct($context, $helperContext);
        
        $this->orderRepository         = $orderRepository;
        $this->orderRelationRepository = $orderRelationRepository;
    }
    
    
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $result */
        $pageResult = $this->createPageResult();
        
        if (!($result = $this->saveInvoiceKey())) {
            /** @todo Add the correct treatment here. */
        }
        
        return $pageResult;
    }
    
    
    /**
     * @return bool
     */
    protected function saveInvoiceKey()
    {
        $order = $this->initOrder();
        
        if (!$order || !$order->getId()) {
            return false;
        }
        
        $invoiceKey = (string) $this->getRequest()->getParam('invoice_key_number');
    
        if (!$invoiceKey) {
            return false;
        }
    
        try {
            /** @var \BitTools\SkyHub\Model\Order $info */
            $info = $order->getExtensionAttributes()->getSkyhubInfo();
            
            if (!$info || !$info->validateInvoiceKey($invoiceKey)) {
                return false;
            }
            
            $info->setInvoiceKey($invoiceKey);
    
            $this->orderRelationRepository->save($info);
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @return bool|\Magento\Sales\Model\Order
     */
    protected function initOrder()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        
        if (!$orderId) {
            return false;
        }
    
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $this->helperContext->registryManager()->register('current_order', $order, true);
            
            return $order;
        } catch (\Exception $e) {
            return false;
        }
    }
}
