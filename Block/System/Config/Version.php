<?php

namespace BitTools\SkyHub\Block\System\Config;

class Version extends AbstractConfig
{

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return array|bool|mixed|null|string
     */
    protected function getCustomElementValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $moduleData = (array) $this->moduleList->getOne('BitTools_SkyHub');
        $version    = $this->arrayExtract($moduleData, 'setup_version');

        return $version;
    }
}
