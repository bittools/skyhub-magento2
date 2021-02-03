<?php
/**
 * BitTools Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  BitTools
 * @package   BitTools_SkyHub
 *
 * @copyright Copyright (c) 2018 B2W Digital - BitTools Platform.
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
 */

namespace BitTools\SkyHub\Helper\Customer;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Helper\AbstractHelper;

class Customer extends AbstractHelper
{
    /** @var \Magento\Eav\Api\AttributeRepositoryInterface */
    protected $_eavAttributeRepository;

    /**
     * Customer constructor.
     * @param Context $context
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     */
    public function __construct(
        Context $context,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
    )
    {
        parent::__construct($context);

        $this->_eavAttributeRepository = $eavAttributeRepository;
    }

    /**
     * @param $attributeCode
     * @return mixed
     */
    public function getAttributeOptions($attributeCode)
    {
        $attribute = $this->_eavAttributeRepository->get(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
        return $attribute->getSource()->getAllOptions(false);
    }
}
