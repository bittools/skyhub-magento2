<?php

namespace BitTools\SkyHub\Api;

use BitTools\SkyHub\Model\Queue;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QueueRepositoryInterface
{
    
    /**
     * Retrieve all attributes for entity type
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
    
    
    /**
     * @param int $queueId
     *
     * @return Queue
     */
    public function get($queueId);
    
    
    /**
     * @param Data\QueueInterface $queue
     *
     * @return mixed
     */
    public function save(Data\QueueInterface $queue);
    
    
    /**
     * @param Data\QueueInterface $queue
     *
     * @return mixed
     */
    public function delete(Data\QueueInterface $queue);
    
    
    /**
     * @param int $queueId
     *
     * @return mixed
     */
    public function deleteById($queueId);
    
    
    /**
     * @param array $data
     *
     * @return Data\QueueInterface
     */
    public function create($data = []);
}
