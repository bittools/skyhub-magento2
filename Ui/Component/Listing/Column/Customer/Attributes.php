<?php

namespace BitTools\SkyHub\Ui\Component\Listing\Column\Customer;

use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;
use BitTools\SkyHub\Ui\Component\Listing\Column\AbstractOptions;

class Attributes extends AbstractOptions
{

    /** @var array */
    protected $currentOptions = [];

    /** @var \Magento\Customer\Model\CustomerFactory */
    protected $customerFactory;

    /**
     * AbstractOptions constructor.
     *
     * @param SystemStore $systemStore
     * @param Escaper     $escaper
     */
    public function __construct(SystemStore $systemStore,
                                Escaper $escaper,
                                \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        parent::__construct($systemStore, $escaper);

        $this->customerFactory = $customerFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        return $this->generateCurrentOptions();
    }


    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $customer_attributes = $this->customerFactory->create()->getAttributes();
        $attributesArrays = [];
        foreach ($customer_attributes as $cal => $val) {
            if($val->getAttributeId()) {
                $attributesArrays[] = [
                    'label' => $val->getFrontendLabel() ? $val->getFrontendLabel() : $cal,
                    'value' => $val->getAttributeId()
                ];
            }
        }
        return $attributesArrays;
    }
}
