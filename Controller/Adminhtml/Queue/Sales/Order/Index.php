<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Sales\Order;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Index extends AbstractController
{
    
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_queues_sales_order';
    
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->createPageResult();
    
        $page->setActiveMenu('BitTools_SkyHub::queue_sales_order');
        
        $page->getConfig()
            ->getTitle()
            ->append(__('Sales Order'));

        return $page;
    }
}
