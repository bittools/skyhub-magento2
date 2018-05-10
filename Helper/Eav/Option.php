<?php

namespace BitTools\SkyHub\Helper\Eav;

use BitTools\SkyHub\Helper\AbstractHelper;
use BitTools\SkyHub\Helper\Context;

class Option extends AbstractHelper
{
    
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }
    
    
    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param int                                 $optionId
     * @param null|\Magento\Store\Model\Store     $store
     *
     * @return mixed|null
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function extractAttributeOptionValue($attribute, $optionId, $store = null)
    {
        return $this->getEavAttributeOptionResource()->getAttributeOptionText($attribute, $optionId, $store);
    }
    
    
    /**
     * @return \BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute\Option
     */
    public function getEavAttributeOptionResource()
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute\Option $resource */
        $resource = $this->context
            ->objectManager()
            ->create(\BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute\Option::class);
        
        return $resource;
    }
}
