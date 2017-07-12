<?php
/**
 * Plugin Name: WooRechnung
 * Plugin URI: http://www.zweischneider.de
 * Description:
 * Version: 1.0.0
 * Author: ZWEISCHNEIDER GmbH & Co. KG
 * Author URI: http://www.zweischneider.de
 *
 * Text Domain: woorechnung
 *
 * @package WooRechnung
 * @category Core
 * @author ZWEISCHNEIDER
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WooInvoicesMain')) {
    final class WooInvoicesMain {

        // settings fields...
        private $settings = null;
        private $invoice_settings = null;
        private $invoice_mail_settings = null;
        private $shipping_settings = null;
        private $shipping_preset_settings = array();
        private $shipping_labels = array();

        // object fields...
        private $api = null;
        private $backend = null;
        private $order = null;
        private $product = null;

        // grouping fields...
        public static $text_domain = 'woorechnung';
        public static $settings_group = 'woo_invoices_settings_group';
        public static $settings_page_name = 'woo_invoices_plugin_settings';

        public static $plugin_url = '';

        // language fields...
        public static $countries = array();

        public static $shipping_carriers = array(
            array('name' => 'DHL', 'value' => 'DHL'),
            array('name' => 'DPD', 'value' => 'DPD'),
            array('name' => 'GLS', 'value' => 'GLS'),
            array('name' => 'Hermes', 'value' => 'Hermes'),
            array('name' => 'UPS', 'value' => 'UPS'),
            array('name' => 'Deutsche Post AG', 'value' => 'dpag')
        );

        public static $shipping_services = array();
        public static $shipping_package_types = array();
        public static $preset_fields = array();

        public function __construct() {
            self::$plugin_url = plugin_dir_url(__FILE__);
            $this->init();
            do_action('woo_invoices_loaded');
        }

        private function init() {
            $this->language();
            $this->includes();
            $this->load_settings();
            $this->init_api();
            $this->init_backend();
            $this->add_actions();
        }

        // language methods...
        public static function get_shipping_carrier_name($_value) {return self::get_langauge_name($_value, self::$shipping_carriers);}
        public static function get_shipping_service_name($_value) {return self::get_langauge_name($_value, self::$shipping_services);}
        public static function get_shipping_package_type_name($_value) {return self::get_langauge_name($_value, self::$shipping_package_types);}
        public static function get_langauge_name($_value, $_language_dict) {
            foreach ($_language_dict as $_dict) {
                if ($_dict['value'] == $_value) {return $_dict['name'];}
            }
            return $_value;
        }

        public static function get_value_of_shipping_preset_type($_shipping_preset, $_type) {
            switch ($_type) {
                case 'carrier': return self::get_shipping_carrier_name($_shipping_preset['labels_properties_'.$_type]);
                case 'service': return self::get_shipping_service_name($_shipping_preset['labels_properties_'.$_type]);
                case 'package_type': return self::get_shipping_package_type_name($_shipping_preset['labels_properties_'.$_type]);
            }
            return $_shipping_preset['labels_properties_'.$_type];
        }

        private function language() {
            self::$countries = array(

                array('name' => __('Afghanistan', \WooInvoicesMain::$text_domain), 'value' => 'AF'),
                array('name' => __('Albanien', \WooInvoicesMain::$text_domain), 'value' => 'AL'),
                array('name' => __('Amerikanisch Samoa', \WooInvoicesMain::$text_domain), 'value' => 'AS'),
                array('name' => __('Andorra', \WooInvoicesMain::$text_domain), 'value' => 'AD'),
                array('name' => __('Angola', \WooInvoicesMain::$text_domain), 'value' => 'AO'),
                array('name' => __('Anguilla', \WooInvoicesMain::$text_domain), 'value' => 'AI'),
                array('name' => __('Antarktis', \WooInvoicesMain::$text_domain), 'value' => 'AQ'),
                array('name' => __('Antigua und Barbuda', \WooInvoicesMain::$text_domain), 'value' => 'AG'),
                array('name' => __('Argentinien', \WooInvoicesMain::$text_domain), 'value' => 'AR'),
                array('name' => __('Armenien', \WooInvoicesMain::$text_domain), 'value' => 'AM'),
                array('name' => __('Aruba', \WooInvoicesMain::$text_domain), 'value' => 'AW'),
                array('name' => __('Österreich', \WooInvoicesMain::$text_domain), 'value' => 'AT'),
                array('name' => __('Australien', \WooInvoicesMain::$text_domain), 'value' => 'AU'),
                array('name' => __('Aserbaidschan', \WooInvoicesMain::$text_domain), 'value' => 'AZ'),
                array('name' => __('Bahamas', \WooInvoicesMain::$text_domain), 'value' => 'BS'),
                array('name' => __('Bahrain', \WooInvoicesMain::$text_domain), 'value' => 'BH'),
                array('name' => __('Bangladesh', \WooInvoicesMain::$text_domain), 'value' => 'BD'),
                array('name' => __('Barbados', \WooInvoicesMain::$text_domain), 'value' => 'BB'),
                array('name' => __('Weißrussland', \WooInvoicesMain::$text_domain), 'value' => 'BY'),
                array('name' => __('Belgien', \WooInvoicesMain::$text_domain), 'value' => 'BE'),
                array('name' => __('Belize', \WooInvoicesMain::$text_domain), 'value' => 'BZ'),
                array('name' => __('Benin', \WooInvoicesMain::$text_domain), 'value' => 'BJ'),
                array('name' => __('Bermuda', \WooInvoicesMain::$text_domain), 'value' => 'BM'),
                array('name' => __('Bhutan', \WooInvoicesMain::$text_domain), 'value' => 'BT'),
                array('name' => __('Bolivien', \WooInvoicesMain::$text_domain), 'value' => 'BO'),
                array('name' => __('Bosnien Herzegowina', \WooInvoicesMain::$text_domain), 'value' => 'BA'),
                array('name' => __('Botswana', \WooInvoicesMain::$text_domain), 'value' => 'BW'),
                array('name' => __('Bouvet Island', \WooInvoicesMain::$text_domain), 'value' => 'BV'),
                array('name' => __('Brasilien', \WooInvoicesMain::$text_domain), 'value' => 'BR'),
                array('name' => __('Brunei Darussalam', \WooInvoicesMain::$text_domain), 'value' => 'BN'),
                array('name' => __('Bulgarien', \WooInvoicesMain::$text_domain), 'value' => 'BG'),
                array('name' => __('Burkina Faso', \WooInvoicesMain::$text_domain), 'value' => 'BF'),
                array('name' => __('Burundi', \WooInvoicesMain::$text_domain), 'value' => 'BI'),
                array('name' => __('Kambodscha', \WooInvoicesMain::$text_domain), 'value' => 'KH'),
                array('name' => __('Kamerun', \WooInvoicesMain::$text_domain), 'value' => 'CM'),
                array('name' => __('Kanada', \WooInvoicesMain::$text_domain), 'value' => 'CA'),
                array('name' => __('Kap Verde', \WooInvoicesMain::$text_domain), 'value' => 'CV'),
                array('name' => __('Cayman Inseln', \WooInvoicesMain::$text_domain), 'value' => 'KY'),
                array('name' => __('Zentralafrikanische Republik', \WooInvoicesMain::$text_domain), 'value' => 'CF'),
                array('name' => __('Tschad', \WooInvoicesMain::$text_domain), 'value' => 'TD'),
                array('name' => __('Chile', \WooInvoicesMain::$text_domain), 'value' => 'CL'),
                array('name' => __('China', \WooInvoicesMain::$text_domain), 'value' => 'CN'),
                array('name' => __('Kolumbien', \WooInvoicesMain::$text_domain), 'value' => 'CO'),
                array('name' => __('Comoros', \WooInvoicesMain::$text_domain), 'value' => 'KM'),
                array('name' => __('Kongo', \WooInvoicesMain::$text_domain), 'value' => 'CG'),
                array('name' => __('Cook Inseln', \WooInvoicesMain::$text_domain), 'value' => 'CK'),
                array('name' => __('Costa Rica', \WooInvoicesMain::$text_domain), 'value' => 'CR'),
                array('name' => __('Elfenbeinküste', \WooInvoicesMain::$text_domain), 'value' => 'CI'),
                array('name' => __('Kroatien', \WooInvoicesMain::$text_domain), 'value' => 'HR'),
                array('name' => __('Kuba', \WooInvoicesMain::$text_domain), 'value' => 'CU'),
                array('name' => __('Tschechien', \WooInvoicesMain::$text_domain), 'value' => 'CZ'),
                array('name' => __('Dänemark', \WooInvoicesMain::$text_domain), 'value' => 'DK'),
                array('name' => __('Djibouti', \WooInvoicesMain::$text_domain), 'value' => 'DJ'),
                array('name' => __('Dominikanische Republik', \WooInvoicesMain::$text_domain), 'value' => 'DO'),
                array('name' => __('Osttimor', \WooInvoicesMain::$text_domain), 'value' => 'TP'),
                array('name' => __('Ecuador', \WooInvoicesMain::$text_domain), 'value' => 'EC'),
                array('name' => __('Ägypten', \WooInvoicesMain::$text_domain), 'value' => 'EG'),
                array('name' => __('El Salvador', \WooInvoicesMain::$text_domain), 'value' => 'SV'),
                array('name' => __('Äquatorial Guinea', \WooInvoicesMain::$text_domain), 'value' => 'GQ'),
                array('name' => __('Eritrea', \WooInvoicesMain::$text_domain), 'value' => 'ER'),
                array('name' => __('Estland', \WooInvoicesMain::$text_domain), 'value' => 'EE'),
                array('name' => __('Äthiopien', \WooInvoicesMain::$text_domain), 'value' => 'ET'),
                array('name' => __('Falkland Inseln', \WooInvoicesMain::$text_domain), 'value' => 'FK'),
                array('name' => __('Faroe Inseln', \WooInvoicesMain::$text_domain), 'value' => 'FO'),
                array('name' => __('Fiji', \WooInvoicesMain::$text_domain), 'value' => 'FJ'),
                array('name' => __('Finland', \WooInvoicesMain::$text_domain), 'value' => 'FI'),
                array('name' => __('Frankreich', \WooInvoicesMain::$text_domain), 'value' => 'FR'),
                array('name' => __('Französisch Guiana', \WooInvoicesMain::$text_domain), 'value' => 'GF'),
                array('name' => __('Französisch Polynesien', \WooInvoicesMain::$text_domain), 'value' => 'PF'),
                array('name' => __('Gabon', \WooInvoicesMain::$text_domain), 'value' => 'GA'),
                array('name' => __('Gambia', \WooInvoicesMain::$text_domain), 'value' => 'GM'),
                array('name' => __('Georgien', \WooInvoicesMain::$text_domain), 'value' => 'GE'),
                array('name' => __('Deutschland', \WooInvoicesMain::$text_domain), 'value' => 'DE'),
                array('name' => __('Ghana', \WooInvoicesMain::$text_domain), 'value' => 'GH'),
                array('name' => __('Gibraltar', \WooInvoicesMain::$text_domain), 'value' => 'GI'),
                array('name' => __('Griechenland', \WooInvoicesMain::$text_domain), 'value' => 'GR'),
                array('name' => __('Grönland', \WooInvoicesMain::$text_domain), 'value' => 'GL'),
                array('name' => __('Grenada', \WooInvoicesMain::$text_domain), 'value' => 'GD'),
                array('name' => __('Guadeloupe', \WooInvoicesMain::$text_domain), 'value' => 'GP'),
                array('name' => __('Guam', \WooInvoicesMain::$text_domain), 'value' => 'GU'),
                array('name' => __('Guatemala', \WooInvoicesMain::$text_domain), 'value' => 'GT'),
                array('name' => __('Guinea', \WooInvoicesMain::$text_domain), 'value' => 'GN'),
                array('name' => __('Guyana', \WooInvoicesMain::$text_domain), 'value' => 'GY'),
                array('name' => __('Haiti', \WooInvoicesMain::$text_domain), 'value' => 'HT'),
                array('name' => __('Vatikan', \WooInvoicesMain::$text_domain), 'value' => 'VA'),
                array('name' => __('Honduras', \WooInvoicesMain::$text_domain), 'value' => 'HN'),
                array('name' => __('Ungarn', \WooInvoicesMain::$text_domain), 'value' => 'HU'),
                array('name' => __('Island', \WooInvoicesMain::$text_domain), 'value' => 'IS'),
                array('name' => __('Indien', \WooInvoicesMain::$text_domain), 'value' => 'IN'),
                array('name' => __('Indonesien', \WooInvoicesMain::$text_domain), 'value' => 'ID'),
                array('name' => __('Iran', \WooInvoicesMain::$text_domain), 'value' => 'IR'),
                array('name' => __('Irak', \WooInvoicesMain::$text_domain), 'value' => 'IQ'),
                array('name' => __('Irland', \WooInvoicesMain::$text_domain), 'value' => 'IE'),
                array('name' => __('Israel', \WooInvoicesMain::$text_domain), 'value' => 'IL'),
                array('name' => __('Italien', \WooInvoicesMain::$text_domain), 'value' => 'IT'),
                array('name' => __('Jamaika', \WooInvoicesMain::$text_domain), 'value' => 'JM'),
                array('name' => __('Japan', \WooInvoicesMain::$text_domain), 'value' => 'JP'),
                array('name' => __('Jordanien', \WooInvoicesMain::$text_domain), 'value' => 'JO'),
                array('name' => __('Kasachstan', \WooInvoicesMain::$text_domain), 'value' => 'KZ'),
                array('name' => __('Kenia', \WooInvoicesMain::$text_domain), 'value' => 'KE'),
                array('name' => __('Kiribati', \WooInvoicesMain::$text_domain), 'value' => 'KI'),
                array('name' => __('Kuwait', \WooInvoicesMain::$text_domain), 'value' => 'KW'),
                array('name' => __('Kirgistan', \WooInvoicesMain::$text_domain), 'value' => 'KG'),
                array('name' => __('Laos', \WooInvoicesMain::$text_domain), 'value' => 'LA'),
                array('name' => __('Lettland', \WooInvoicesMain::$text_domain), 'value' => 'LV'),
                array('name' => __('Libanon', \WooInvoicesMain::$text_domain), 'value' => 'LB'),
                array('name' => __('Lesotho', \WooInvoicesMain::$text_domain), 'value' => 'LS'),
                array('name' => __('Liechtenstein', \WooInvoicesMain::$text_domain), 'value' => 'LI'),
                array('name' => __('Litauen', \WooInvoicesMain::$text_domain), 'value' => 'LT'),
                array('name' => __('Luxemburg', \WooInvoicesMain::$text_domain), 'value' => 'LU'),
                array('name' => __('Macau', \WooInvoicesMain::$text_domain), 'value' => 'MO'),
                array('name' => __('Mazedonien', \WooInvoicesMain::$text_domain), 'value' => 'MK'),
                array('name' => __('Madagaskar', \WooInvoicesMain::$text_domain), 'value' => 'MG'),
                array('name' => __('Malawi', \WooInvoicesMain::$text_domain), 'value' => 'MW'),
                array('name' => __('Malaysia', \WooInvoicesMain::$text_domain), 'value' => 'MY'),
                array('name' => __('Malediven', \WooInvoicesMain::$text_domain), 'value' => 'MV'),
                array('name' => __('Mali', \WooInvoicesMain::$text_domain), 'value' => 'ML'),
                array('name' => __('Malta', \WooInvoicesMain::$text_domain), 'value' => 'MT'),
                array('name' => __('Mauretanien', \WooInvoicesMain::$text_domain), 'value' => 'MR'),
                array('name' => __('Mauritius', \WooInvoicesMain::$text_domain), 'value' => 'MU'),
                array('name' => __('Mayotte', \WooInvoicesMain::$text_domain), 'value' => 'YT'),
                array('name' => __('Mexiko', \WooInvoicesMain::$text_domain), 'value' => 'MX'),
                array('name' => __('Mikronesien', \WooInvoicesMain::$text_domain), 'value' => 'FM'),
                array('name' => __('Moldavien', \WooInvoicesMain::$text_domain), 'value' => 'MD'),
                array('name' => __('Monaco', \WooInvoicesMain::$text_domain), 'value' => 'MC'),
                array('name' => __('Mongolei', \WooInvoicesMain::$text_domain), 'value' => 'MN'),
                array('name' => __('Montserrat', \WooInvoicesMain::$text_domain), 'value' => 'MS'),
                array('name' => __('Marokko', \WooInvoicesMain::$text_domain), 'value' => 'MA'),
                array('name' => __('Mosambik', \WooInvoicesMain::$text_domain), 'value' => 'MZ'),
                array('name' => __('Myanmar', \WooInvoicesMain::$text_domain), 'value' => 'MM'),
                array('name' => __('Namibia', \WooInvoicesMain::$text_domain), 'value' => 'NA'),
                array('name' => __('Nauru', \WooInvoicesMain::$text_domain), 'value' => 'NR'),
                array('name' => __('Nepal', \WooInvoicesMain::$text_domain), 'value' => 'NP'),
                array('name' => __('Niederlande', \WooInvoicesMain::$text_domain), 'value' => 'NL'),
                array('name' => __('Neuseeland', \WooInvoicesMain::$text_domain), 'value' => 'NZ'),
                array('name' => __('Nicaragua', \WooInvoicesMain::$text_domain), 'value' => 'NI'),
                array('name' => __('Niger', \WooInvoicesMain::$text_domain), 'value' => 'NE'),
                array('name' => __('Nigeria', \WooInvoicesMain::$text_domain), 'value' => 'NG'),
                array('name' => __('Niue', \WooInvoicesMain::$text_domain), 'value' => 'NU'),
                array('name' => __('Norfolk Inseln', \WooInvoicesMain::$text_domain), 'value' => 'NF'),
                array('name' => __('Nord Korea', \WooInvoicesMain::$text_domain), 'value' => 'KP'),
                array('name' => __('Norwegen', \WooInvoicesMain::$text_domain), 'value' => 'NO'),
                array('name' => __('Oman', \WooInvoicesMain::$text_domain), 'value' => 'OM'),
                array('name' => __('Pakistan', \WooInvoicesMain::$text_domain), 'value' => 'PK'),
                array('name' => __('Palau', \WooInvoicesMain::$text_domain), 'value' => 'PW'),
                array('name' => __('Panama', \WooInvoicesMain::$text_domain), 'value' => 'PA'),
                array('name' => __('Papua Neu Guinea', \WooInvoicesMain::$text_domain), 'value' => 'PG'),
                array('name' => __('Paraguay', \WooInvoicesMain::$text_domain), 'value' => 'PY'),
                array('name' => __('Peru', \WooInvoicesMain::$text_domain), 'value' => 'PE'),
                array('name' => __('Philippinen', \WooInvoicesMain::$text_domain), 'value' => 'PH'),
                array('name' => __('Polen', \WooInvoicesMain::$text_domain), 'value' => 'PL'),
                array('name' => __('Portugal', \WooInvoicesMain::$text_domain), 'value' => 'PT'),
                array('name' => __('Puerto Rico', \WooInvoicesMain::$text_domain), 'value' => 'PR'),
                array('name' => __('Rumänien', \WooInvoicesMain::$text_domain), 'value' => 'RO'),
                array('name' => __('Russland', \WooInvoicesMain::$text_domain), 'value' => 'RU'),
                array('name' => __('Ruanda', \WooInvoicesMain::$text_domain), 'value' => 'RW'),
                array('name' => __('Samoa', \WooInvoicesMain::$text_domain), 'value' => 'WS'),
                array('name' => __('San Marino', \WooInvoicesMain::$text_domain), 'value' => 'SM'),
                array('name' => __('Saudi-Arabien', \WooInvoicesMain::$text_domain), 'value' => 'SA'),
                array('name' => __('Senegal', \WooInvoicesMain::$text_domain), 'value' => 'SN'),
                array('name' => __('Seychellen', \WooInvoicesMain::$text_domain), 'value' => 'SC'),
                array('name' => __('Sierra Leone', \WooInvoicesMain::$text_domain), 'value' => 'SL'),
                array('name' => __('Singapur', \WooInvoicesMain::$text_domain), 'value' => 'SG'),
                array('name' => __('Slovakei', \WooInvoicesMain::$text_domain), 'value' => 'SK'),
                array('name' => __('Solomon Inseln', \WooInvoicesMain::$text_domain), 'value' => 'SB'),
                array('name' => __('Somalia', \WooInvoicesMain::$text_domain), 'value' => 'SO'),
                array('name' => __('Südafrika', \WooInvoicesMain::$text_domain), 'value' => 'ZA'),
                array('name' => __('Südkorea', \WooInvoicesMain::$text_domain), 'value' => 'KR'),
                array('name' => __('Spanien', \WooInvoicesMain::$text_domain), 'value' => 'ES'),
                array('name' => __('Sri Lanka', \WooInvoicesMain::$text_domain), 'value' => 'LK'),
                array('name' => __('Sudan', \WooInvoicesMain::$text_domain), 'value' => 'SD'),
                array('name' => __('Suriname', \WooInvoicesMain::$text_domain), 'value' => 'SR'),
                array('name' => __('Swasiland', \WooInvoicesMain::$text_domain), 'value' => 'SZ'),
                array('name' => __('Schweden', \WooInvoicesMain::$text_domain), 'value' => 'SE'),
                array('name' => __('Schweiz', \WooInvoicesMain::$text_domain), 'value' => 'CH'),
                array('name' => __('Syrien', \WooInvoicesMain::$text_domain), 'value' => 'SY'),
                array('name' => __('Taiwan', \WooInvoicesMain::$text_domain), 'value' => 'TW'),
                array('name' => __('Tadschikistan', \WooInvoicesMain::$text_domain), 'value' => 'TJ'),
                array('name' => __('Tansania', \WooInvoicesMain::$text_domain), 'value' => 'TZ'),
                array('name' => __('Thailand', \WooInvoicesMain::$text_domain), 'value' => 'TH'),
                array('name' => __('Togo', \WooInvoicesMain::$text_domain), 'value' => 'TG'),
                array('name' => __('Tonga', \WooInvoicesMain::$text_domain), 'value' => 'TO'),
                array('name' => __('Trinidad und Tobago', \WooInvoicesMain::$text_domain), 'value' => 'TT'),
                array('name' => __('Tunesien', \WooInvoicesMain::$text_domain), 'value' => 'TN'),
                array('name' => __('Türkei', \WooInvoicesMain::$text_domain), 'value' => 'TR'),
                array('name' => __('Turkmenistan', \WooInvoicesMain::$text_domain), 'value' => 'TM'),
                array('name' => __('Tuvalu', \WooInvoicesMain::$text_domain), 'value' => 'TV'),
                array('name' => __('Uganda', \WooInvoicesMain::$text_domain), 'value' => 'UG'),
                array('name' => __('Ukraine', \WooInvoicesMain::$text_domain), 'value' => 'UA'),
                array('name' => __('Vereinigte Arabische Emirate', \WooInvoicesMain::$text_domain), 'value' => 'AE'),
                array('name' => __('Vereinigtes Königreich', \WooInvoicesMain::$text_domain), 'value' => 'GB'),
                array('name' => __('Vereinigte Staaten von Amerika', \WooInvoicesMain::$text_domain), 'value' => 'US'),
                array('name' => __('Uruguay', \WooInvoicesMain::$text_domain), 'value' => 'UY'),
                array('name' => __('Usbekistan', \WooInvoicesMain::$text_domain), 'value' => 'UZ'),
                array('name' => __('Vanuatu', \WooInvoicesMain::$text_domain), 'value' => 'VU'),
                array('name' => __('Venezuela', \WooInvoicesMain::$text_domain), 'value' => 'VE'),
                array('name' => __('Vietnam', \WooInvoicesMain::$text_domain), 'value' => 'VN'),
                array('name' => __('Virgin Islands', \WooInvoicesMain::$text_domain), 'value' => 'VG'),
                array('name' => __('Westsahara', \WooInvoicesMain::$text_domain), 'value' => 'EH'),
                array('name' => __('Jemen', \WooInvoicesMain::$text_domain), 'value' => 'YE'),
                array('name' => __('Jugoslavien', \WooInvoicesMain::$text_domain), 'value' => 'YU'),
                array('name' => __('Zaire', \WooInvoicesMain::$text_domain), 'value' => 'ZR'),
                array('name' => __('Sambia', \WooInvoicesMain::$text_domain), 'value' => 'ZM'),
                array('name' => __('Simbabwe', \WooInvoicesMain::$text_domain), 'value' => 'ZW'),
            );

            self::$shipping_services = array(
                array('name' => __('Standard', \WooInvoicesMain::$text_domain), 'value' => 'standard'),
                array('name' => __('Express', \WooInvoicesMain::$text_domain), 'value' => 'one_day'),
                array('name' => __('Express until 10 o´clock', \WooInvoicesMain::$text_domain), 'value' => 'one_day_early'),
                array('name' => __('Returns', \WooInvoicesMain::$text_domain), 'value' => 'returns')
            );

            self::$shipping_package_types = array(
                array('name' => '', 'value' => ''),
                array('name' => __('Brief', \WooInvoicesMain::$text_domain), 'value' => 'letter'),
                array('name' => __('Warensendung', \WooInvoicesMain::$text_domain), 'value' => 'parcel_letter'),
                array('name' => __('Buchsendung', \WooInvoicesMain::$text_domain), 'value' => 'books')
            );

            self::$preset_fields = array(
                array('id' => 'carrier', 'name' => __('Carrier', \WooInvoicesMain::$text_domain)),
                array('id' => 'service', 'name' => __('Service', \WooInvoicesMain::$text_domain)),
                array('id' => 'package_type', 'name' => __('Typ', \WooInvoicesMain::$text_domain)),
                array('id' => 'package_width', 'name' => __('Breite (in cm)', \WooInvoicesMain::$text_domain)),
                array('id' => 'package_length', 'name' => __('Länge (in cm)', \WooInvoicesMain::$text_domain)),
                array('id' => 'package_height', 'name' => __('Höhe (in cm)', \WooInvoicesMain::$text_domain)),
                array('id' => 'package_weight', 'name' => __('Gewicht (in kg)', \WooInvoicesMain::$text_domain))
            );
        }

        // include methods...
        private function includes() {
            include_once('includes/class-backend.php');
            include_once('includes/class-api.php');
        }

        // settings methods...
        private function load_settings() {
            $this->settings = get_option(self::$settings_page_name);
            $this->invoice_settings = get_option(self::$settings_page_name.'_invoice');
            $this->invoice_mail_settings = get_option(self::$settings_page_name.'_invoice_mail');
            $this->shipping_settings = get_option(self::$settings_page_name.'_shipping');
            $this->shipping_preset_settings = get_option(self::$settings_page_name.'_shipping_presets');
        }

        // init methods...
        private function init_api() {
            $this->api = new \WooInvoices\Api();
        }

        private function init_backend() {
            $this->backend = new \WooInvoices\Backend();
        }

        // actions methods...
        private function add_actions() {
            try {
                if (!empty($this->settings['woo_invoices_api_key'])) {
                    // Nur für kurze Zeit im Code um die Settings zu übernehmen
                    $get_options = get_option('woo_invoices_plugin_settings');
                    if(isset($get_options['woo_invoices_status'])) {
                        $get_options_invoice = get_option('woo_invoices_plugin_settings_invoice');
                        $get_options_invoice['woo_invoices_status'] = $get_options['woo_invoices_status'];
                        $get_options_invoice['woo_send_invoices'] = $get_options['woo_send_invoices'];
                        $get_options_invoice['woo_line_description'] = $get_options['woo_line_description'];
                        $get_options_invoice['woo_order_number_prefix'] = $get_options['woo_order_number_prefix'];
                        $get_options_invoice['woo_order_number_suffix'] = $get_options['woo_order_number_suffix'];
                        unset($get_options['woo_invoices_status']);
                        unset($get_options['woo_send_invoices']);
                        unset($get_options['woo_line_description']);
                        unset($get_options['woo_order_number_prefix']);
                        unset($get_options['woo_order_number_suffix']);
                        update_option('woo_invoices_plugin_settings', $get_options);
                        update_option('woo_invoices_plugin_settings_invoice', $get_options_invoice);
                        $this->settings = get_option(self::$settings_page_name);
                        $this->invoice_settings = get_option(self::$settings_page_name.'_invoice');
                    }

                    if (isset($_REQUEST['remove_woorechnung_error'])) {
                        if ($_REQUEST['remove_woorechnung_error'] == 1) {
                            delete_option('woo_invoices_plugin_error');
                        }
                    }
                    $savedError = get_option('woo_invoices_plugin_error');
                    if(!empty($savedError)) {
                        add_action('admin_notices', array($this, 'error_woocommerce'));
                    }

                    if (isset($_REQUEST['remove_woorechnung_rate_msg'])) {
                        if ($_REQUEST['remove_woorechnung_rate_msg'] == 1) {
                            add_option('woo_invoices_rate_msg', array('time' => time()));
                        }
                    }
                    $countInvoices = get_option('woo_invoices_count_invoices');
                    $rateMsg = get_option('woo_invoices_rate_msg');
                    if(empty($rateMsg) && $countInvoices['count'] > 1) {
                        add_action('admin_notices', array($this, 'rate_woocommerce'));
                    }

                    if (isset($_REQUEST['api_test'])) {
                        if ($_REQUEST['api_test'] == 1) {
                            add_action('admin_notices', array($this, 'api_test'));
                        }
                    }

                    add_action('phpmailer_init', array($this, 'mailer_config'), 10, 1);

                    // INVOICE
                    if(!isset($this->invoice_settings['woo_invoices_deactivate'])) { $this->invoice_settings['woo_invoices_deactivate'] = 0; }
                    if($this->invoice_settings['woo_invoices_deactivate'] < 1) {
                        add_action('admin_footer-edit.php', array($this, 'add_pdf_export_to_order_list'));
                        add_action('load-edit.php', array($this, 'pdf_bulk_export'));

                        add_action('woocommerce_admin_order_actions_end', array($this, 'add_invoice_backend_listing'));

                        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'customer_account_add_invoice_link'), 10, 2);

                        if (empty($this->invoice_settings['woo_invoices_status'])) {
                            $this->invoice_settings['woo_invoices_status'] = 'completed';
                        }
                        add_action('woocommerce_order_status_' . $this->invoice_settings['woo_invoices_status'], array($this, 'order_status_completed'), 9, 1);

                        if(!isset($this->invoice_settings['woo_send_invoices'])) { $this->invoice_settings['woo_send_invoices'] = 0; }
                        if ($this->invoice_settings['woo_send_invoices'] > 0) {
                            if(!isset($this->invoice_settings['woo_send_invoices_type'])) { $this->invoice_settings['woo_send_invoices_type'] = 0; }
                            if($this->invoice_settings['woo_send_invoices_type'] > 0) {
                                add_action('woocommerce_email_attachments', array($this, 'send_invoice_email_attach'), 10, 3);
                            } else {
                                add_action('woocommerce_order_status_' . $this->invoice_settings['woo_sending_status'], array($this, 'send_invoice_email'), 10, 1);
                            }
                        }

                        add_action('woocommerce_order_status_cancelled', array($this, 'cancel_invoice'), 10, 1);
                        add_action('woocommerce_order_status_refunded', array($this, 'cancel_invoice'), 10, 1);

                        add_action('wp_ajax_download_invoice', array($this, 'download_invoice_ajax'));
                    }

                    // SHIPPING
                    if(!isset($this->shipping_settings['woo_invoices_shipping_activate'])) { $this->shipping_settings['woo_invoices_shipping_activate'] = 0; }
                    if ($this->shipping_settings['woo_invoices_shipping_activate'] > 0) {
                        add_action('admin_head', array($this, 'admin_head'));
                        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
                        add_action('admin_print_footer_scripts', array($this, 'admin_print_footer_scripts'));
                    }

                    add_action('wp_ajax_woo_invoices_api', array($this, 'ajax_woo_invoices_api_callback'));

                    return true;
                } else {
                    add_action('admin_notices', array($this, 'error_woocommerce_licence'));
                    return false;
                }
            }
            catch(\Exception $_exception) {
                echo $_exception;
                return false;
            }
        }

        public function ajax_woo_invoices_api_callback() {
            if (!current_user_can('manage_woocommerce')) {exit();}

            $_response = array('error' => __('Service wurde nicht gefunden', \WooInvoicesMain::$text_domain));

            try {
                switch ($_POST['service']) {

                    case 'shipping_data.save':
                        if (!empty($_POST['post_id'])) {
                            $_response = array('saved' => false);
                            if ($this->save_shipping_data($_POST['post_id'], array('receiver' => $_POST['receiver'], 'properties' => $_POST['properties']))) {
                                $_response = array('saved' => true);
                            }
                        }
                        break;

                    case 'shipping.create':
                        $_response = $this->api->create_shipping($_POST);
                        if (!empty($_response['response'])) {
                            if (!empty($_response['response']['id'])) {
                                $this->save_new_shipping_label($_response['request']['post_id'], array($_response));
                                $_response['label'] = $this->render_shipping_label($_response);
                            }
                        }
                        break;

                    case 'shipping_quote.create':
                        $_response = $this->api->create_shipping_quote($_POST);
                        break;

                    case 'shipping.delete':
                        $_response = array(
                            'request' => $_POST,
                            'response' => array('deleted' => $this->delete_shipping_label($_POST['post_id'], $_POST['label_id']))
                        );
                        break;
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
            wp_send_json($_response);
        }

        public function sanitize_array_keys($_search, $_replace, $_array) {
            $_array2 = array();
            foreach ($_array as $_key => $_value) {
                $_array2[str_replace($_search, $_replace, $_key)] = $_value;
            }
            return $_array2;
        }

        public function admin_head() {
            wp_register_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');
            wp_register_style('woo_invoices_styles', self::$plugin_url.'css/styles.css');

            wp_enqueue_style('fontawesome');
            wp_enqueue_style('woo_invoices_styles');

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('woo_invoices_shipping_js', self::$plugin_url.'js/shipping.js', array('jquery'));

            wp_localize_script('woo_invoices_shipping_js', 'woorechnung_language', array(
                'delete_request' => esc_html__('Wirklich löschen?', \WooInvoicesMain::$text_domain),
                'failure' => esc_html__('Fehler', \WooInvoicesMain::$text_domain)
            ));
        }

        public function admin_print_footer_scripts() {
            ?>
            <script type='text/javascript'>
                woo_invoices_shipping.set_post_id(<?php echo get_the_ID(); ?>);
                woo_invoices_shipping.set_sender(<?php echo json_encode($this->sanitize_array_keys('woo_invoices_shipping_sender_', '', $this->shipping_settings)); ?>);
            </script>
            <?php
        }

        public function add_meta_boxes() {
            if (current_user_can('manage_woocommerce')) {
                add_meta_box('woo_invoices_order_shipping_labels_meta_box', __('shipcloud', \WooInvoicesMain::$text_domain), array($this, 'add_shipping_labels_meta_box'), 'shop_order');
            }
        }

        public function get_shipping_labels($_post_id) {
            if ($_post_id === false) {return array();}

            if ($_meta = get_post_meta($_post_id, '_woo_invoices_shipping_labels', true)) {
                if (!empty($_meta)) {
                    return unserialize(base64_decode($_meta));
                }
            }
            return array();
        }

        public function save_shipping_labels($_post_id, $_labels_data) {
            if ($_post_id === false) {return false;}
            update_post_meta($_post_id, '_woo_invoices_shipping_labels', base64_encode(serialize($_labels_data)));
            $this->logging('Save Shipping Label (PostID: '.$_post_id.')');
            return true;
        }

        public function save_new_shipping_label($_post_id, $_label_data) {
            if ($_post_id === false) {return;}

            if ($_shipping_labels = $this->get_shipping_labels($_post_id)) {
                if (!empty($_shipping_labels)) {
                    $_label_data = array_merge($_shipping_labels, $_label_data);
                }
            }
            $this->save_shipping_labels($_post_id, $_label_data);
        }

        public function delete_shipping_label($_post_id, $_label_id) {
            if ($_post_id === false) {return false;}

            $_deleted = false;
            $_new_shipping_labels = array();

            $_shipping_labels = $this->get_shipping_labels($_post_id);
            foreach ($_shipping_labels as $_shipping_label) {
                if ($_shipping_label['response']['id'] != $_label_id) {
                    $_new_shipping_labels[] = $_shipping_label;
                }
                else {
                    $_deleted = true;
                }
            }
            $this->save_shipping_labels($_post_id, $_new_shipping_labels);
            $this->logging('Delete Shipping Label (PostID: '.$_post_id.', LabelID: '.$_label_id.')');

            return $_deleted;
        }

        public function get_shipping_data($_post_id, $_data = array()) {

            if ($_post_id === false) {return $_data;}

            if ($_meta = get_post_meta($_post_id, '_woo_invoices_shipping_data', true)) {
                if (!empty($_meta)) {

                    $_meta = unserialize(base64_decode($_meta));

                    foreach ($_meta as $_key => $_value) {
                        if (is_array($_value)) {
                            foreach ($_value as $_key2 => $_value2) {
                                $_data[$_key][$_key2] = $_value2;
                            }
                        }
                        else {
                            $_data[$_key] = $_value;
                        }
                    }
                }
            }
            return $_data;
        }

        public function save_shipping_data($_post_id, $_data) {
            if ($_post_id === false) {return false;}
            update_post_meta($_post_id, '_woo_invoices_shipping_data', base64_encode(serialize($_data)));
            return true;
        }

        public function get_package_weight() {
            $all_Items = $this->order->get_items();
            $totalWeight = 0;

            foreach ($all_Items as $item) {
                $productID = $item['product_id'];
                $quantity = $item['quantity'];
                $this->product = new WC_Product($productID);
                $productWeight = $this->product->get_weight();
                $totalWeight += $quantity * $productWeight;
            }

            return $totalWeight;
        }

        public function add_shipping_labels_meta_box() {
            $this->order = new WC_Order(get_the_ID());
            $_post_meta = get_post_meta(get_the_ID());

            $_data = array(
                'receiver' => array(
                    'company' => $this->order->get_shipping_company(),
                    'first_name' => $this->order->get_shipping_first_name(),
                    'last_name' => $this->order->get_shipping_last_name(),
                    'street' => trim(preg_replace('/([^\d]+)\s+(.+)/is', '$1', $this->order->get_shipping_address_1())),
                    'street_no' => preg_replace('/([^\d]+)\s+(.+)/is', '$2', $this->order->get_shipping_address_1()),
                    'care_of' => $this->order->get_shipping_address_2(),
                    'zip_code' => $this->order->get_shipping_postcode(),
                    'city' => $this->order->get_shipping_city(),
                    'state' => $this->order->get_shipping_state(),
                    'country' => $this->order->get_shipping_country()
                ),
                'properties' => array(
                    'carrier' => '',
                    'service' => 'standard',
                    'package_width' => '',
                    'package_length' => '',
                    'package_height' => '',
                    'declared_value' => array('amount' => $this->order->get_total()),
                    'package_weight' => $this->get_package_weight(),
                    'package_bulk' => 'false',
                    'reference_number' => $this->order->get_order_number()
                )
            );

            if(!isset($this->shipping_settings['woo_invoices_shipping_sender_empty_notification_email'])) {
                $this->shipping_settings['woo_invoices_shipping_sender_empty_notification_email'] = '0';
            }
            if($this->shipping_settings['woo_invoices_shipping_sender_empty_notification_email'] < 1) {
                $_data['properties']['notification_email'] = $this->order->get_billing_email();
            }

            if($this->shipping_settings['woo_invoices_shipping_sender_default_preset'] != '-1') {
                foreach ($this->shipping_preset_settings as $_index => $_shipping_preset) {
                    if($_index == $this->shipping_settings['woo_invoices_shipping_sender_default_preset']) {
                        $_data['properties']['carrier'] = $_shipping_preset['labels_properties_carrier'];
                        $_data['properties']['service'] = $_shipping_preset['labels_properties_service'];
                        if(isset($_shipping_preset['labels_properties_package_type'])) {
                            $_data['properties']['package_type'] = $_shipping_preset['labels_properties_package_type'];
                        }
                        $_data['properties']['package_width'] = $_shipping_preset['labels_properties_package_width'];
                        $_data['properties']['package_length'] = $_shipping_preset['labels_properties_package_length'];
                        $_data['properties']['package_height'] = $_shipping_preset['labels_properties_package_height'];
                        if($_data['properties']['package_weight'] <= 0) {
                            $_data['properties']['package_weight'] = $_shipping_preset['labels_properties_package_weight'];
                        }
                    }
                }
            }

            $_data = $this->get_shipping_data(get_the_ID(), $_data);

            ?>
            <h3><?php _e('Adresse', \WooInvoicesMain::$text_domain); ?></h3>
            <table class="widefat">
                <tr>
                    <td colspan="2"><?php $this->add_meta_box_text_field(__('Firma', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_company', stripslashes($_data['receiver']['company'])); ?></td>
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('Vorname', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_first_name', stripslashes($_data['receiver']['first_name'])); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Nachname', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_last_name', stripslashes($_data['receiver']['last_name'])); ?></td>
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('Straße', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_street', stripslashes($_data['receiver']['street'])); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Hausnummer', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_street_no', stripslashes($_data['receiver']['street_no'])); ?></td>
                </tr>
                <tr>
                    <td colspan="2"><?php $this->add_meta_box_text_field(__('Adress Zusatz', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_care_of', stripslashes($_data['receiver']['care_of'])); ?></td>
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('PLZ', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_zip_code', stripslashes($_data['receiver']['zip_code'])); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Ort', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_city', stripslashes($_data['receiver']['city'])); ?></td>
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('Bundesland', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_state', stripslashes($_data['receiver']['state'])); ?></td>
                    <td><?php $this->add_meta_box_dropdown(__('Land', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_address_country', \WooInvoicesMain::$countries, stripslashes($_data['receiver']['country'])); ?></td>
                </tr>
            </table>

            <h3><?php _e('Versandeigenschaften', \WooInvoicesMain::$text_domain); ?></h3>
            <table class="widefat">
                <tr>
                    <td><?php $this->add_meta_box_dropdown(__('Carriers', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_carrier', self::$shipping_carriers, $_data['properties']['carrier']); ?></td>
                    <td><?php $this->add_meta_box_dropdown(__('Service', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_service', self::$shipping_services, $_data['properties']['service']); ?></td>
                    <?php if(!isset($_data['properties']['package_type'])) { $_data['properties']['package_type'] = null; } ?>
                    <td><?php $this->add_meta_box_dropdown(__('Typ (Nur bei Deutsche Post AG)', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_type', self::$shipping_package_types, $_data['properties']['package_type']); ?></td>
                    <td><?php $this->add_meta_box_checkbox(__('Bulk', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_bulk', 'true', $_data['properties']['package_bulk']); ?></td>
                    <?php if(!isset($this->shipping_settings['woo_invoices_shipping_sender_new_tab'])) {$this->shipping_settings['woo_invoices_shipping_sender_new_tab'] = '0';} ?>
                    <input type="hidden" name="woo_invoices_shipping_labels_properties_new_tab" id="woo_invoices_shipping_labels_properties_new_tab" value="<?php echo $this->shipping_settings['woo_invoices_shipping_sender_new_tab']; ?>" />
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('Breite (in cm)', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_width', $_data['properties']['package_width']); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Länge (in cm)', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_length', $_data['properties']['package_length']); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Höhe (in cm)', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_height', $_data['properties']['package_height']); ?></td>
                    <td>
                        <?php $this->add_meta_box_text_field(__('Versicherungsbetrag (in '.$_post_meta['_order_currency'][0].')', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_declared_value_amount', $_data['properties']['declared_value']['amount']); ?>
                        <input type="hidden" name="woo_invoices_shipping_labels_properties_declared_value_currency" id="woo_invoices_shipping_labels_properties_declared_value_currency" value="<?php echo $_post_meta['_order_currency'][0]; ?>" />
                    </td>
                </tr>
                <tr>
                    <td><?php $this->add_meta_box_text_field(__('Gewicht (in kg)', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_package_weight', $_data['properties']['package_weight']); ?></td>
                    <td><?php $this->add_meta_box_text_field(__('Referenz', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_reference_number', $_data['properties']['reference_number']); ?></td>
                    <?php if(!isset($_data['properties']['notification_email'])) { $_data['properties']['notification_email'] = null; } ?>
                    <td colspan="2"><?php $this->add_meta_box_text_field(__('Notification E-Mail', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping_labels_properties_notification_email', $_data['properties']['notification_email']); ?></td>
                </tr>
            </table>

            <div id="woo_invoices_shipping_presets" style="display:none;">
                <h3><?php _e('Presets', \WooInvoicesMain::$text_domain); ?></h3>

                <?php
                echo '<table class="wp-list-table widefat striped fixed posts">';
                echo '<thead>';
                echo '<tr>';

                $_first_col = true;
                foreach (\WooInvoicesMain::$preset_fields as $_field) {
                    echo '<th scope="col" id="shipping_preset_'.$_field['id'].'" class="manage-column column-shipping_preset_'.$_field['id'].' '.($_first_col ? 'column-primary' : '').'">'.$_field['name'].'</th>';
                    $_first_col = false;
                }

                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                if(isset($this->shipping_preset_settings)) {
                    if(!empty($this->shipping_preset_settings) && is_array($this->shipping_preset_settings)) {
                        foreach ($this->shipping_preset_settings as $_index => $_shipping_preset) {
                            $_first_col = true;

                            $_options = '';
                            foreach (self::$preset_fields as $_field) {
                                if ($_options != '') {
                                    $_options .= ', ';
                                }
                                $_options .= "'" . $_field['id'] . "': '" . $_shipping_preset['labels_properties_' . $_field['id']] . "'";
                            }

                            $_onclick = 'woo_invoices_shipping.use_shipping_preset({' . $_options . '});';

                            echo '<tr id="shipping-preset-' . $_index . '" class="iedit author-self level-0 shipping-preset-' . $_index . ' hentry">';
                            foreach (\WooInvoicesMain::$preset_fields as $_field) {
                                echo '<td class="column-shipping_preset_' . $_field['id'] . ' ' . ($_first_col ? 'has-row-actions column-primary' : '') . '" data-colname="' . $_field['name'] . '">';
                                $_value = \WooInvoicesMain::get_value_of_shipping_preset_type($_shipping_preset, $_field['id']);
                                echo $_value == '' ? '--' : $_value;
                                if ($_first_col) {
                                    $_first_col = false;
                                    echo '<div class="hidden" id="inline_shipping_preset_' . $_index . '">';
                                    foreach (\WooInvoicesMain::$preset_fields as $_field2) {
                                        $_value = \WooInvoicesMain::get_value_of_shipping_preset_type($_shipping_preset, $_field2['id']);
                                        if ($_value == '') {
                                            $_value = '--';
                                        }
                                        echo '<div class="shipping_preset_' . $_field2['id'] . '" id="shipping_preset_' . $_field2['id'] . '_' . $_index . '">' . $_value . '</div>';
                                    }
                                    echo '</div>';
                                    echo '<div class="row-actions">';
                                    echo '<span class="edit"><a href="javascript:;" target="_self" onclick="' . $_onclick . '" aria-label="' . __('verwenden', self::$text_domain) . '">' . __('verwenden', self::$text_domain) . '</a></span>';
                                    echo '</div>';
                                    echo '<button type="button" class="toggle-row woo_invoices_shipping_toggle_row"><span class="screen-reader-text">Mehr Details anzeigen</span></button>';
                                }
                                echo '</td>';
                            }
                            echo '</tr>';
                        }
                    }
                }

                echo '</tbody>';
                echo '</table>';
                ?>
            </div>
            <br>
            <?php
            $this->add_meta_box_js_button('woo_invoices_shipping_presets_display_button', __('Presets anzeigen', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping.show_shipping_presets();', 'default');
            $this->add_meta_box_js_button('woo_invoices_shipping_shipping_cost_request_button', __('Versandkosten abfragen', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping.shipping_cost_request();', 'default');
            $this->add_meta_box_js_button('woo_invoices_shipping_label_save_button', __('Speichern', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping.shipping_data_save();', 'default');
            $this->add_meta_box_js_button('woo_invoices_shipping_label_create_button', __('Versandmarke erstellen', \WooInvoicesMain::$text_domain), 'woo_invoices_shipping.label_create();', 'primary');
            ?>
            <br>
            <br>
            <hr>
            <ul id="woo_invoices_shipping_labels_label_list" class="clearfix">
                <?php
                $this->shipping_labels = $this->get_shipping_labels(get_the_ID());
                if (!empty($this->shipping_labels)) {
                    foreach ($this->shipping_labels as $_shipping_label) {
                        echo $this->render_shipping_label($_shipping_label);
                    }
                }
                ?>
            </ul>
            <?php
        }

        public function render_shipping_label($_data) {
            ob_start();
            ?>
            <li id="<?php echo 'woo_invoices_shipping_label_'.$_data['response']['id']; ?>">
                <div class="woo_invoices_shipping_label_delete"><a onclick="woo_invoices_shipping.delete_label('<?php echo $_data['response']['id']; ?>'); return false;" href="#"><span class="fa fa-times"></span></a></div>
                <h3><span class="fa fa-file-o"></span> Label: <?php echo $_data['response']['id']; ?></h3>
                <p>
                    #&nbsp;<?php echo (!empty($_data['response']['carrier_tracking_no']) ? $_data['response']['carrier_tracking_no'] : 'N.A.'); ?>
                    &nbsp;|&nbsp;<span class="fa fa-truck"></span>&nbsp;<a href="<?php echo $_data['response']['tracking_url']; ?>" target="_blank">Tracking</a>
                    &nbsp;|&nbsp;<span class="fa fa-file-pdf-o"></span>&nbsp;<?php if (!empty($_data['response']['label_url'])) { ?><a href="<?php echo $_data['response']['label_url']; ?>" target="_blank">Label</a><?php } else {echo 'N.A.';}?>
                    &nbsp;|&nbsp;<?php echo number_format((!empty($_data['response']['price']) ? $_data['response']['price'] : 0), 2, ',', '.'); ?>&nbsp;&euro;
                </p>
                <p>
                    <?php echo $_data['request']['properties']['carrier']; ?>
                    &nbsp;|&nbsp;<?php echo $_data['request']['properties']['package_width'].'cm&nbsp;x&nbsp;'.$_data['request']['properties']['package_length'].'cm&nbsp;x&nbsp;'.$_data['request']['properties']['package_height'].'cm'; ?>
                    &nbsp;|&nbsp;<?php echo $_data['request']['properties']['package_weight'].'kg'; ?>
                    <?php if ($_data['request']['properties']['package_bulk'] == 'true') { ?>&nbsp;|&nbsp;Bulk<?php } ?>
                    &nbsp;|&nbsp;Referenz:&nbsp;<?php echo $_data['request']['properties']['reference_number']; ?>
                </p>
            </li>
            <?php
            return ob_get_clean();
        }

        public function add_meta_box_text_field($_label, $_name, $_value) {
            ?>
            <div class="form-field form-field-wide">
                <label for="<?php echo $_name; ?>"><?php echo $_label; ?></label>
                <br />
                <input type="text" id="<?php echo $_name; ?>" name="<?php echo $_name; ?>" value="<?php echo $_value; ?>" />
            </div>
            <?php
        }

        public function add_meta_box_dropdown($_label, $_name, $_options, $_value) {
            ?>
            <div class="form-field form-field-wide">
                <label for="<?php echo $_name; ?>"><?php echo $_label; ?></label>
                <br />
                <select id="<?php echo $_name; ?>" name="<? echo $_name; ?>">
                    <?php foreach ($_options as $_option) { ?>
                        <option value="<?php echo $_option['value']; ?>"<?php if ($_option['value'] == $_value) {echo ' selected';} ?>><?php echo $_option['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php
        }

        public function add_meta_box_checkbox($_label, $_name, $_value, $_checked) {
            ?>
            <div class="form-field form-field-wide">
                <label for="<?php echo $_name; ?>"><?php echo $_label; ?></label>
                <br />
                <input type="checkbox" id="<?php echo $_name; ?>" name="<?php echo $_name; ?>" value="<?php echo $_value; ?>" <?php if ($_checked == 'true') {echo 'checked';} ?> />
            </div>
            <?php
        }

        public function add_meta_box_js_button($_name, $_text, $_click, $_class = 'default') {
            ?>
            <button type="button" id="<?php echo $_name; ?>" name="<?php echo $_name; ?>" class="button button-<?php echo $_class; ?>" onclick="<?php echo $_click; ?>">
                <?php echo $_text; ?>
                <span class="fa fa-check" id="<?php echo $_name; ?>_success" style="display:none;"></span>
                <span class="fa fa-times" id="<?php echo $_name; ?>_failure" style="display:none;"></span>
                <span class="fa fa-circle-o-notch fa-spin" id="<?php echo $_name; ?>_wait" style="display:none;"></span>
            </button>
            <?php
        }

        public function order_status_completed($_post_id) {
            $this->get_woo_invoices_invoice($_post_id);
        }

        public function cancel_invoice($_post_id) {
            $_woo_invoices_invoice_id = get_post_meta($_post_id, 'woo_invoices_invoice_id', true);

            if (!empty($_woo_invoices_invoice_id)) {
                $response = $this->api->cancel_invoice($_woo_invoices_invoice_id);
                update_post_meta($_post_id, 'woo_invoices_invoice_id', $response['invoice_id']);
            }

            $this->logging('Cancel Invoice (PostID: '.$_post_id.')');
        }

        public function get_woo_invoices_invoice($_post_id, $_delivery_note = false) {
            $_order = new WC_Order($_post_id);
            if($_order->get_total() > 0) {
                $_woo_invoices_invoice_id = get_post_meta($_post_id, 'woo_invoices_invoice_id', true);

                if (!empty($_woo_invoices_invoice_id)) {
                    $_woo_invoice = $this->api->get_invoice($_woo_invoices_invoice_id, $_delivery_note);
                    if (empty($_woo_invoice)) {
                        $_woo_invoices_invoice_id = null;
                        delete_post_meta($_post_id, 'woo_invoices_invoice_id');
                    }
                } else {
                    $_woo_invoice = null;
                }
                if (empty($_woo_invoices_invoice_id)) {
                    $_woo_invoice = $this->api->save_invoice($_order, $_delivery_note);
                    if (!empty($_woo_invoice['id'])) {
                        add_post_meta($_post_id, 'woo_invoices_invoice_id', $_woo_invoice['id'], true);
                        $this->logging('Create New Invoice (PostID: '.$_post_id.', InvoiceID: '.$_woo_invoice['id'].')');
                    }
                }

                return $_woo_invoice;
            } else {
                return false;
            }
        }

        public function error_woocommerce_licence() {
            $message = '<div class="error"><p>'.__('WooRechnungen ist noch nicht aktiviert. Bitte hinterlegen Sie einen validen Lizenzkey.', \WooInvoicesMain::$text_domain).' <a href="'.get_admin_url(null, 'admin.php?page=woo_invoices_settings').'">'.__('Hier konfigurieren', \WooInvoicesMain::$text_domain).'</a></p></div>';

            echo $message;
        }

        public function error_woocommerce() {
            $savedError = get_option('woo_invoices_plugin_error');

            $message = '<div class="error"><p>WooRechnung Error: ('.date('H:i d.m.Y', $savedError['time']).') '.$savedError['msg'].' <a href="'.get_admin_url(null, 'admin.php?page=woo_invoices_settings&remove_woorechnung_error=1').'" style="float: right;">[close]</a></p></div>';
            $this->logging('Error: '.$savedError['msg']);

            echo $message;
        }

        public function rate_woocommerce() {
            $countInvoices = get_option('woo_invoices_count_invoices');

            $message = '<div class="notice notice-info">';
            $message .= '<p><a href="'.get_admin_url(null, 'admin.php?page=woo_invoices_settings&remove_woorechnung_rate_msg=1').'" style="float: right;">'.__('[Nicht mehr anzeigen]', \WooInvoicesMain::$text_domain).'</a>'.str_replace('[count]', $countInvoices['count'], __('Vielen Dank das Sie WooRechnung nutzen! Inzwischen haben Sie [count] Rechnungen geschrieben und wir hoffen Sie sind zufrieden mit dem Dienst.<br />Sollten Sie Fragen oder Anmerkungen haben, freuen wir uns über eine Mail an <a href="mailto:info@woorechnung.com">info@woorechnung.com</a>.', \WooInvoicesMain::$text_domain)).'</p>';
            $message .= '<p>'.__('Desweiteren freuen wir uns, wenn Sie uns eine Bewertung hinterlassen:', \WooInvoicesMain::$text_domain).' <a href="https://wordpress.org/support/view/plugin-reviews/woorechnung" target="_blank">https://wordpress.org/support/view/plugin-reviews/woorechnung</a><br />'.__('Als kleines Dankeschön geben wir Ihnen einen <b>Gutschein</b> über 50% des ersten Monats oder 10% des ersten Jahres. Senden Sie einfach eine E-Mail mit einem Screenshot der Bewertung als Beweis an die oben stehende E-Mail.', \WooInvoicesMain::$text_domain).'</p>';
            $message .= '</div>';

            echo $message;
        }

        public function api_test() {

            $result = $this->api->api_test();

            if($result['api_key'] == trim($this->settings['woo_invoices_api_key'])) {
                $message = '<div class="notice notice-info">';
                    $message .= '<p>'.__('Der Test war erfolgreich', \WooInvoicesMain::$text_domain).'</p>';
                $message .= '</div>';
                $this->logging('WooRechnung API Test successfull');
            } else {
                $message = '<div class="error">';
                    $message .= '<p>'.__('Es konnte keine Verbindung hergestellt werden oder der Lizenzkey stimmt nicht mit dem unter woorechnung.com überein! Bitte kontaktiere support@woorechnung.com.', \WooInvoicesMain::$text_domain).'</p>';
                $message .= '</div>';
                $this->logging('WooRechnung API Test unsuccessfull');
            }

            echo $message;
        }

        public function add_invoice_backend_listing($order) {
            $data = array(
                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=download_invoice&order_id=' . $order->get_id()), 'download_invoice'),
                'img' => self::$plugin_url.'images/invoice.png',
                'alt' => 'PDF Invoice',
            );
            $data_new = array(
                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=download_invoice&order_id=' . $order->get_id()), 'download_invoice'),
                'img' => self::$plugin_url.'images/invoice-new.png',
                'alt' => 'PDF Invoice',
            );
            $invoices_invoices_id = get_post_meta($order->get_id(), 'woo_invoices_invoice_id', true);
            if(!empty($invoices_invoices_id)) {
                ?>
                <a href="<?php echo $data['url']; ?>" class="button tips woorechnung"
                   alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
                    <img src="<?php echo $data['img']; ?>" alt="<?php echo $data['alt']; ?>" width="16">
                </a>
                <?php
            } else if($order->get_total() > 0) {
                ?>
                <a href="<?php echo $data_new['url']; ?>" class="button tips woorechnung"
                   alt="<?php echo $data_new['alt']; ?>" data-tip="<?php echo $data_new['alt']; ?>">
                    <img src="<?php echo $data_new['img']; ?>" alt="<?php echo $data_new['alt']; ?>" width="16">
                </a>
                <?php
            }
        }

        public function customer_account_add_invoice_link($actions, $order) {
            $download_url = wp_nonce_url(admin_url('admin-ajax.php?action=download_invoice&order_id=' . $order->get_id() . '&my-account=1'), 'download_invoice');

            if(get_post_meta($order->get_id(), 'woo_invoices_invoice_id', true)) {
                $actions['name'] = array(
                    'url'  => $download_url,
                    'name' => 'Rechnung anzeigen'
                );
            }

            return $actions;
        }

        function send_invoice_email_attach( $attachments, $status , $order ) {
            $allowed_status = array('wc-'.$this->invoice_settings['woo_sending_status']);

            if( isset($status) && in_array($order->post_status, $allowed_status) && $order->get_total() > 0 ) {

                sleep(2);

                $invoice_id = get_post_meta($order->get_id(), 'woo_invoices_invoice_id', true);
                if(!empty($invoice_id)) {
                    $invoice = $this->api->get_invoice($invoice_id);
                } else {
                    if(!isset($this->invoice_settings['woo_delivery_note'])) {
                        $this->invoice_settings['woo_delivery_note'] = 0;
                    }
                    if($this->invoice_settings['woo_delivery_note'] > 0) {
                        $delivery_note = true;
                    } else {
                        $delivery_note = false;
                    }
                    $invoice = $this->get_woo_invoices_invoice($order->get_id(), $delivery_note);
                }

                if(!empty($invoice['document_url'])) {
                    $folder = plugin_dir_path( __FILE__ ).'tmp/';
                    if(!empty($invoice['number'])) {
                        $filename = $folder.$invoice['number'].'.pdf';
                    } else {
                        $filename = $folder.md5($order->get_id().time()).'.pdf';
                    }
                    $document = wp_remote_get($invoice['document_url']);
                    $document_body = $document['body'];

                    if(!is_dir($folder)) {
                        mkdir($folder);
                    }
                    file_put_contents($filename, $document_body);
                    $attachments[] = $filename;

                    $this->logging('Send invoice mail (OrderID: '.$order->get_id().', InvoiceID: '.$invoice_id.')');
                } else if(!empty($invoice['document_body'])) {
                    $folder = plugin_dir_path( __FILE__ ).'tmp/';
                    if(!empty($invoice['number'])) {
                        $filename = $folder.$invoice['number'].'.pdf';
                    } else {
                        $filename = $folder.md5($order->get_id().time()).'.pdf';
                    }
                    $document_body = base64_decode($invoice['document_body']);

                    if(!is_dir($folder)) {
                        mkdir($folder);
                    }
                    file_put_contents($filename, $document_body);
                    $attachments[] = $filename;

                    $this->logging('Send invoice mail (OrderID: '.$order->get_id().', InvoiceID: '.$invoice_id.')');
                }
            }
            return $attachments;
        }

        function send_invoice_email($_post_id) {
            $order = new WC_Order($_post_id);

            if (!empty($order)) {
                if ($order->get_total() > 0) {

                    sleep(2);

                    $attachments = null;

                    $invoice_id = get_post_meta($order->get_id(), 'woo_invoices_invoice_id', true);
                    if (!empty($invoice_id)) {
                        $invoice = $this->api->get_invoice($invoice_id);
                    } else {
                        if(!isset($this->invoice_settings['woo_delivery_note'])) {
                            $this->invoice_settings['woo_delivery_note'] = 0;
                        }
                        if($this->invoice_settings['woo_delivery_note'] > 0) {
                            $delivery_note = true;
                        } else {
                            $delivery_note = false;
                        }
                        $invoice = $this->get_woo_invoices_invoice($order->get_id(), $delivery_note);
                    }

                    if (!empty($invoice['document_url'])) {
                        $folder = realpath(get_temp_dir()).'/';
                        if(!empty($invoice['number'])) {
                            $filename = $folder.$invoice['number'].'.pdf';
                        } else {
                            $filename = $folder.md5($order->get_id().time()).'.pdf';
                        }
                        $document = wp_remote_get($invoice['document_url']);
                        $document_body = $document['body'];

                        if (!is_dir($folder)) {
                            mkdir($folder);
                        }
                        file_put_contents($filename, $document_body);
                        $attachments[] = $filename;
                    }
                    else if (!empty($invoice['document_body'])) {
                        $folder = realpath(get_temp_dir()).'/';
                        if(!empty($invoice['number'])) {
                            $filename = $folder.$invoice['number'].'.pdf';
                        } else {
                            $filename = $folder.md5($order->get_id().time()).'.pdf';
                        }
                        $document_body = base64_decode($invoice['document_body']);

                        if (!is_dir($folder)) {
                            mkdir($folder);
                        }
                        file_put_contents($filename, $document_body);
                        $attachments[] = $filename;
                    }

                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8'
                    );

                    $search = array(
                        '%order_id%',
                        '%first_name%',
                        '%last_name%',
                        '%page_title%'
                    );

                    $replace = array(
                        $order->get_id(),
                        $order->billing_first_name,
                        $order->billing_last_name,
                        wp_get_document_title()
                    );

                    $subject = str_replace($search, $replace, $this->invoice_mail_settings['woo_invoice_mail_subject']);
                    $message = str_replace($search, $replace, $this->invoice_mail_settings['woo_invoice_mail_body_html']);
                    $this->mail_alt_body = str_replace($search, $replace, $this->invoice_mail_settings['woo_invoice_mail_body_text']);

                    wp_mail($order->billing_email, $subject, $message, $headers, $attachments);

                    $this->logging('Send invoice mail (PostID: '.$_post_id.', InvoiceID: '.$invoice_id.')');
                }
            }
        }

        private $mail_alt_body = null;

        public function mailer_config(\PHPMailer $phpmailer) {
            if (!empty($this->mail_alt_body)) {
                $phpmailer->AltBody = $this->mail_alt_body;
                $this->mail_alt_body = null;
            }
        }

        public function download_invoice_ajax()
        {
            // Check the nonce
            if (empty($_GET['action']) || !is_user_logged_in() || !check_admin_referer($_GET['action']) || empty($_GET['order_id']) || !current_user_can('manage_woocommerce_orders') && !current_user_can('edit_shop_orders') && !isset($_GET['my-account'])) {
                wp_die('You are not allowed to view this invoice.');
            }

            $order_id = $_GET['order_id'];
            // Get user_id of order
            $this->order = new WC_Order ($order_id);

            try {
                if ($this->order->get_total() > 0) {
                    $invoice_id = get_post_meta($this->order->get_id(), 'woo_invoices_invoice_id', true);
                    if (empty($invoice_id)) {
                        $invoice_id = get_post_meta($this->order->get_id(), 'fastbill_invoice_id', true);
                    }
                    if (!empty($invoice_id)) {
                        if(!isset($this->invoice_settings['woo_delivery_note'])) {
                            $this->invoice_settings['woo_delivery_note'] = 0;
                        }
                        if($this->invoice_settings['woo_delivery_note'] > 0) {
                            $delivery_note = true;
                        } else {
                            $delivery_note = false;
                        }
                        $invoice = $this->api->get_invoice($invoice_id, $delivery_note);
                        $invoice_id = $invoice['invoice_id'];
                    } else {
                        $invoice = null;
                    }

                    if (empty($invoice)) {
                        if(!isset($this->invoice_settings['woo_delivery_note'])) {
                            $this->invoice_settings['woo_delivery_note'] = 0;
                        }
                        if($this->invoice_settings['woo_delivery_note'] > 0) {
                            $delivery_note = true;
                        } else {
                            $delivery_note = false;
                        }
                        $invoice = $this->get_woo_invoices_invoice($order_id, $delivery_note);
                    }

                    if (empty($invoice['document_body'])) {
                        $document = wp_remote_get($invoice['document_url']);
                    } else {
                        $document_body = base64_decode($invoice['document_body']);
                    }
                    if (!isset($document->errors)) {
                        if (empty($document_body)) {
                            $document_body = $document['body'];
                        }

                        // get the invoice and send it to the browser
                        if(!empty($invoice['number'])) {
                            $filename = $invoice['number'] . ".pdf";
                        } else {
                            $filename = $this->order->get_id() . ".pdf";
                        }
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . $filename . '"');
                        header('Content-Transfer-Encoding: binary');
                        header('Connection: Keep-Alive');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');

                        // output PDF data
                        echo $document_body;

                        $this->logging('Download Invoice (OrderID: '.$order_id.', InvoiceID: '.$invoice_id.')');

                        exit;
                    } else {
                        echo '<p>'.__('Die Rechnung, die Sie aufrufen möchte, liegt leider nicht mehr vor. Bitte kontaktieren Sie Ihren Shobbetreiber. Wenn Sie selbst Administrator der Seite sind, senden Sie bitte den Inhalt der <b>DEBUG Konsole</b> an support@woorechnung.com', \WooInvoicesMain::$text_domain).'</p>';
                        echo '<p>';
                            echo '<b>DEBUG Konsole:</b><br />';
                            echo '<textarea id="debug_log" style="width: 100%; width: 450px; height: 300px;">';
                            $requestLog = $this->api->getRequestLog();
                            foreach ($requestLog AS $log) {
                                print_r($log);
                            }
                            echo '</textarea>';
                        echo '</p>';
                        $this->logging('Download Invoice Error: '.print_r($requestLog, true).' (OrderID: '.$order_id.', InvoiceID: '.$invoice_id.')');
                        exit;
                    }
                } else {
                    echo '<p>'.__('Die Rechnung konnte nicht geladen werden. Bitte senden Sie uns den Inhalt aus der <b>DEBUG Konsole</b> an support@woorechnung.com.', \WooInvoicesMain::$text_domain).'</p>';
                    echo '<p>';
                        echo '<b>DEBUG Konsole:</b><br />';
                        echo '<textarea id="debug_log" style="width: 100%; width: 450px; height: 300px;">';
                        $requestLog = $this->api->getRequestLog();
                        foreach ($requestLog AS $log) {
                            print_r($log);
                        }
                        echo '</textarea>';
                    echo '</p>';
                    $this->logging('Download Invoice Error: '.print_r($requestLog, true).' (OrderID: '.$order_id.')');
                    exit;
                }
            } catch (Exception $e) {
                echo '<p>'.__('Die Rechnung konnte nicht geladen werden. Bitte senden Sie uns den Inhalt aus der <b>DEBUG Konsole</b> an support@woorechnung.com.', \WooInvoicesMain::$text_domain).'</p>';
                echo '<p>';
                    echo '<b>DEBUG Konsole:</b><br />';
                    echo '<textarea id="debug_log" style="width: 100%; width: 450px; height: 300px;">';
                    print_r($e);
                    echo '</textarea>';
                echo '</p>';
                $this->logging('Download Invoice Error: '.print_r($e, true).' (OrderID: '.$order_id.')');
                exit;
            }
        }

        public function add_pdf_export_to_order_list() {

            global $post_type;

            if($post_type == 'shop_order') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery('<option>').val('woo_invoice_export').text('<?php _e('Rechnungen exportieren', \WooInvoicesMain::$text_domain); ?>').appendTo("select[name='action']");
                        jQuery('<option>').val('woo_invoice_export').text('<?php _e('Rechnungen exportieren', \WooInvoicesMain::$text_domain); ?>').appendTo("select[name='action2']");
                    });
                </script>
                <?php
            }
        }

        public function pdf_bulk_export() {
            global $typenow;
            $post_type = $typenow;

            if($post_type == 'shop_order') {

                // get the action
                $wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
                $action = $wp_list_table->current_action();

                $allowed_actions = array("woo_invoice_export");
                if(!in_array($action, $allowed_actions)) return;

                // security check
                check_admin_referer('bulk-posts');

                // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
                if(isset($_REQUEST['post'])) {
                    $post_ids = array_map('intval', $_REQUEST['post']);
                }

                if(empty($post_ids)) return;

                switch($action) {
                    case 'woo_invoice_export':

                        $folder = realpath(get_temp_dir()).'/';
                        if(!is_dir($folder)) {
                            mkdir($folder);
                        }
                        $filename = $folder.md5(time()).'.zip';

                        $zip = new \ZipArchive();
                        if ($zip->open($filename, \ZipArchive::CREATE) === true) {

                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="Rechnungen_'.date('d_m_Y').'.zip"');
                            header('Content-Transfer-Encoding: binary');
                            header('Connection: Keep-Alive');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                            header('Pragma: public');

                            foreach( $post_ids as $post_id ) {

                                if ( !$this->perform_export($zip, $post_id) ) {
                                    wp_die(__('Fehler beim exportieren der Rechnungen!'));
                                }

                            }
                            $zip->close();

                            echo file_get_contents($filename);
                            @unlink($filename);
                            exit();
                        }

                        break;

                    default: return;
                }

                $this->logging('Bulk invoice download ('.print_r($post_ids,true).')');
            }
        }

        public function perform_export($zip, $post_id) {
            if(!isset($this->invoice_settings['woo_delivery_note'])) {
                $this->invoice_settings['woo_delivery_note'] = 0;
            }
            if($this->invoice_settings['woo_delivery_note'] > 0) {
                $delivery_note = true;
            } else {
                $delivery_note = false;
            }
            $invoice = $this->get_woo_invoices_invoice($post_id, $delivery_note);

            if(!empty($invoice['number'])) {
                $filename = $invoice['number'];
            } else {
                $filename = $post_id;
            }

            if(empty($invoice['document_body'])) {
                $document = wp_remote_get($invoice['document_url']);
                if (!isset($document->errors)) {
                    $zip->addFromString($filename.'.pdf', $document['body']);
                }

            } else if (!empty($invoice['document_body'])) {
                $zip->addFromString($filename.'.pdf', base64_decode($invoice['document_body']));
            }
            return true;
        }

        public function logging($_content) {
            $fileName = realpath(get_temp_dir()).'/woorechnung_log.txt';
            if(is_writable($fileName)) {
                if(!file_exists($fileName)) {
                    if(file_put_contents($fileName, ' '.PHP_EOL, LOCK_EX) === false) {
                        return false;
                    }
                }
            } else {
                return false;
            }
            $max = 200;
            $_content = date('d.m.Y H:i:s').' - '.$_content;

            $file = array_filter(array_map("trim", file($fileName)));

            // Make Sure you always have maximum number of lines
            $file = array_slice($file, 0, $max);

            // Remove any extra line
            count($file) >= $max and array_shift($file);

            // Add new Line
            array_unshift($file, $_content);

            // Save Result
            file_put_contents($fileName, implode(PHP_EOL, array_filter($file)), LOCK_EX);
        }
    }
}

$GLOBALS['woorechnung'] = new WooInvoicesMain();