<?php

namespace BitTools\SkyHub\Block\System\Config;

use BitTools\SkyHub\Functions;
use Magento\Config\Block\System\Config\Form\Field;

class Version extends Field
{

    use Functions;


    /** @var \Magento\Framework\Module\ModuleListInterface */
    protected $moduleList;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->moduleList = $moduleList;
    }


    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()
            ->unsCanRestoreToDefault()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        $moduleData = (array) $this->moduleList->getOne('BitTools_SkyHub');
        $version    = $this->arrayExtract($moduleData, 'setup_version');

        if ($version) {
            $element->setData('value', $version);
        }

        return parent::render($element);
    }
}
