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
     * SearchResult constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param null|string $resourceModel
     * @param null|string $identifierName
     * @param null|string $connectionName
     * @param string|null $entityType
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null,
        $entityType = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
        
        $this->entityType = $entityType;
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
