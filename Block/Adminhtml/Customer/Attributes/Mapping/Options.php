<?php

namespace BitTools\SkyHub\Block\Adminhtml\Customer\Attributes\Mapping;

class Options extends \Magento\Backend\Block\Template
{
    public function toHtml()
    {
        $url = $this->_urlBuilder->getUrl('*/*/optionsrenderer');
        $mappingAttributeId = $this->getRequest()->getParam('id');

        return '<script type="text/x-magento-init">
        {
            "*": {
                "BitTools_SkyHub/js/customeroptionsreloader": {
                    "AjaxUrl": "' . $url . '",
                    "mappingAttributeId":"' . $mappingAttributeId . '"
                }
            }
        }
</script>';
    }
}
