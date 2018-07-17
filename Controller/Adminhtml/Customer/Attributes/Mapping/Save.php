<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Julio Reis <julio.reis@b2wdigital.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Customer\Attributes\Mapping;

use BitTools\SkyHub\Api\CustomerAttributeMappingRepositoryInterface;
use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Backend\App\Action\Context;

class Save extends AbstractMapping
{
    protected $_customerAttributesMapping;
    protected $_customerMappingRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param HelperContext $helperContext
     * @param CustomerAttributeMappingRepositoryInterface $customerAttributeMappingRepository
     * @param \BitTools\SkyHub\Model\Customer\Attributes\Mapping $customerAttributesMapping
     * @param \BitTools\SkyHub\Model\Customer\Attributes\MappingRepository $customerMappingRepository
     */
    public function __construct(
        Context $context,
        HelperContext $helperContext,
        CustomerAttributeMappingRepositoryInterface $customerAttributeMappingRepository,
        \BitTools\SkyHub\Model\Customer\Attributes\Mapping $customerAttributesMapping,
        \BitTools\SkyHub\Model\Customer\Attributes\MappingRepository $customerMappingRepository
    )
    {
        parent::__construct($context, $helperContext, $customerAttributeMappingRepository);

        $this->_customerAttributesMapping = $customerAttributesMapping;
        $this->_customerMappingRepository = $customerMappingRepository;
    }

    public function execute()
    {
        $customerMappingId = $this->getRequest()->getParam('id');
        $customerMapping = $this->_customerMappingRepository->get($customerMappingId);

        $magentoAttributeId = $customerMappingId = $this->getRequest()->getParam('attribute_id');
        if ($magentoAttributeId) {
            $customerMapping->setAttributeId($magentoAttributeId);
            $this->_customerMappingRepository->save($customerMapping);
            $this->messageManager->addNoticeMessage(__('The mapping was saved.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $redirectPage */
        $redirectPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectPage->setPath('*/*/index');
        return $redirectPage;
    }
}