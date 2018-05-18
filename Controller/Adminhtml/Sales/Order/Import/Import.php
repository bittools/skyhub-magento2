<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Import extends AbstractController
{

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectResult */
        $redirectResult = $this->createRedirectResult();
        $redirectResult->setPath('*/*/manual');

        $this->messageManager->addSuccessMessage(__('The process is finished.'));

        return $redirectResult;
    }
}
