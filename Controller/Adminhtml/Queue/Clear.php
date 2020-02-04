<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Queue;

use Magento\Framework\App\ResponseInterface;

class Clear extends AbstractQueue
{
    
    /**
     * Clear the current queue to a specified entity type
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $entityType = $this->getRequest()->getParam('entity_type');
        
        if (!empty($entityType)) {
            $this->queueRepository->deleteByEntityType($entityType);
        }
        
        return $this->redirectToRefererUrl();
    }
}
