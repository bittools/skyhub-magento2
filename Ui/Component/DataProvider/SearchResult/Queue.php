<?php

namespace BitTools\SkyHub\Ui\Component\DataProvider\SearchResult;

use BitTools\SkyHub\Ui\Component\DataProvider\SearchResult;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Queue extends SearchResult
{
    
    /** @var string */
    protected $entityType = null;
    
    
    /**
     * Queue constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger        $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager  $eventManager
     * @param string        $mainTable
     * @param string        $resourceModel
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = \BitTools\SkyHub\Model\ResourceModel\Queue::MAIN_TABLE,
        $resourceModel = \BitTools\SkyHub\Model\ResourceModel\Queue::class
    )
    {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }
    
    
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->entityType) {
            $this->addFieldToFilter('entity_type', $this->entityType);
        }
        
        parent::_beforeLoad();
        
        return $this;
    }
}
