<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\Config\Source\Catalog\Product;

use BitTools\SkyHub\Model\Config\Source\AbstractSource;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaFactory;

class Attributes extends AbstractSource
{
    
    /** @var ProductAttributeRepositoryInterface */
    protected $productAttributeRepository;
    
    /** @var $searchCriteriaFactory */
    protected $searchCriteriaFactory;
    
    
    /**
     * Attributes constructor.
     *
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaFactory               $searchCriteriaFactory
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaFactory $searchCriteriaFactory
    )
    {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }


    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $attributes     = [];
        $searchCriteria = $this->searchCriteriaFactory->create();
        $result         = $this->productAttributeRepository->getList($searchCriteria);
        
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($result->getItems() as $attribute) {
            $attributes[$attribute->getId()] = $attribute->getName();
        }
        
        return $attributes;
    }
}
