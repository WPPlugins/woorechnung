<?php

/* ******************************************** */
/*   Copyright: ZWEISCHNEIDER DIGITALSCHMIEDE   */
/*         http://www.zweischneider.de          */
/* ******************************************** */

namespace WooInvoices;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOO_INVOICES_API_URL', 'https://woorechnung.com/api/');

if (!class_exists('\WooInvoices\Api')) {
    class Api {

        private $settings = null;
        private $invoice_settings = null;
        private $requestLog = array();

        public function __construct() {
            $this->load_settings();
        }

        public function getRequestLog() {
            return $this->requestLog;
        }

        private function load_settings() {
            $this->settings = get_option(\WooInvoicesMain::$settings_page_name);
            $this->invoice_settings = get_option(\WooInvoicesMain::$settings_page_name.'_invoice');
        }

        private function check_settings() {
            if (empty($this->settings['woo_invoices_api_key'])) {return false;}
            return true;
        }

        private function request($_service, $_data, $_type = 'post') {

            $_request_data = array(
                'api_key' => trim($this->settings['woo_invoices_api_key']),
                'service' => $_service,
                'data' => $_data
            );

            $_post_data = json_encode($_request_data);
            if($_post_data === false) {
                $_post_data = json_encode(unserialize(str_replace(array('NAN;','INF;'),'0;',serialize($_request_data))));
            }

            $_curl = curl_init();

            curl_setopt($_curl, CURLOPT_URL, WOO_INVOICES_API_URL.$_service);
            switch(strtolower($_type)) {
                case 'post':
                    curl_setopt($_curl, CURLOPT_POST, 1);
                    break;
                case 'put':
                    curl_setopt($_curl, CURLOPT_PUT, 1);
                    break;
            }
            curl_setopt($_curl, CURLOPT_POSTFIELDS, array('post_data' => $_post_data));
            curl_setopt($_curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($_curl, CURLOPT_VERBOSE, 1);
            curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($_curl, CURLOPT_TIMEOUT, 30);

            $_responseJson = curl_exec($_curl);
            $this->requestLog[] = $_responseJson;
            $_response = json_decode($_responseJson, true);

            curl_close($_curl);

            if(!isset($_response['status'])) {
                $_response['status'] = null;
            }
            if($_response['status'] == 'error' && !empty($_response['message'])) {
                $savedError = get_option('woo_invoices_plugin_error');
                if(empty($savedError)) {
                    add_option('woo_invoices_plugin_error', array('time' => time(), 'msg' => $_response['message']));
                } else {
                    update_option('woo_invoices_plugin_error', array('time' => time(), 'msg' => $_response['message']));
                }
            }

            return $_response;
        }

        public function api_test() {
            if (!$this->check_settings()) {return false;}
            $_response = $this->request('api.test', array());

            if (!empty($_response)) {
                return $_response;
            }

            return false;
        }

        // CUSTOMER
        public function get_customer_data_from_order($_order) {
            $_post_meta = get_post_meta($_order->id);

            $billing_title = null;
            $shipping_title = null;
            if(!empty($_post_meta['_billing_title'][0])) {
                if($_post_meta['_billing_title'][0] == 1) {
                    $billing_title = 'mr';
                } else {
                    $billing_title = 'mrs';
                }
            }
            if(!empty($_post_meta['_shipping_title'][0])) {
                if($_post_meta['_shipping_title'][0] == 1) {
                    $shipping_title = 'mr';
                } else {
                    $shipping_title = 'mrs';
                }
            }

            $vat_number = null;
            if(!empty($_post_meta['_vat_number'])) {
                $vat_number = $_post_meta['_vat_number'][0];
            } else if(!empty($_post_meta['vat_number'])) {
                $vat_number = $_post_meta['vat_number'][0];
            }

            return array(
                    'customer_number' => $_post_meta['_customer_user'][0],
                    'email' => $_post_meta['_billing_email'][0],
                    'salutation' => $billing_title,
                    'first_name' => $_post_meta['_billing_first_name'][0],
                    'last_name' => $_post_meta['_billing_last_name'][0],
                    'company' => $_post_meta['_billing_company'][0],
                    'street' => $_post_meta['_billing_address_1'][0],
                    'address_2' => $_post_meta['_billing_address_2'][0],
                    'zip_code' => $_post_meta['_billing_postcode'][0],
                    'city' => $_post_meta['_billing_city'][0],
                    'country' => $_post_meta['_billing_country'][0],
                    'phone' => $_post_meta['_billing_phone'][0],
                    'vat_number' => $vat_number,
                    'shipping' => array(
                            'salutation' => $shipping_title,
                            'first_name' => $_post_meta['_shipping_first_name'][0],
                            'last_name' => $_post_meta['_shipping_last_name'][0],
                            'company' => $_post_meta['_shipping_company'][0],
                            'street' => $_post_meta['_shipping_address_1'][0],
                            'address_2' => $_post_meta['_shipping_address_2'][0],
                            'zip_code' => $_post_meta['_shipping_postcode'][0],
                            'city' => $_post_meta['_shipping_city'][0],
                            'country' => $_post_meta['_shipping_country'][0],
                    )
            );
        }

        // INVOICE
        public function get_invoice($_invoice_id, $_delivery_note = false) {
            if (!$this->check_settings()) {return false;}
            $_response = $this->request('invoice.get', array('id' => $_invoice_id, 'delivery_note' => $_delivery_note));

            if (!empty($_response)) {
                return $_response;
            }

            return false;
        }

        public function create_invoice($_invoice, $_delivery_note = false) {
            if (!$this->check_settings()) {return false;}

            $_invoice['delivery_note'] = $_delivery_note;
            $_response = $this->request('data.create', $_invoice);

            if (!empty($_response)) {
                $countInvoices = get_option('woo_invoices_count_invoices');
                if(empty($countInvoices)) {
                    add_option('woo_invoices_count_invoices', array('count' => 1));
                } else {
                    update_option('woo_invoices_count_invoices', array('count' => $countInvoices['count']+1));
                }

                return $_response;
            }

            return false;
        }

        public function save_invoice($_order, $_delivery_note = false) {
            if (!$this->check_settings()) {return false;}

            $invoice = $this->get_invoice_data_from_order($_order);

            return $this->create_invoice($invoice, $_delivery_note);
        }

        public function cancel_invoice($_invoice_id) {
            if (!$this->check_settings()) {return false;}

            $_response = $this->request('invoice.cancel', array('invoice_id' => $_invoice_id));

            if (!empty($_response)) {
                return $_response;
            }

            return false;
        }

        public function get_invoice_data_from_order($_order) {
            $_post_meta = get_post_meta($_order->id);

            $_order_items = array();
            foreach ($_order->get_items() as $_item) {
                $_article_post_id = (!empty($_item['variation_id']) ? $_item['variation_id'] : $_item['product_id']);
                $product = get_product($_article_post_id);

                // get article number...
                $_article_number = get_post_meta($_article_post_id, '_sku', true);
                if (empty($_article_number)) {
                    $_article_number = get_post_meta($_item['product_id'], '_sku', true);
                }

                // get product unit...
                $_unit_checks = array('einheit', 'unit');
                $_unit = get_post_meta($_item['product_id'], '_unit', true);
                if (empty($_unit)) {
                    $_attributes = get_post_meta($_item['product_id'], '_product_attributes', true);
                    foreach ($_attributes as $_attr_name => $_attribute) {
                        foreach ($_unit_checks as $_unit_check) {
                            if (strtolower($_attr_name) == strtolower($_unit_check)) {
                                $_unit = $_attribute['value'];
                                break;
                            }
                        }
                    }
                }

                $price_including_tax = false;
                if(get_option('woocommerce_prices_include_tax') === 'yes') { $price_including_tax = true; }
                $_quantity = (!empty($_item['qty']) ? $_item['qty'] : 1);
                $_price = $_order->get_line_subtotal($_item, $price_including_tax, false)/$_quantity;

                $_newItem = array(
                        'article_number' => $_article_number,
                        'name' => $_item['name'],
                        'description' => $product->post->post_content,
                        'unit' => $_unit,
                        'quantity' => $_quantity,
                        'price' => $_price,
                        'vat' => $this->get_item_sub_vat($_item)
                );
                if($price_including_tax === true) {
                    $_newItem['is_gross'] = 1;
                }

                if(empty($this->invoice_settings['woo_line_description'])) {
                    $_newItem['description'] = '';
                } else if($this->invoice_settings['woo_line_description'] == 'short' && !empty($product->post->post_excerpt)) {
                    $_newItem['description'] = $product->post->post_excerpt;
                } else if($this->invoice_settings['woo_line_description'] == 'variation_title' && !empty($_item['variation_id'])) {
                    $variation = new \WC_Product_Variation($_article_post_id);
                    $variations = $variation->get_variation_attributes();
                    $count = 0;
                    $_newItem['description'] = '';
                    foreach($variations AS $key => $value) {
                        if($count > 0) {
                            $_newItem['description'] .= ', ';
                        }
                        $_newItem['description'] .= $value;
                        $count++;
                    }
                } else if($this->invoice_settings['woo_line_description'] == 'variation' && !empty($_item['variation_id'])) {
                    $variation = new \WC_Product_Variation($_article_post_id);
                    $_newItem['description'] = strip_tags($variation->get_variation_description());
                } else if($this->invoice_settings['woo_line_description'] != 'article') {
                    $_newItem['description'] = '';
                }

                $_order_items[] = $_newItem;
            }

            foreach ($_order->get_fees() as $_item) {
                $_quantity = (!empty($_item['qty']) ? $_item['qty'] : 1);
                $_price = $_order->get_line_total($_item, $price_including_tax, false)/$_quantity;

                $_order_array_temp = array(
                        'article_number' => '',
                        'name' => $_item['name'],
                        'description' => '',
                        'unit' => '',
                        'quantity' => $_quantity,
                        'price' => $_price,
                        'vat' => $this->get_item_vat($_item)
                );
                if($price_including_tax === true) {
                    $_order_array_temp['is_gross'] = 1;
                }

                $_order_items[] = $_order_array_temp;
            }

            // PROMOCODES
            if(!empty($_post_meta['_cart_discount']) && $_post_meta['_cart_discount'][0] > 0) {
                $_order_items[] = array(
                        'article_number' => 'promo',
                        'name' => 'Gutschein',
                        'unit' => '',
                        'quantity' => 1,
                        'price' => -($_post_meta['_cart_discount'][0] + $_post_meta['_cart_discount_tax'][0]),
                        'vat' => round(($_post_meta['_cart_discount_tax'][0] / $_post_meta['_cart_discount'][0]) * 100),
                        'is_gross' => 1
                );
            }

            // WOORENT
            if(!empty($_post_meta['_woo_rent_insurance'][0])) {
                foreach($_order_items AS $key => $item) {
                    $_order_items[$key]['description'] = $_post_meta['_woo_rent_start_date'][0].' - '.$_post_meta['_woo_rent_end_date'][0];
                }
                $_order_items[] = array(
                        'article_number' => 'vers',
                        'name' => 'Versicherung',
                        'unit' => '',
                        'quantity' => 1,
                        'price' => $_post_meta['_woo_rent_insurance'][0],
                        'vat' => round($_order->order_shipping_tax * 100 / $_order->order_shipping),
                        'is_gross' => 1
                );
            }

            if(!empty($_order->order_shipping)) {
                if(!empty($this->invoice_settings['woo_invoice_shipping_code'])) {
                    $shipping_article_code = $this->invoice_settings['woo_invoice_shipping_code'];
                } else {
                    $shipping_article_code = 'vk';
                }
                $_order_items[] = array(
                        'article_number' => $shipping_article_code,
                        'name' => 'Versandkosten',
                        'unit' => '',
                        'quantity' => 1,
                        'price' => $_order->order_shipping + $_order->order_shipping_tax,
                        'vat' => round($_order->order_shipping_tax * 100 / $_order->order_shipping),
                        'is_gross' => 1
                );
            }

            $customer = $this->get_customer_data_from_order($_order);

            $order_number = $_order->get_order_number();
            if(!empty($this->invoice_settings['woo_order_number_prefix'])) {
                $order_number = $this->invoice_settings['woo_order_number_prefix'] . $order_number;
            }
            if(!empty($this->invoice_settings['woo_order_number_suffix'])) {
                $order_number .= $this->invoice_settings['woo_order_number_suffix'];
            }

            if($this->invoice_settings['woo_invoice_date'] == 'order') {
                $date = date('Y-m-d', strtotime($_order->order_date));
            } else {
                $date = date('Y-m-d');
            }

            $payed = true;
            if(!isset($this->invoice_settings['woo_payment_methods_'.$_order->payment_method_title])) {
                $this->invoice_settings['woo_payment_methods_'.$_order->payment_method_title] = null;
            }
            if($this->invoice_settings['woo_payment_methods_'.$_order->payment_method_title] == 'payed' || empty($this->invoice_settings['woo_payment_methods_'.$_order->payment_method_title])) {
                $payed = true;
            } else if($this->invoice_settings['woo_payment_methods_'.$_order->payment_method_title] == 'not_payed') {
                $payed = false;
            }

            return array(
                    'id' => (!empty($_post_meta['woo_invoices_invoice_id']) ? $_post_meta['woo_invoices_invoice_id'][0] : 0),
                    'order_key' => $_post_meta['_order_key'][0],
                    'order_number' => $order_number,
                    'payment_method' => $_order->payment_method_title,
                    'payed' => $payed,
                    'currency' => $_post_meta['_order_currency'][0],
                    'date' => $date,
                    'items' => $_order_items,
                    'customer' => $customer,
                    'base_country' => \WC_Countries::get_base_country(),
                    'home_url' => get_home_url()
            );
        }

        private function get_item_vat($_item) {
            if (empty($_item['line_tax']) || !is_numeric($_item['line_tax'])) { return 0; }

            return round($_item['line_tax'] * 100 / $_item['line_total']);
        }

        private function get_item_sub_vat($_item) {
            if (empty($_item['line_subtotal_tax']) || !is_numeric($_item['line_subtotal_tax'])) { return 0; }

            return round($_item['line_subtotal_tax'] * 100 / $_item['line_subtotal']);
        }

        // SHIPPING
        public function create_shipping($_data) {
            $_response = array('error' => __('Unbekannter Fehler', \WooInvoicesMain::$text_domain));

            try {
                if ($this->check_settings()) {

                    $_response = $this->request('shipping.create', $_data);

                    if (!empty($_response)) {
                        return $_response;
                    }
                }
                else {
                    $_response = array('error' => __('Falsche Einstellungen', \WooInvoicesMain::$text_domain));
                }
            }
            catch (\Exception $_exception) {
                $_response = array(
                        'message' => $_exception->getMessage(),
                        'line' => $_exception->getLine(),
                        'file' => $_exception->getFile()
                );
            }

            return $_response;
        }

        public function create_shipping_quote($_data) {
            $_response = array('error' => __('Unbekannter Fehler', \WooInvoicesMain::$text_domain));

            try {
                if ($this->check_settings()) {

                    $_response = $this->request('shipping_quote.create', $_data);

                    if (!empty($_response)) {
                        return $_response;
                    }
                }
                else {
                    $_response = array('error' => __('Falsche Einstellungen', \WooInvoicesMain::$text_domain));
                }
            }
            catch (\Exception $_exception) {
                $_response = array(
                        'message' => $_exception->getMessage(),
                        'line' => $_exception->getLine(),
                        'file' => $_exception->getFile(),
                        'code' => $_exception->getCode()
                );
            }

            return $_response;
        }
    }
}