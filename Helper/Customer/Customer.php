<?php

namespace BitTools\SkyHub\Helper\Customer;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Helper\AbstractHelper;

class Customer extends AbstractHelper
{
    protected $_eavAttributeRepository;

    public function __construct(
        Context $context,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
    )
    {
        parent::__construct($context);

        $this->_eavAttributeRepository = $eavAttributeRepository;
    }

    public function getAttributeOptions($attributeCode)
    {
        $attribute = $this->_eavAttributeRepository->get(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
        return $attribute->getSource()->getAllOptions(false);
    }
}
