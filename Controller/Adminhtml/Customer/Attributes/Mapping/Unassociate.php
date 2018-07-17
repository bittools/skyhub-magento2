<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Customer\Attributes\Mapping;

use BitTools\SkyHub\Model\Customer\Attributes\Mapping;

class Unassociate extends AbstractMapping
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_customer_attributes_mapping_unassociate';
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $mappingId = $this->getRequest()->getParam('id');
    
        /** @var Mapping $mapping */
        $mapping = $this->customerAttributeMappingRepository->get($mappingId);
        $mapping->setData('attribute_id', null);
        
        $this->customerAttributeMappingRepository->save($mapping);
        
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectPage */
        $redirectPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
    
        $redirectPage->setPath('*/*/index');
        
        return $redirectPage;
    }
}
