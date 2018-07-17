<?php

namespace BitTools\SkyHub\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerAttributeMappingOptionsRepositoryInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);


    /**
     * @param $mappingId
     *
     * @return mixed
     */
    public function get($mappingId);


    /**
     * @param Data\CustomerAttributeMappingOptionsInterface $mapping
     *
     * @return mixed
     */
    public function save(Data\CustomerAttributeMappingOptionsInterface $mapping);


    /**
     * @param Data\CustomerAttributeMappingOptionsInterface $mapping
     *
     * @return mixed
     */
    public function delete(Data\CustomerAttributeMappingOptionsInterface $mapping);


    /**
     * @param int $mappingId
     *
     * @return mixed
     */
    public function deleteById($mappingId);
}
