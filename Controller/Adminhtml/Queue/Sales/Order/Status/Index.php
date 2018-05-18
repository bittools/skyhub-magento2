<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Sales\Order\Status;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Index extends AbstractController
{
    
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_queues_sales_order_status';
    
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->createPageResult();
    
        $title = $page->getConfig()->getTitle();
        $title->prepend(__('SkyHub'));
        $title->prepend(__('Queues'));
        $title->prepend(__('Sales Order Status Queue'));
        
        return $page;
    }
}
