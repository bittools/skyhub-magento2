<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Mapping extends Action implements ViewInterface
{
    /** @var PageFactory */
    protected $resultPageFactory;


    /**
     * @var Context $context
     * @var PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    /**
     * Start by creating your controller's logic...
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
