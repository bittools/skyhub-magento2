define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    function main(config, element) {
        var AjaxUrl = config.AjaxUrl;
        var mappingAttributeId = config.mappingAttributeId;
        var elementSelector = 'select[name=attribute_id]';
        var containerIdSelector = '#customer-options-container';

        $('body').on('change', elementSelector, function () {
            if (!$(containerIdSelector).length) {
                $(elementSelector).after('<div id="customer-options-container"></div>');
            }
            event.preventDefault();
            var magentoAttributeId = $(this).val();
            $.ajax({
                showLoader: true,
                url: AjaxUrl,
                data: {
                    magento_attribute_id: magentoAttributeId,
                    mapping_attribute_id: mappingAttributeId
                },
                type: "POST"
            }).done(function (data) {
                $(containerIdSelector).html(data);
                return true;
            });
        });
    };
    return main;
});