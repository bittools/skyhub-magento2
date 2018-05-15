<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes\Mapping;

use BitTools\SkyHub\Api\ProductAttributeMappingRepositoryInterface;
use Magento\Backend\App\Action\Context;
use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use Magento\Framework\ObjectManagerInterface;

abstract class AbstractMapping extends AbstractController
{
    
    /** @var ProductAttributeMappingRepositoryInterface */
    protected $productAttributeMappingRepository;
    
    
    /**
     * AbstractMapping constructor.
     *
     * @param Context                                    $context
     * @param ProductAttributeMappingRepositoryInterface $productAttributeMappingRepository
     */
    public function __construct(
        Context                                    $context,
        ProductAttributeMappingRepositoryInterface $productAttributeMappingRepository
    )
    {
        parent::__construct($context);
        $this->productAttributeMappingRepository = $productAttributeMappingRepository;
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
