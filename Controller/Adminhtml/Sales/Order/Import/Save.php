<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

class Save extends AbstractImport
{

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
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
        
        $this->messageManager->addSuccessMessage(__('The process is finished.'));

        return $redirectResult;
    }
    
    
    protected function importOrder($referenceCode, $storeId = null)
    {
        $this->helperContext->storeManager()->setCurrentStore($storeId);
        
        /** @var \BitTools\SkyHub\Integration\Integrator\Sales\Order $integrator */
        $integrator = $this->getOrderIntegrator();
        
        $order = $integrator->order($referenceCode);
        
        if (!$order) {
            $this->messageManager
                ->addWarningMessage(__('The order reference "%1" does not exist in Skyhub.', $referenceCode));
            
            return false;
        }
    
        $this->messageManager
            ->addSuccessMessage(__('The order reference "%1" was successfully imported.', $referenceCode));
        
        return true;
    }
}
