<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;

class Manual extends AbstractController
{

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->createPageResult();

        $this->_setActiveMenu('BitTools_SkyHub::manual_import');

        $pageResult->getConfig()
            ->getTitle()
            ->prepend(__('Manual Import'));

        return $pageResult;
    }
}
