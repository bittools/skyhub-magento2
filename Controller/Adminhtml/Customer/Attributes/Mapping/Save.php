<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Julio Reis <julio.reis@b2wdigital.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Customer\Attributes\Mapping;

class Save extends AbstractMapping
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();

        /**
         * @todo code for save the mapping
         */

        /** @var \Magento\Framework\Controller\Result\Redirect $redirectPage */
        $redirectPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectPage->setPath('*/*/edit');
    }
}