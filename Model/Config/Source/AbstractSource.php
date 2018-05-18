<?php

namespace BitTools\SkyHub\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

abstract class AbstractSource implements ArrayInterface
{
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }
        
        return $options;
    }
    
    
    /**
     * @return array
     */
    abstract public function toArray();
}
