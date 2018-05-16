<?php

namespace BitTools\SkyHub\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractController extends Action
{
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    protected function createPageResult()
    {
        /** @var \Magento\Framework\View\Result\Page $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        return $result;
    }
}
