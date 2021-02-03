<?php
/**
 * BitTools Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  BitTools
 * @package   BitTools_SkyHub
 *
 * @copyright Copyright (c) 2018 B2W Digital - BitTools Platform.
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
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
