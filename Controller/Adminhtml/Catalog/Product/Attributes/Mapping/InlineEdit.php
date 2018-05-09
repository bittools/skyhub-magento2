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
use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;
use Magento\Backend\App\Action\Context;
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
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\ResultInterface
     *
     * @throws \Magento\Framework\Exception\InputException
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
        
        $messages = [
            __('Yhe attribute was successfully saved.')
        ];
        
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData([
            'messages' => $messages,
            'error'    => false
        ]);
    }
}
