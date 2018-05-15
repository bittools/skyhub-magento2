<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes\Mapping;

class Index extends AbstractMapping
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_product_attributes_mapping';


    /**
     * Start by creating your controller's logic...
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        
        $title = $resultPage->getConfig()->getTitle();
        $title->prepend(__('SkyHub'));
        $title->prepend(__('Attributes Mapping'));
        
        return $resultPage;
    }
}
