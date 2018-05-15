<?php

namespace BitTools\SkyHub\Model\Catalog\Product\Attributes;

use BitTools\SkyHub\Api\Data;
use BitTools\SkyHub\Api\ProductAttributeMappingRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class MappingRepository implements ProductAttributeMappingRepositoryInterface
{
    
    /** @var MappingFactory */
    protected $mappingFactory;
    
    
    public function __construct(MappingFactory $mappingFactory)
    {
        $this->mappingFactory = $mappingFactory;
    }
    
    
    /**
     * Retrieve all attributes for entity type
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }
    
    
    /**
     * @param $mappingId
     *
     * @return Mapping|mixed
     *
     * @throws NoSuchEntityException
     */
    public function get($mappingId)
    {
        /** @var Mapping $mapping */
        $mapping = $this->mappingFactory->create();
        $mapping->load($mappingId);
    
        if (!$mapping->getId()) {
            throw new NoSuchEntityException(__('Attribute Mapping with id "%1" does not exist.', $mappingId));
        }
        
        return $mapping;
    }
    
    
    /**
     * @param Data\ProductAttributeMappingInterface $mapping
     *
     * @return mixed
     */
    public function save(Data\ProductAttributeMappingInterface $mapping)
    {
        $mapping->save();
        return $this;
    }
    
    
    /**
     * @param Data\ProductAttributeMappingInterface $mapping
     *
     * @return mixed
     */
    public function delete(Data\ProductAttributeMappingInterface $mapping)
    {
        $mapping->delete();
        return $this;
    }
    
    
    /**
     * @param int $mappingId
     *
     * @return mixed
     */
    public function deleteById($mappingId)
    {
        /** @var Mapping $mapping */
        $mapping = $this->mappingFactory->create();
        $mapping->setId($mappingId);
        $this->delete($mapping);
        
        return $this;
    }
}
