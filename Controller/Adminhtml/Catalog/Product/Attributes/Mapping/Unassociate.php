<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes\Mapping;

use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;

class Unassociate extends AbstractMapping
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_product_attributes_mapping_save';
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $mappingId = $this->getRequest()->getParam('id');
    
        /** @var Mapping $mapping */
        $mapping = $this->productAttributeMappingRepository->get($mappingId);
        $mapping->setData('attribute_id', null);
        
        $this->productAttributeMappingRepository->save($mapping);
        
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectPage */
        $redirectPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
    
        $redirectPage->setPath('*/*/index');
        
        return $redirectPage;
    }
}
