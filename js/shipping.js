var woo_invoices_shipping = (function() {

    var post_id = 0;
    var sender = {};
    var receiver = {};
    var properties = {};

    function set_post_id(_post_id) {
        post_id = _post_id;
    }

    function set_sender(_sender) {
        sender = _sender;
    }

    function get_receiver() {
        return {
            'company': jQuery('#woo_invoices_shipping_labels_address_company').val(),
            'first_name': jQuery('#woo_invoices_shipping_labels_address_first_name').val(),
            'last_name': jQuery('#woo_invoices_shipping_labels_address_last_name').val(),
            'street': jQuery('#woo_invoices_shipping_labels_address_street').val(),
            'street_no': jQuery('#woo_invoices_shipping_labels_address_street_no').val(),
            'care_of': jQuery('#woo_invoices_shipping_labels_care_of').val(),
            'zip_code': jQuery('#woo_invoices_shipping_labels_address_zip_code').val(),
            'city': jQuery('#woo_invoices_shipping_labels_address_city').val(),
            'state': jQuery('#woo_invoices_shipping_labels_address_state').val(),
            'country': jQuery('#woo_invoices_shipping_labels_address_country').val()
        };
    }

    function get_properties() {
        return {
            'carrier': jQuery('#woo_invoices_shipping_labels_properties_carrier').val(),
            'service': jQuery('#woo_invoices_shipping_labels_properties_service').val(),
            'package_width': jQuery('#woo_invoices_shipping_labels_properties_package_width').val(),
            'package_length': jQuery('#woo_invoices_shipping_labels_properties_package_length').val(),
            'package_height': jQuery('#woo_invoices_shipping_labels_properties_package_height').val(),
            'package_weight': jQuery('#woo_invoices_shipping_labels_properties_package_weight').val(),
            'declared_value': {
                'amount': jQuery('#woo_invoices_shipping_labels_properties_declared_value_amount').val(),
                'currency': jQuery('#woo_invoices_shipping_labels_properties_declared_value_currency').val()
            },
            'package_type': jQuery('#woo_invoices_shipping_labels_properties_package_type').val(),
            'package_bulk': jQuery('#woo_invoices_shipping_labels_properties_package_bulk').is(':checked'),
            'reference_number': jQuery('#woo_invoices_shipping_labels_properties_reference_number').val(),
            'notification_email': jQuery('#woo_invoices_shipping_labels_properties_notification_email').val(),
            'new_tab': jQuery('#woo_invoices_shipping_labels_properties_new_tab').val()
        };
    }

    function shipping_data_save() {

        receiver = get_receiver();
        properties = get_properties();

        var _data = {
            'action': 'woo_invoices_api',
            'service': 'shipping_data.save',
            'post_id': post_id,
            'receiver': receiver,
            'properties': properties
        };

        jQuery('#woo_invoices_shipping_label_save_button_success').hide();
        jQuery('#woo_invoices_shipping_label_save_button_failure').hide();
        jQuery('#woo_invoices_shipping_label_save_button_wait').show();
        jQuery('#woo_invoices_shipping_label_save_button').prop('disabled', true);

        jQuery.post(ajaxurl, _data, function(_response) {

            jQuery('#woo_invoices_shipping_label_save_button_wait').hide();

            if (typeof(_response.saved) !== 'undefined') {
                if (_response.saved === true) {
                    jQuery('#woo_invoices_shipping_label_save_button_success').show();
                }
                else {
                    jQuery('#woo_invoices_shipping_label_save_button_failure').show();
                }
            }
            else {
                jQuery('#woo_invoices_shipping_label_save_button_failure').show();
            }

            jQuery('#woo_invoices_shipping_label_save_button').prop('disabled', false);
        });
    }

    function label_create() {

        receiver = get_receiver();
        properties = get_properties();

        var _data = {
            'action': 'woo_invoices_api',
            'service': 'shipping.create',
            'post_id': post_id,
            'sender': sender,
            'receiver': receiver,
            'properties': properties
        };

        jQuery('#woo_invoices_shipping_label_create_button_success').hide();
        jQuery('#woo_invoices_shipping_label_create_button_failure').hide();
        jQuery('#woo_invoices_shipping_label_create_button_wait').show();
        jQuery('#woo_invoices_shipping_label_create_button').prop('disabled', true);

        jQuery.post(ajaxurl, _data, function(_response) {

            jQuery('#woo_invoices_shipping_label_create_button_wait').hide();

            if (typeof(_response.label) !== 'undefined') {
                if (typeof(_response.response) !== 'undefined') {
                    if (typeof(_response.response.carrier_tracking_no) !== 'undefined') {
                        jQuery('#_order_trackno').val(_response.response.carrier_tracking_no);
                    }
                }
                if (typeof(_response.request) !== 'undefined') {
                    if (typeof(_response.request.properties) !== 'undefined') {
                        if (typeof(_response.request.properties.carrier) !== 'undefined') {
                            var carrier = _response.request.properties.carrier;
                            if(carrier === 'DHL') {
                                jQuery("#_order_trackurl option[value='DHLGER']").attr('selected',true);
                            } else if(carrier === 'DPD') {
                                jQuery("#_order_trackurl option[value='DPDPARCEL']").attr('selected',true);
                            } else if(carrier === 'Hermes') {
                                jQuery("#_order_trackurl option[value='MYHERMESDE']").attr('selected',true);
                            } else if(carrier === 'UPS') {
                                jQuery("#_order_trackurl option[value='UPSGER']").attr('selected',true);
                            }
                        }
                    }
                }
                jQuery('#woo_invoices_shipping_label_create_button_success').show();
                jQuery('#woo_invoices_shipping_labels_label_list').append(_response.label);

                if(properties.new_tab === '1') {
                    var newTab = window.open(_response.response.label_url, '_blank');
                    if (newTab) {
                        newTab.focus();
                    } else {
                        alert('Bitte lass Pop-ups für diese Webseite zu.');
                    }
                }
            }
            else {
                if(typeof(_response.response) !== 'undefined') {
                    var temp = _response.response.message.split('{"errors":["');
                    var error = temp[1];
                } else {
                    var error = _response.message;
                }
                jQuery('#woo_invoices_shipping_label_create_button_failure').show();
                alert('Fehler: '+error);
            }

            jQuery('#woo_invoices_shipping_label_create_button').prop('disabled', false);
        });
    }

    function shipping_cost_request() {

        receiver = get_receiver();
        properties = get_properties();

        var _data = {
            'action': 'woo_invoices_api',
            'service': 'shipping_quote.create',
            'post_id': post_id,
            'sender': sender,
            'receiver': receiver,
            'properties': properties
        };

        jQuery('#woo_invoices_shipping_shipping_cost_request_button_success').hide();
        jQuery('#woo_invoices_shipping_shipping_cost_request_button_failure').hide();
        jQuery('#woo_invoices_shipping_shipping_cost_request_button_wait').show();
        jQuery('#woo_invoices_shipping_shipping_cost_request_button').prop('disabled', true);

        jQuery.post(ajaxurl, _data, function(_response) {

            jQuery('#woo_invoices_shipping_shipping_cost_request_button_wait').hide();

            if (typeof(_response.response.shipment_quote) !== 'undefined') {
                if (typeof(_response.response.shipment_quote.price) !== 'undefined') {
                    jQuery('#woo_invoices_shipping_shipping_cost_request_button_success').show();
                    alert('EUR '+_response.response.shipment_quote.price);
                }
                else {
                    jQuery('#woo_invoices_shipping_shipping_cost_request_button_failure').show();
                }
            }
            else {
                if(typeof(_response.response) !== 'undefined') {
                    var temp = _response.response.message.split('{"errors":["');
                    var error = temp[1];
                } else {
                    var error = _response.message;
                }
                jQuery('#woo_invoices_shipping_shipping_cost_request_button_failure').show();
                alert(woorechnung_language.failure+': '+error);
            }

            jQuery('#woo_invoices_shipping_shipping_cost_request_button').prop('disabled', false);
        });
    }

    function delete_label(_label_id) {

        if (confirm(woorechnung_language.delete_request)) {
            var _data = {
                'action': 'woo_invoices_api',
                'service': 'shipping.delete',
                'post_id': post_id,
                'label_id': _label_id
            };

            jQuery.post(ajaxurl, _data, function(_response) {
                if (typeof(_response.response.deleted) !== 'undefined') {
                    if (_response.response.deleted === true) {
                        jQuery('#woo_invoices_shipping_label_'+_response.request.label_id).remove();
                    }
                }
                else {
                    console.log(_data);
                    console.log(_response);
                    alert('Delete failed!');
                }
            });
        }
    }

    function show_shipping_presets() {
        jQuery('#woo_invoices_shipping_presets').show();
    }

    function use_shipping_preset($_options) {
        jQuery('#woo_invoices_shipping_labels_properties_carrier').val($_options.carrier);
        jQuery('#woo_invoices_shipping_labels_properties_service').val($_options.service);
        jQuery('#woo_invoices_shipping_labels_properties_package_type').val($_options.package_type);
        jQuery('#woo_invoices_shipping_labels_properties_package_width').val($_options.package_width);
        jQuery('#woo_invoices_shipping_labels_properties_package_length').val($_options.package_length);
        jQuery('#woo_invoices_shipping_labels_properties_package_height').val($_options.package_height);
        jQuery('#woo_invoices_shipping_labels_properties_package_weight').val($_options.package_weight);
        jQuery('#woo_invoices_shipping_presets').hide();
    }

    return {
        'set_post_id': set_post_id,
        'set_sender': set_sender,
        'shipping_data_save': shipping_data_save,
        'label_create': label_create,
        'shipping_cost_request': shipping_cost_request,
        'delete_label': delete_label,
        'show_shipping_presets': show_shipping_presets,
        'use_shipping_preset': use_shipping_preset
    };
})();

jQuery(document).ready(function() {
    jQuery('#woo_invoices_shipping_labels_label_list').sortable({});
});
