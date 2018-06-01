<?php

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\Import\Manual;

use BitTools\SkyHub\Block\Widget\Button\GenericButton;

class SaveButton extends GenericButton
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
                'mage-init' => ['button' => [
                    'event' => 'submit'
                ]],
                'form-role' => 'submit',
            ],
            'sort_order' => 100,
        ];
    }
}
