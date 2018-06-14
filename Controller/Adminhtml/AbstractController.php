<?php

namespace BitTools\SkyHub\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context as BackendContext;
use BitTools\SkyHub\Helper\Context as HelperContext;

abstract class AbstractController extends Action
{
    
    /** @var HelperContext */
    protected $helperContext;


    /**
     * AbstractController constructor.
     *
     * @param BackendContext     $context
     * @param HelperContext      $helperContext
     */
    public function __construct(BackendContext $context, HelperContext $helperContext)
    {
        parent::__construct($context);
        
        $this->helperContext = $helperContext;
    }
    
    
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createPageResult()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $result;
    }
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function createRedirectResult()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result;
    }
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function createJsonResult()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $result;
    }
    
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirectToRefererUrl()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->createRedirectResult();
        $result->setRefererUrl();
        
        return $result;
    }
    
    
    /**
     * @param null $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        return $this->helperContext->storeManager()->getStore($storeId);
    }
}
