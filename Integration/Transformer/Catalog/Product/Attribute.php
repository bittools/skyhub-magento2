<?php

namespace BitTools\SkyHub\Integration\Transformer\Catalog\Product;

use BitTools\SkyHub\Integration\Transformer\AbstractTransformer;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use SkyHub\Api\EntityInterface\Catalog\Product\Attribute as AttributeInterface;

class Attribute extends AbstractTransformer
{
    
    /**
     * @param EntityAttribute $attribute
     *
     * @return AttributeInterface
     */
    public function convert(EntityAttribute $attribute)
    {
        /** @var AttributeInterface $interface */
        $interface = $this->context->api()->productAttribute()->entityInterface();
        
        try {
            $code  = $attribute->getAttributeCode();
            $label = $attribute->getStoreLabel(Mage::app()->getDefaultStoreView());
            
            $interface->setCode($code)
                ->setLabel($label);
            
            $this->appendAttributeOptions($attribute, $interface);
        } catch (\Exception $e) {
            $this->context->helperContext()->logger()->critical($e);
        }
        
        return $interface;
    }
    
    
    /**
     * @param EntityAttribute    $attribute
     * @param AttributeInterface $interface
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function appendAttributeOptions(EntityAttribute $attribute, AttributeInterface $interface)
    {
        if (!in_array($attribute->getFrontend()->getInputType(), ['select', 'multiselect'])) {
            return $this;
        }
        
        if (!$attribute->getSourceModel()) {
            return $this;
        }
        
        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if (!isset($option['label']) || empty($option['label'])) {
                continue;
            }
            
            $optionLabel = $option['label'];
            
            $interface->addOption($optionLabel);
        }
        
        return $this;
    }
}
