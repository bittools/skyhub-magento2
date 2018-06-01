<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    
    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        /** @var \DOMXPath $xpath */
        $xpath = new \DOMXPath($source);
    
        /**
         * @var \DOMNodeList $attributesNode
         * @var \DOMNodeList $blacklistNode
         */
        $attributesNode = $xpath->evaluate('/config/skyhub/catalog/product/attributes/attribute');
        $blacklistNode  = $xpath->evaluate('/config/skyhub/catalog/product/attributes/blacklist/attribute');
        
        $attributes = [];
        $blacklist  = [];
        
        /** @var \DOMElement $_blacklistNode */
        foreach ($blacklistNode as $_blacklistNode) {
            $code = $this->_getAttributeValue($_blacklistNode, 'code');
            $blacklist[$code] = $code;
        }
        
        /** @var \DOMElement $attributeNode */
        foreach ($attributesNode as $attributeNode) {
            $code          = $this->_getAttributeValue($attributeNode, 'code');
            $required      = (bool) $this->_getAttributeValue($attributeNode, 'required');
            $castType      = $this->_getAttributeValue($attributeNode, 'cast_type');
            $attributeType = $this->_getAttributeValue($attributeNode, 'type');
            $input         = $this->_getAttributeValue($attributeNode, 'input');
            $enabled       = (bool) $this->_getAttributeValue($attributeNode, 'enabled');
            $editable      = (bool) $this->_getAttributeValue($attributeNode, 'editable');
    
            $attributes[$code]['code']           = $code;
            $attributes[$code]['required']       = $required;
            $attributes[$code]['cast_type']      = $castType;
            $attributes[$code]['attribute_type'] = $attributeType;
            $attributes[$code]['input']          = $input;
            $attributes[$code]['enabled']        = $enabled;
            $attributes[$code]['editable']       = $editable;
            
            /** @var \DOMElement $childNode */
            foreach ($attributeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                
                $nodeName  = $childNode->nodeName;
                $nodeValue = $childNode->nodeValue;
                
                if ($nodeName == 'attribute_install_config') {
                    
                    /** @var \DOMElement $childChildNode */
                    foreach ($childNode->childNodes as $childChildNode) {
                        if ($childChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        
                        $childNodeName  = $childChildNode->nodeName;
                        $childNodeValue = $childChildNode->nodeValue;
    
                        $attributes[$code][$nodeName][$childNodeName] = $childNodeValue;
                    }
                    
                    continue;
                }
    
                $attributes[$code][$nodeName] = $nodeValue;
            }
        }
        
        return [
            'blacklist'  => [
                'catalog_product' => $blacklist
            ],
            'attributes' => [
                'catalog_product' => $attributes
            ]
        ];
    }
    
    
    /**
     * Get attribute value
     *
     * @param \DOMElement $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    protected function _getAttributeValue(\DOMElement $input, $attributeName, $default = null)
    {
        $value = $input->getAttribute($attributeName);
        
        if (!$value) {
            $value = $default;
        }
        
        if ($value) {
            switch ($value) {
                case 'false':
                    $value = false;
                    break;
                case 'true':
                    $value = true;
                    break;
            }
        }
        
        return $value;
    }
}
