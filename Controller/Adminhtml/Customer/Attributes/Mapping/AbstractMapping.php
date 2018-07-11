<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Customer\Attributes\Mapping;

use BitTools\SkyHub\Api\CustomerAttributeMappingRepositoryInterface;
use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Backend\App\Action\Context;

abstract class AbstractMapping extends AbstractController
{
    
    /** @var CustomerAttributeMappingRepositoryInterface */
    protected $customerAttributeMappingRepository;


    /**
     * AbstractMapping constructor.
     *
     * @param Context                                     $context
     * @param HelperContext                               $helperContext
     * @param CustomerAttributeMappingRepositoryInterface $customerAttributeMappingRepository
     */
    public function __construct(
        Context                                    $context,
        HelperContext                              $helperContext,
        CustomerAttributeMappingRepositoryInterface $customerAttributeMappingRepository
    )
    {
        parent::__construct($context, $helperContext);
        $this->customerAttributeMappingRepository = $customerAttributeMappingRepository;
    }
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectIndex()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectPage */
        $redirectPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectPage->setPath('*/*/index');
        
        return $redirectPage;
    }
}
