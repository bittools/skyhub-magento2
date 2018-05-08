<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\Config\Source\Catalog\Product;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaFactory;

class Attributes implements ArrayInterface
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
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ((array) $this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
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
