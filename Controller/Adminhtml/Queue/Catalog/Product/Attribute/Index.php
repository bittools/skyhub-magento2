<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Catalog\Product\Attribute;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Index extends AbstractController
{
    
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_queues_catalog_product_attribute';
    
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->createPageResult();
    
        $page->setActiveMenu('BitTools_SkyHub::queue_catalog_product_attribute');
        
        $title = $page->getConfig()->getTitle();
        $title->prepend(__('SkyHub'));
        $title->prepend(__('Queue'));
        $title->prepend(__('Catalog Product Attributes Queue'));
        
        return $page;
    }
}
