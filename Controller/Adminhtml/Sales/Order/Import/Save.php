<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use Magento\Sales\Api\Data\OrderInterface;

class Save extends AbstractImport
{
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectResult */
        $redirectResult = $this->createRedirectResult();
        $redirectResult->setPath('*/*/manual');
        
        $storeId    = $this->getRequest()->getParam('store_id');
        $references = (array) explode(PHP_EOL, $this->getRequest()->getParam('reference'));
    
        if (empty($references)) {
            $this->messageManager->addWarningMessage(__('No order reference was informed.'));
            return $redirectResult;
        }
    
        $references = array_unique(array_map(function (&$value) {
            return trim($value);
        }, $references));
        
        /** @var string $reference */
        foreach ($references as $reference) {
            $this->importOrder($reference, $storeId);
        }
        
        $this->messageManager->addNoticeMessage(__('The process is finished.'));

        return $redirectResult;
    }
    
    
    /**
     * @param string   $referenceCode
     * @param null|int $storeId
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function importOrder($referenceCode, $storeId = null)
    {
        $this->helperContext->storeManager()->setCurrentStore($storeId);
        
        /** @var array|bool $orderData */
        $orderData = $this->getOrderIntegrator()->order($referenceCode);
        
        if (!$orderData) {
            $this->messageManager->addWarningMessage(
                $this->message->getNonExistentOrderMessage($referenceCode)
            );
            
            return false;
        }
        
        /** @var bool|OrderInterface $order */
        $order = $this->getOrderProcessor()->createOrder($orderData);
        
        if (!$order) {
            $this->messageManager->addWarningMessage($this->message->getNotCreatedOrderMessage($referenceCode));
            return false;
        }
    
        $this->messageManager->addSuccessMessage($this->message->getOrderCreationMessage($order, $referenceCode));
        return true;
    }
}
