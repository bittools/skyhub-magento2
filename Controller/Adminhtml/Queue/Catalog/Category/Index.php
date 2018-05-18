<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Catalog\Category;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Index extends AbstractController
{
    
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_queues_category';
    
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->createPageResult();
    
        $title = $page->getConfig()->getTitle();
        $title->prepend(__('SkyHub'));
        $title->prepend(__('Queue'));
        $title->prepend(__('Catalog Category Queue'));
        
        return $page;
    }
}
