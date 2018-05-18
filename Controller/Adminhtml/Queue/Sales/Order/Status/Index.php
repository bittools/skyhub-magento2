<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Sales\Order\Status;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Index extends AbstractController
{
    
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_queues_sales_order_status';
    
    
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->createPageResult();
    
        $page->setActiveMenu('BitTools_SkyHub::queue_sales_order_status');
        
        $page->getConfig()
            ->getTitle()
            ->prepend(__('Sales Order Status'));

        return $page;
    }
}
