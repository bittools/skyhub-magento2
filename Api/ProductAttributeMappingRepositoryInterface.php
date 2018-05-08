<?php

namespace BitTools\SkyHub\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductAttributeMappingRepositoryInterface
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
     * @param $mappingId
     *
     * @return mixed
     */
    public function get($mappingId);
    
    
    /**
     * @param Data\ProductAttributeMappingInterface $mapping
     *
     * @return mixed
     */
    public function save(Data\ProductAttributeMappingInterface $mapping);
    
    
    /**
     * @param Data\ProductAttributeMappingInterface $mapping
     *
     * @return mixed
     */
    public function delete(Data\ProductAttributeMappingInterface $mapping);
    
    
    /**
     * @param int $mappingId
     *
     * @return mixed
     */
    public function deleteById($mappingId);
}
