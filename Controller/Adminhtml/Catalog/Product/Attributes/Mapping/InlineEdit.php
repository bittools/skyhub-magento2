<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes\Mapping;

use BitTools\SkyHub\Api\ProductAttributeMappingRepositoryInterface;
use BitTools\SkyHub\Controller\AbstractController;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends AbstractController
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_product_attributes_mapping_save';
    
    /** @var JsonFactory */
    protected $resultJsonFactory;
    
    /** @var ProductAttributeMappingRepositoryInterface */
    protected $productAttributeMappingRepository;


    /**
     * @var Context $context
     * @var JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context                                    $context,
        ProductAttributeMappingRepositoryInterface $productAttributeMappingRepository
    )
    {
        parent::__construct($context);
        $this->productAttributeMappingRepository = $productAttributeMappingRepository;
    }
    
    
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $items = $this->getRequest()->getParam('items', null);
        $item  = (array) array_pop($items);
        
        if (empty($item) || !isset($item['attribute_id']) || !isset($item['id'])) {
            throw new \Magento\Framework\Exception\InputException(__('Invalid parameters'));
        }
        
        $mappingId   = $item['id'];
        $attributeId = $item['attribute_id'];
        
        /** @var Mapping $mapping */
        $mapping = $this->productAttributeMappingRepository->get($mappingId);
        $mapping->setData('attribute_id', $attributeId);
        $this->productAttributeMappingRepository->save($mapping);
        
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData(['success' => true]);
        
        return $resultJson;
    }
}
