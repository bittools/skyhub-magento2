<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue;

use Magento\Framework\App\ResponseInterface;

class Delete extends AbstractQueue
{
    
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $queueId = $this->getRequest()->getParam('id');
        
        if (!empty($queueId)) {
            $this->queueRepository->deleteById($queueId);
        }
        
        return $this->redirectToRefererUrl();
    }
}
