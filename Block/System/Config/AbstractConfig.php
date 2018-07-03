<?php
/**
 * Created by PhpStorm.
 * User: tiagosampaio
 * Date: 08/06/18
 * Time: 15:43
 */

namespace BitTools\SkyHub\Block\System\Config;

use BitTools\SkyHub\Functions;

abstract class AbstractConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    use Functions;


    /** @var \Magento\Framework\Module\ModuleListInterface */
    protected $moduleList;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->moduleList = $moduleList;
    }


    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return $this
     */
    protected function initRenderUnset(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()
            ->unsCanRestoreToDefault()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return $this;
    }


    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->initRenderUnset($element);

        if ($value = $this->getCustomElementValue($element)) {
            $element->setData('value', $value);
        }

        return parent::render($element);
    }


    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return mixed
     */
    protected function getCustomElementValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return null;
    }
}
