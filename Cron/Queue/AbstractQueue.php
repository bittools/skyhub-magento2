<?php

namespace BitTools\SkyHub\Cron\Queue;

use BitTools\SkyHub\Cron\AbstractCron;

abstract class AbstractQueue extends AbstractCron implements QueueInterface
{
    
    /** @var \BitTools\SkyHub\Model\ResourceModel\QueueFactory */
    protected $queueResourceFactory;
    
    
    public function __construct(
        \BitTools\SkyHub\Cron\Context $context,
        \BitTools\SkyHub\StoreConfig\Context $configContext,
        \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\State $state,
        \BitTools\SkyHub\Model\ResourceModel\QueueFactory $queueResourceFactory
    ) {
        parent::__construct($context, $configContext, $storeIterator, $groupRepository, $state);
        $this->queueResourceFactory = $queueResourceFactory;
    }
    
    
    /**
     * @return \BitTools\SkyHub\Model\ResourceModel\Queue
     */
    public function getQueueResource()
    {
        return $this->queueResourceFactory->create();
    }
}
