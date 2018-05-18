<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\Import\Manual;

class ImportButton extends GenericButton
{

    /**
     * @{inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Import Order(s)'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 100,
        ];
    }
}
