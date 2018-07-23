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
        $productAttributesNode = $xpath->evaluate('/config/skyhub/catalog/product/attributes/attribute');
        $productBlacklistNode = $xpath->evaluate('/config/skyhub/catalog/product/attributes/blacklist/attribute');
        $customerAttributesNode = $xpath->evaluate('/config/skyhub/customer/attributes/attribute');
        $customerBlacklistNode = $xpath->evaluate('/config/skyhub/customer/attributes/blacklist/attribute');

        $productAttributes = [];
        $productBlacklist = [];

        $customerAttributes = [];
        $customerBlacklist = [];

        /** @var \DOMElement $_blacklistNode */
        foreach ($productBlacklistNode as $_blacklistNode) {
            $code = $this->_getAttributeValue($_blacklistNode, 'code');
            $productBlacklist[$code] = $code;
        }

        /** @var \DOMElement $_blacklistNode */
        foreach ($customerBlacklistNode as $_blacklistNode) {
            $code = $this->_getAttributeValue($_blacklistNode, 'code');
            $customerBlacklist[$code] = $code;
        }

        /** @var \DOMElement $productAttributesNode */
        foreach ($productAttributesNode as $attributeNode) {
            $code = $this->_getAttributeValue($attributeNode, 'code');
            $required = (bool)$this->_getAttributeValue($attributeNode, 'required');
            $castType = $this->_getAttributeValue($attributeNode, 'cast_type');
            $attributeType = $this->_getAttributeValue($attributeNode, 'type');
            $input = $this->_getAttributeValue($attributeNode, 'input');
            $enabled = (bool)$this->_getAttributeValue($attributeNode, 'enabled');
            $editable = (bool)$this->_getAttributeValue($attributeNode, 'editable');

            $productAttributes[$code]['code'] = $code;
            $productAttributes[$code]['required'] = $required;
            $productAttributes[$code]['cast_type'] = $castType;
            $productAttributes[$code]['attribute_type'] = $attributeType;
            $productAttributes[$code]['input'] = $input;
            $productAttributes[$code]['enabled'] = $enabled;
            $productAttributes[$code]['editable'] = $editable;

            /** @var \DOMElement $childNode */
            foreach ($attributeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $nodeName = $childNode->nodeName;
                $nodeValue = $childNode->nodeValue;

                if ($nodeName == 'attribute_install_config') {

                    /** @var \DOMElement $childChildNode */
                    foreach ($childNode->childNodes as $childChildNode) {
                        if ($childChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }

                        $childNodeName = $childChildNode->nodeName;
                        $childNodeValue = $childChildNode->nodeValue;

                        $productAttributes[$code][$nodeName][$childNodeName] = $childNodeValue;
                    }

                    continue;
                }

                $productAttributes[$code][$nodeName] = $nodeValue;
            }
        }

        /** @var \DOMElement $customerAttributesNode */
        foreach ($customerAttributesNode as $attributeNode) {
            $code = $this->_getAttributeValue($attributeNode, 'code');
            $required = (bool)$this->_getAttributeValue($attributeNode, 'required');
            $castType = $this->_getAttributeValue($attributeNode, 'cast_type');
            $attributeType = $this->_getAttributeValue($attributeNode, 'type');
            $input = $this->_getAttributeValue($attributeNode, 'input');
            $enabled = (bool)$this->_getAttributeValue($attributeNode, 'enabled');
            $editable = (bool)$this->_getAttributeValue($attributeNode, 'editable');

            $customerAttributes[$code]['code'] = $code;
            $customerAttributes[$code]['required'] = $required;
            $customerAttributes[$code]['cast_type'] = $castType;
            $customerAttributes[$code]['attribute_type'] = $attributeType;
            $customerAttributes[$code]['input'] = $input;
            $customerAttributes[$code]['enabled'] = $enabled;
            $customerAttributes[$code]['editable'] = $editable;

            /** @var \DOMElement $childNode */
            foreach ($attributeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $nodeName = $childNode->nodeName;
                $nodeValue = $childNode->nodeValue;

                if ($nodeName == 'attribute_install_config') {

                    /** @var \DOMElement $childChildNode */
                    foreach ($childNode->childNodes as $childChildNode) {
                        if ($childChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }

                        $childNodeName = $childChildNode->nodeName;
                        $childNodeValue = $childChildNode->nodeValue;

                        $customerAttributes[$code][$nodeName][$childNodeName] = $childNodeValue;
                    }

                    continue;
                }

                if ($nodeName == 'options') {
                    $customerAttributes[$code][$nodeName] = $this->getXmlValuesAsArrayRecursively($childNode);
                    continue;
                }

                $customerAttributes[$code][$nodeName] = $nodeValue;
            }
        }

        return [
            'blacklist' => [
                'catalog_product' => $productBlacklist,
                'customer' => $customerBlacklist
            ],
            'attributes' => [
                'catalog_product' => $productAttributes,
                'customer' => $customerAttributes
            ]
        ];
    }

    protected function getXmlValuesAsArrayRecursively($node)
    {
        $return = [];
        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $childValue = $this->getXmlValuesAsArrayRecursively($childNode);
                $return[$childNode->nodeName] = $childValue ? $childValue : $childNode->nodeValue;
            }
        } else {
            $return[$node->nodeName] = $node->nodeValue;
        }
        return $return;
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
