<?php

/* ******************************************** */
/*   Copyright: ZWEISCHNEIDER DIGITALSCHMIEDE   */
/*         http://www.zweischneider.de          */
/* ******************************************** */

namespace WooInvoices;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOO_INVOICES_ACCOUNT_URL', 'https://woorechnung.com');

if (!class_exists('\WooInvoices\Backend')) {
    class Backend {

        // grouping fields...
        public static $settings_page_url_param = 'woo_invoices_settings';
        public static $settings_plugin_section_id = 'woo_invoices_plugin_settings_id';

        // helper fields...
        private $settings_errors = array();
        private $shipping_preset_settings = array();
        private $shipping_preset_id = null;
        private $shipping_preset_delete_id = null;
        private $shipping_presets_referer_url = '';
        private $settings_updated = null;
        private $active_tab = null;

        // tabs fields...
        private $settings_tabs = array('general', 'invoice', 'invoice_mail', 'shipping', 'shipping_presets', 'log');
        private $settings_tabs_names = array();

        public function __construct() {
            $this->init();
        }

        // init methods...
        private function init() {

            $this->settings_tabs_names = array(
                'general' => __('Allgemein', \WooInvoicesMain::$text_domain),
                'invoice' => __('Rechnungen', \WooInvoicesMain::$text_domain),
                'invoice_mail' => __('Rechnungs E-Mail', \WooInvoicesMain::$text_domain),
                'shipping' => __('Shipcloud', \WooInvoicesMain::$text_domain),
                'shipping_presets' => __('Shipcloud Vorlagen', \WooInvoicesMain::$text_domain),
                'log' => __('Log', \WooInvoicesMain::$text_domain),
            );

            $this->add_actions();
        }

        // actions methods...
        private function add_actions() {
            try {
                add_action('admin_head', array(&$this, 'admin_head'));
                add_action('admin_footer', array(&$this, 'admin_footer'));
                add_action('admin_menu', array(&$this, 'settings_menu'));
                add_action('admin_init', array(&$this, 'settings_init'));
                add_action('admin_notices', array(&$this, 'settings_notices'));
            }
            catch(\Exception $_exception) {
                echo $_exception;
            }
        }

        public function admin_head() {
        }

        public function admin_footer() {
        }

        public function settings_menu() {
            add_submenu_page(
                'woocommerce',
                __('WooRechnung', \WooInvoicesMain::$text_domain),
                __('WooRechnung', \WooInvoicesMain::$text_domain),
                'manage_woocommerce',
                self::$settings_page_url_param,
                array($this, 'settings_page')
            );
        }

        // init methods...
        public function settings_init() {

            // general...
            register_setting(
                \WooInvoicesMain::$settings_group,
                \WooInvoicesMain::$settings_page_name,
                array($this, 'sanitize_callback')
            );

            add_settings_section(
                self::$settings_plugin_section_id,
                __('Grundeinstellungen', \WooInvoicesMain::$text_domain),
                array($this, 'settings_section_callback'),
                \WooInvoicesMain::$settings_page_name
            );

            $this->add_settings_field('woo_invoices_api_key', __('Lizenzkey', \WooInvoicesMain::$text_domain), 'general', 'settings_text_callback', __('Lizenzkey für WooRechnung. Den Lizenzkey findest Du unter "Installation & Lizenzkey" auf: ', \WooInvoicesMain::$text_domain).'<a href="'.WOO_INVOICES_ACCOUNT_URL.'" target="_blank">'.WOO_INVOICES_ACCOUNT_URL.'</a>');
            $this->add_settings_field('woo_invoices_api_check', __('API-Test', \WooInvoicesMain::$text_domain), 'general', 'settings_check_api', __('Testet ob eine Verbindung mit WooRechnung und dem Lizenzkey hergestellt werden kann.', \WooInvoicesMain::$text_domain));

            // invoice...
            register_setting(
                \WooInvoicesMain::$settings_group.'_invoice',
                \WooInvoicesMain::$settings_page_name.'_invoice',
                array($this, 'sanitize_callback')
            );

            add_settings_section(
                self::$settings_plugin_section_id.'_invoice',
                __('Rechnungseinstellungen', \WooInvoicesMain::$text_domain),
                array($this, 'settings_section_callback'),
                \WooInvoicesMain::$settings_page_name.'_invoice'
            );

            $this->add_settings_field('woo_invoices_deactivate', __('Deaktivieren?', \WooInvoicesMain::$text_domain), 'invoice', 'settings_checkbox_callback', __('Rechnungserstellung deaktivieren.', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoices_status', __('Status', \WooInvoicesMain::$text_domain), 'invoice', 'settings_select_status_callback', __('Status bei dem die Rechnung erstellt werden soll', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_send_invoices', __('Rechnung senden', \WooInvoicesMain::$text_domain), 'invoice', 'settings_select_send_callback', __('Rechnung per E-Mail versenden?', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_send_invoices_type', __('Rechnung an bestehende Mail anhängen?', \WooInvoicesMain::$text_domain), 'invoice', 'settings_checkbox_callback', __('Soll die E-Mail an eine bestehende WooCommerce E-Mail angehangen werden? Bitte beachte dass zu deinem gewählten Status auch wirklich eine E-Mail von WooCommerce verschickt wird!', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_sending_status', __('Rechnung senden Status', \WooInvoicesMain::$text_domain), 'invoice', 'settings_select_status_callback', __('Status bei dem die Rechnung per E-Mail versendet wird (Der Status muss ein späterer sein als bei der Erzeugung!)', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoice_date', __('Rechnungsdatum', \WooInvoicesMain::$text_domain), 'invoice', 'settings_select_invoice_date_callback', __('Wähle mit welchem Datum die Rechnung erzeugt werden soll.', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoice_shipping_code', __('Versandkosten Artikelnummer', \WooInvoicesMain::$text_domain), 'invoice', 'settings_text_callback', __('Artikelnummer der Versandkosten', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_line_description', __('Produktbeschreibung', \WooInvoicesMain::$text_domain), 'invoice', 'settings_select_description_callback', __('Wähle ob und welche Beschreibung pro Position/Produkt gespeichert werden soll.', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_order_number_prefix', 'Bestellnummer-Prefix', 'invoice', 'settings_text_callback', __('Prefix für die Bestellnummer', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_order_number_suffix', 'Bestellnummer-Suffix', 'invoice', 'settings_text_callback', __('Suffix für die Bestellnummer', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_delivery_note', 'Lieferscheine erzeugen?', 'invoice', 'settings_checkbox_callback', __('Aktiviert die Lieferschein-Funktion (Aktuell nur mit Debitoor Möglich)', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_payment_methods', 'Rechnungsstatus', 'invoice', 'settings_payment_methods_callback', __('Status pro Zahlungsart', \WooInvoicesMain::$text_domain));

            // invoice_mail...
            register_setting(
                \WooInvoicesMain::$settings_group.'_invoice_mail',
                \WooInvoicesMain::$settings_page_name.'_invoice_mail',
                array($this, 'sanitize_callback')
            );

            add_settings_section(
                self::$settings_plugin_section_id.'_invoice_mail',
                __('Rechnungs E-Mail', \WooInvoicesMain::$text_domain),
                array($this, 'settings_section_callback'),
                \WooInvoicesMain::$settings_page_name.'_invoice_mail'
            );

            $this->add_settings_field('woo_invoice_mail_subject', __('Betreff', \WooInvoicesMain::$text_domain), 'invoice_mail', 'settings_text_callback', __('Mögliche Variablen: %order_id% | %first_name% | %last_name% | %page_title%', \WooInvoicesMain::$text_domain), 'Ihre Rechnung zu Ihrer Bestellung %order_id%');
            $this->add_settings_field('woo_invoice_mail_body_text', __('Text Inhalt', \WooInvoicesMain::$text_domain), 'invoice_mail', 'settings_textarea_callback', __('Mögliche Variablen: %order_id% | %first_name% | %last_name% | %page_title%', \WooInvoicesMain::$text_domain), "Guten Tag %first_name% %last_name%,\n\nhiermit senden wir Ihnen die Rechnung zu Ihrer Bestellung  %order_id%.\n\nMit freundlichen grüßen,\n\n%page_title%");
            $this->add_settings_field('woo_invoice_mail_body_html', __('HTML Inhalt', \WooInvoicesMain::$text_domain), 'invoice_mail', 'settings_wp_editor_callback', __('Mögliche Variablen: %order_id% | %first_name% | %last_name% | %page_title%', \WooInvoicesMain::$text_domain), "Guten Tag %first_name% %last_name%,<br><br>hiermit senden wir Ihnen die Rechnung zu Ihrer Bestellung  %order_id%.<br><br>Mit freundlichen grüßen,<br><br>%page_title%");

            // shipping...
            register_setting(
                \WooInvoicesMain::$settings_group.'_shipping',
                \WooInvoicesMain::$settings_page_name.'_shipping',
                array($this, 'sanitize_callback')
            );

            add_settings_section(
                self::$settings_plugin_section_id.'_shipping',
                __('Shipcloudeinstellungen', \WooInvoicesMain::$text_domain),
                array($this, 'settings_section_callback'),
                \WooInvoicesMain::$settings_page_name.'_shipping'
            );

            $this->add_settings_field('woo_invoices_shipping_activate', __('Aktivieren?', \WooInvoicesMain::$text_domain), 'shipping', 'settings_checkbox_callback', __('Wenn Du shipcloud nutzen möchtest, fülle bitte die unten stehenden Felder mit Deiner Adresse (Rücksendeadresse).', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoices_shipping_sender_company', __('Firma', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_first_name', __('Vorname', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_last_name', __('Nachname', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_street', __('Straße', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_street_no', __('Haus-Nr.', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_care_of', __('Adress Zusatz', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_zip_code', __('PLZ', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_city', __('Ort', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_state', __('Bundesland', \WooInvoicesMain::$text_domain), 'shipping', 'settings_text_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_country', __('Land', \WooInvoicesMain::$text_domain), 'shipping', 'settings_select_country_callback', '');
            $this->add_settings_field('woo_invoices_shipping_sender_empty_notification_email', __('Notification E-Mail leer lassen', \WooInvoicesMain::$text_domain), 'shipping', 'settings_checkbox_callback', __('Durch anhaken wird die Notification E-Mail leer gelassen', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoices_shipping_sender_new_tab', __('Neuer Tab ', \WooInvoicesMain::$text_domain), 'shipping', 'settings_checkbox_callback', __('Durch anhaken wird das Versandlabel nach dem erfolgreichen Erstellen automatisch in einem neuen Tab geöffnet', \WooInvoicesMain::$text_domain));
            $this->add_settings_field('woo_invoices_shipping_sender_default_preset', __('Default Preset', \WooInvoicesMain::$text_domain), 'shipping', 'settings_select_default_preset_callback', '', 'Kein Default Preset');

            // shipping presets...
            $this->shipping_preset_delete_id = isset($_REQUEST['delete_preset_id']) ? $_REQUEST['delete_preset_id'] : null;
            $this->shipping_preset_id = isset($_REQUEST['preset_id']) ? ($this->settings_updated == true) ? null : $_REQUEST['preset_id'] : null;
            $this->shipping_presets_referer_url = 'admin.php?page='.self::$settings_page_url_param.'&tab=shipping_presets';

            register_setting(
                \WooInvoicesMain::$settings_group.'_shipping_presets',
                \WooInvoicesMain::$settings_page_name.'_shipping_presets',
                array($this, 'sanitize_callback')
            );

            add_settings_section(
                self::$settings_plugin_section_id.'_shipping_presets',
                __('shipcloud Presets', \WooInvoicesMain::$text_domain).(!isset($this->shipping_preset_id) ? ' <a href="'.$this->shipping_presets_referer_url.'&preset_id=-1" class="page-title-action" target="_self">'.__('Preset hinzufügen', \WooInvoicesMain::$text_domain).'</a>' : ''),
                array($this, 'settings_section_callback'),
                \WooInvoicesMain::$settings_page_name.'_shipping_presets'
            );

            $this->settings_updated = empty($_REQUEST['settings_updated']) ? null : (bool)$_REQUEST['settings_updated'];
            $this->shipping_preset_settings = get_option(\WooInvoicesMain::$settings_page_name.'_shipping_presets');

            if (isset($this->shipping_preset_id)) {

                $_defaults = array();
                if (((int)$this->shipping_preset_id >= 0) && (!empty($this->shipping_preset_settings))) {
                    if (count($this->shipping_preset_settings) > $this->shipping_preset_id) {
                        $_defaults = $this->shipping_preset_settings[$this->shipping_preset_id];
                    }
                }

                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_carrier', __('Anbieter', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_select_callback', '', (isset($_defaults['labels_properties_carrier']) ? $_defaults['labels_properties_carrier'] : null), \WooInvoicesMain::$shipping_carriers);
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_service', __('Service', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_select_callback', '', (isset($_defaults['labels_properties_service']) ? $_defaults['labels_properties_service'] : null), \WooInvoicesMain::$shipping_services);
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_package_type', __('Typ', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_select_callback', 'Nur bei Deutsche Post AG', (isset($_defaults['labels_properties_package_type']) ? $_defaults['labels_properties_package_type'] : null), \WooInvoicesMain::$shipping_package_types);
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_package_width', __('Breite (in cm)', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_text_callback', '', (isset($_defaults['labels_properties_package_width']) ? $_defaults['labels_properties_package_width'] : null));
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_package_length', __('Länge (in cm)', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_text_callback', '', (isset($_defaults['labels_properties_package_length']) ? $_defaults['labels_properties_package_length'] : null));
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_package_height', __('Höhe (in cm)', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_text_callback', '', (isset($_defaults['labels_properties_package_height']) ? $_defaults['labels_properties_package_height'] : null));
                $this->add_settings_field('woo_invoices_shipping_preset_labels_properties_package_weight', __('Gewicht (in kg)', \WooInvoicesMain::$text_domain), 'shipping_presets', 'settings_text_callback', '', (isset($_defaults['labels_properties_package_weight']) ? $_defaults['labels_properties_package_weight'] : null));
            }

            // log...
            register_setting(
                    \WooInvoicesMain::$settings_group.'_log',
                    \WooInvoicesMain::$settings_page_name.'_log',
                    array($this, 'sanitize_callback')
            );

            add_settings_section(
                    self::$settings_plugin_section_id.'_log',
                    __('Log', \WooInvoicesMain::$text_domain),
                    array($this, 'settings_section_callback'),
                    \WooInvoicesMain::$settings_page_name.'_log'
            );

            $this->add_settings_field('woo_invoices_log_textarea', __('Log', \WooInvoicesMain::$text_domain), 'log', 'settings_log_callback', __('Hier findest du die letzten 200 Einträge aus deinem WooRechnung Log.', \WooInvoicesMain::$text_domain));

        }

        public function add_hidden_inputs() {

            $_sSubmitText = __('Änderungen übernehmen', \WooInvoicesMain::$text_domain);
            $_hidden_inputs = array();

            switch ($this->active_tab) {
                case 'shipping_presets':

                    $_sSubmitText = isset($this->shipping_preset_id) ? __('Speichern', \WooInvoicesMain::$text_domain) : '';

                    $_hidden_inputs = array(
                        array('id' => 'preset_id', 'name' => 'preset_id', 'value' => $this->shipping_preset_id)
                    );
                    break;
            }

            foreach ($_hidden_inputs as $_hidden_input) {
                echo '<input type="hidden" id="'.\WooInvoicesMain::$settings_page_name.'_'.$_hidden_input['id'].'" name="'.\WooInvoicesMain::$settings_page_name.'_'.$_hidden_input['name'].'" value="'.$_hidden_input['value'].'" />';
            }

            return $_sSubmitText;
        }

        public function add_settings_field($_id, $_name, $_type, $_callback = 'settings_text_callback', $_description = '', $_default = null, $_options = null) {

            if ($_type == 'general') {$_type = '';}

            add_settings_field(
                $_id,
                $_name,
                array($this, $_callback),
                \WooInvoicesMain::$settings_page_name.(!empty($_type) ? '_'.$_type : ''),
                self::$settings_plugin_section_id.(!empty($_type) ? '_'.$_type : ''),
                array(
                    'page' => \WooInvoicesMain::$settings_page_name.(!empty($_type) ? '_'.$_type : ''),
                    'id' => $_id,
                    'description' => $_description,
                    'default' => $_default,
                    'options' => $_options
                )
            );
        }

        public function settings_notices() {
            if($_REQUEST['page'] === 'woo_invoices_settings') {
                settings_errors();
            }
        }

        public function sanitize_callback($_data) {

            $_notice_message = null;
            $_notice_type = null;

            if (array_key_exists('woo_invoices_shipping_preset_labels_properties_carrier', $_data)) {

                $_data2 = array();
                foreach ($_data as $_key => $_value) {
                    $_data2[str_replace('woo_invoices_shipping_preset_', '', $_key)] = $_value;
                }

                if ((int)$_POST[\WooInvoicesMain::$settings_page_name.'_preset_id'] == -1) { // create
                    $this->shipping_preset_settings[] = $_data2;
                }
                else { // update
                    $this->shipping_preset_settings[(int)$_POST[\WooInvoicesMain::$settings_page_name.'_preset_id']] = $_data2;
                }
                $_data = $this->shipping_preset_settings;
            }

            if (!empty($this->settings_errors)) {
                $_notice_message = '';
                foreach ($this->settings_errors as $_error) {
                    $_notice_message .= __($_error, \WooInvoicesMain::$text_domain).'<br>';
                }
                $_notice_type = 'error';
            }
            else {
                $_notice_message = __('Ihre Einstellungen wurden erfolgreich gespeichert.', \WooInvoicesMain::$text_domain);
                $_notice_type = 'updated';
            }

            add_settings_error(
                'woo_invoices_settings_messages_id',
                esc_attr('settings_updated'),
                $_notice_message,
                $_notice_type
            );

            return $_data;
        }

        public function settings_section_callback() {

            switch ($this->active_tab) {
                case 'shipping_presets':

                    if (!isset($this->shipping_preset_id)) {
                        echo '<table class="wp-list-table widefat striped fixed posts">';
                        echo '<thead>';
                        echo '<tr>';

                        $_first_col = true;
                        foreach (\WooInvoicesMain::$preset_fields as $_field) {
                            echo '<th scope="col" id="'.$_field['id'].'" class="manage-column column-'.$_field['id'].' '.($_first_col ? 'column-primary' : '').'">'.$_field['name'].'</th>';
                            $_first_col = false;
                        }

                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody id="the-list">';

                        if(!empty($this->shipping_preset_settings)) {
                            foreach ($this->shipping_preset_settings as $_index => $_shipping_preset) {
                                echo '<tr id="shipping-preset-' . $_index . '" class="iedit author-self level-0 shipping-preset-' . $_index . ' hentry">';
                                $_first_col = true;
                                foreach (\WooInvoicesMain::$preset_fields as $_field) {
                                    echo '<td class="column-' . $_field['id'] . ' ' . ($_first_col ? 'has-row-actions column-primary' : '') . '" data-colname="' . $_field['name'] . '">';
                                    echo \WooInvoicesMain::get_value_of_shipping_preset_type($_shipping_preset, $_field['id']);
                                    if ($_first_col) {
                                        $_first_col = false;
                                        echo '<div class="hidden" id="inline_' . $_index . '">';
                                        foreach (\WooInvoicesMain::$preset_fields as $_field2) {
                                            echo '<div class="' . $_field2['id'] . '" id="' . $_field2['id'] . '_' . $_index . '">';
                                            echo \WooInvoicesMain::get_value_of_shipping_preset_type($_shipping_preset, $_field2['id']);
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                        echo '<div class="row-actions">';
                                        echo '<span class="edit"><a href="' . $this->shipping_presets_referer_url . '&preset_id=' . $_index . '" target="_self" aria-label="' . __('Bearbeiten', \WooInvoicesMain::$text_domain) . '">' . __('Bearbeiten', \WooInvoicesMain::$text_domain) . '</a> | </span>';
                                        echo '<span class="trash"><a href="' . $this->shipping_presets_referer_url . '&delete_preset_id=' . $_index . '" target="_self" class="submitdelete" aria-label="' . __('Löschen', \WooInvoicesMain::$text_domain) . '">' . __('Löschen', \WooInvoicesMain::$text_domain) . '</a></span>';
                                        echo '</div>';
                                        echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Mehr Details anzeigen', \WooInvoicesMain::$text_domain) . '</span></button>';
                                    }
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                        }

                        echo '</tbody>';
                        echo '</table>';
                    }

                    break;
            }

            return;
        }

        public function settings_page() {
            ?>
            <div class="wrap">
                <h2><?php _e('WooRechnung Einstellungen', \WooInvoicesMain::$text_domain); ?></h2>

                <?php
                $this->active_tab = empty($_REQUEST['tab']) ? 'general' : sanitize_title($_REQUEST['tab']);
                ?>

                <h2 class="nav-tab-wrapper">
                    <?php
                    foreach ($this->settings_tabs as $_tab) {
                        $this->add_tab(self::$settings_page_url_param, __($this->settings_tabs_names[$_tab], \WooInvoicesMain::$text_domain), $_tab, $this->active_tab);
                    }
                    ?>
                </h2>

                <?php
                if ((isset($this->shipping_preset_delete_id)) && (!empty($this->shipping_preset_settings))) {
                    if (!empty($_POST['accept'])) {
                        if ($_POST['accept'] == __('löschen', \WooInvoicesMain::$text_domain)) {
                            unset($this->shipping_preset_settings[$this->shipping_preset_delete_id]);
                            if (update_option(\WooInvoicesMain::$settings_page_name.'_shipping_presets', $this->shipping_preset_settings)) {
                                $this->shipping_preset_settings = get_option(\WooInvoicesMain::$settings_page_name.'_shipping_presets');
                                $_notice_message = __('Das Preset wurde erfolgreich gelöscht.', \WooInvoicesMain::$text_domain);
                                echo '<div class="updated settings-error notice is-dismissible"><p><strong>'.$_notice_message.'</strong></p></div>';
                            }
                            else {
                                $_notice_message = __('Das Preset konnte nicht gelöscht werden.', \WooInvoicesMain::$text_domain);
                                echo '<div class="error settings-error notice is-dismissible"><p><strong>'.$_notice_message.'</strong></p></div>';
                            }
                        }
                    }
                    if (empty($_POST['accept'])) {
                        echo '<h3>'.__('Wollen Sie das Preset wirklich löschen?', \WooInvoicesMain::$text_domain).'</h3>';
                        echo '<form action="'.$this->shipping_presets_referer_url.'&delete_preset_id='.$this->shipping_preset_delete_id.'" method="POST" target="_self">';
                        echo '<input type="submit" name="accept" value="'.__('abbrechen', \WooInvoicesMain::$text_domain).'" class="button button-secondary" />';
                        echo '&nbsp;&nbsp;&nbsp;';
                        echo '<input type="submit" name="accept" value="'.__('löschen', \WooInvoicesMain::$text_domain).'" class="button button-primary" />';
                        echo '</form>';
                        echo '<br>';
                    }
                }
                ?>

                <form action="options.php" method="POST" target="_self">
                    <?php
                    $_sSubmitText = $this->add_hidden_inputs();
                    if ($this->active_tab == 'general') {
                        settings_fields(\WooInvoicesMain::$settings_group);
                        do_settings_sections(\WooInvoicesMain::$settings_page_name);
                    }
                    else if (in_array($this->active_tab, $this->settings_tabs)) {
                        settings_fields(\WooInvoicesMain::$settings_group.'_'.$this->active_tab);
                        do_settings_sections(\WooInvoicesMain::$settings_page_name.'_'.$this->active_tab);
                    }
                    if (!empty($_sSubmitText)) {
                        submit_button($_sSubmitText);
                    }
                    ?>
                </form>
            </div>
            <?php
        }

        public function add_tab($_page, $_tab_name, $_tab_value) {
            ?>
            <a href="<?php echo add_query_arg(array('tab' => $_tab_value), '?page='.$_page); ?>" class="nav-tab <?php echo $this->active_tab == $_tab_value ? 'nav-tab-active' : ''; ?>"><?php echo $_tab_name; ?></a>
            <?php
        }

        public function settings_check_api($_args) {

            $_html = '<a href="'.get_admin_url(null, 'admin.php?page=woo_invoices_settings').'&api_test=1" id="'.$_args['id'].'">'.__('testen…', \WooInvoicesMain::$text_domain).'</a>';
            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }
            echo $_html;

            echo '<p style="margin-top: 3em;">';
                echo '<strong>Solltest du noch Fragen haben, prüfe zuerst ob dir unsere <a href="https://woorechnung.com/faq" target="_blank">FAQ</a> helfen kann. Ansonsten schreib uns unter <a href="mailto:support@woorechnung.com">support@woorechnung.com</a>.</strong>';
            echo '</p>';
        }

        public function settings_text_callback($_args) {
            if (empty($_args['size'])) {
                $_args['size'] = 80;
            }

            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<input type="text" id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']" value="'.$_value.'" size="'.$_args['size'].'"';
            if (!empty($_args['min_length'])) {
                $_html .= 'min-length="'.$_args['min_length'].'" ';
            }
            if (!empty($_args['max_length'])) {
                $_html .= 'max-length="'.$_args['max_length'].'" ';
            }
            $_html .= ' />';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_textarea_callback($_args) {
            if (empty($_args['size'])) {
                $_args['size'] = 80;
            }

            if (empty($_args['rows'])) {
                $_args['rows'] = 8;
            }

            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<textarea id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']" cols="'.$_args['size'].'" rows="'.$_args['rows'].'" ';
            if (!empty($_args['min_length'])) {
                $_html .= 'min-length="'.$_args['min_length'].'" ';
            }
            if (!empty($_args['max_length'])) {
                $_html .= 'max-length="'.$_args['max_length'].'" ';
            }
            $_html .= '>'.$_value.'</textarea>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_wp_editor_callback($_args) {
            if (empty($_args['size'])) {
                $_args['size'] = 80;
            }

            if (empty($_args['rows'])) {
                $_args['rows'] = 8;
            }

            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = wp_editor($_value, $_args['id'], array(
                'wpautop' => false,
                'media_buttons' => true,
                'textarea_name' => $_args['page'].'['.$_args['id'].']',
                'textarea_rows' => $_args['rows']
            ));

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_status_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            $_html .= '<option value="completed"';
            if($_value == 'completed') { $_html .= ' selected'; }
            $_html .= '>'.__('Fertiggestellt', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="pending"';
            if($_value == 'pending') { $_html .= ' selected'; }
            $_html .= '>'.__('Zahlung ausstehend', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="processing"';
            if($_value == 'processing') { $_html .= ' selected'; }
            $_html .= '>'.__('In Bearbeitung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="on-hold"';
            if($_value == 'on-hold') { $_html .= ' selected'; }
            $_html .= '>'.__('Wartend', \WooInvoicesMain::$text_domain).'</option>';

            if (class_exists('WC_Order_Status_Manager_Order_Statuses')) {
                $_defaults = array(
                    'post_type'        => 'wc_order_status',
                    'post_status'      => 'publish',
                    'posts_per_page'   => -1,
                    'suppress_filters' => 1,
                    'orderby'          => 'menu_order',
                    'order'            => 'ASC',
                );

                $_custom_statuses = new \WP_Query(wp_parse_args($_args = array(), $_defaults));
                foreach ($_custom_statuses->posts as $_order_status_post) {
                    $_custom_status = new \WC_Order_Status_Manager_Order_Status($_order_status_post);
                    if (!$_custom_status->is_core_status()) {
                        $_html .= '<option value="'.$_custom_status->get_slug().'"';
                        if($_value == $_custom_status->get_slug()) { $_html .= ' selected'; }
                        $_html .= '>'.$_custom_status->get_name().'</option>';
                    }
                }
            }

            $_html .= '</select>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_send_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            $_html .= '<option value="0"';
            if($_value == 0) { $_html .= ' selected'; }
            $_html .= '>'.__('nicht senden', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="1"';
            if($_value == 1) { $_html .= ' selected'; }
            $_html .= '>'.__('senden', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '</select>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_invoice_date_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            $_html .= '<option value="today"';
            if($_value == 'today' || $_value == '') { $_html .= ' selected'; }
            $_html .= '>'.__('Tag der Rechnungserstellung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="order"';
            if($_value == 'order') { $_html .= ' selected'; }
            $_html .= '>'.__('Tag der Bestellung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '</select>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_description_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            $_html .= '<option value=""';
            if($_value == '') { $_html .= ' selected'; }
            $_html .= '>'.__('Nur Produkttitel, keine Beschreibung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="article"';
            if($_value == 'article') { $_html .= ' selected'; }
            $_html .= '>'.__('Produkttitel und Produktbeschreibung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="short"';
            if($_value == 'short') { $_html .= ' selected'; }
            $_html .= '>'.__('Produkttitel und Produktkurzbeschreibung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="variation_title"';
            if($_value == 'variation_title') { $_html .= ' selected'; }
            $_html .= '>'.__('Produkttitel und Variationstitel', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '<option value="variation"';
            if($_value == 'variation') { $_html .= ' selected'; }
            $_html .= '>'.__('Produkttitel und Variationsbeschreibung', \WooInvoicesMain::$text_domain).'</option>';
            $_html .= '</select>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_country_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            foreach (\WooInvoicesMain::$countries as $_country) {
                $_html .= '<option value="'.$_country['value'].'"';
                if($_value == $_country['value']) { $_html .= ' selected'; }
                $_html .= '>'.$_country['name'].'</option>';
            }

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_default_preset_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            // Default Wert: 'Kein Preset ausgewählt'
            $_html .= '<option value="-1">'.$_args['default'].'</option>';

            foreach ($this->shipping_preset_settings as $_index => $_preset) {
                $value = $_index;
                $_html .= '<option value="'.$value.'"';
                if($_value == $value) { $_html .= ' selected'; }
                if($_preset['labels_properties_carrier'] == 'dpag') {
                    $_html .= '>'.\WooInvoicesMain::get_shipping_carrier_name($_preset['labels_properties_carrier']).' - '.\WooInvoicesMain::get_shipping_service_name($_preset['labels_properties_service']).' - '.\WooInvoicesMain::get_shipping_package_type_name($_preset['labels_properties_package_type']).' - '.$_preset['labels_properties_package_length'].' x '.$_preset['labels_properties_package_width'].' x '.$_preset['labels_properties_package_height'].' cm - '.$_preset['labels_properties_package_weight'].' kg'.'</option>';
                } else {
                    $_html .= '>'.\WooInvoicesMain::get_shipping_carrier_name($_preset['labels_properties_carrier']).' - '.\WooInvoicesMain::get_shipping_service_name($_preset['labels_properties_service']).' - '.$_preset['labels_properties_package_length'].' x '.$_preset['labels_properties_package_width'].' x '.$_preset['labels_properties_package_height'].' cm - '.$_preset['labels_properties_package_weight'].' kg'.'</option>';
                }
            }


            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_select_callback($_args) {
            $_value = '';
            if (!empty($_args['default'])) {
                $_value = $_args['default'];
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<select id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']">';
            foreach ($_args['options'] as $_option) {
                $_html .= '<option value="'.$_option['value'].'"';
                if($_value == $_option['value']) { $_html .= ' selected'; }
                $_html .= '>'.$_option['name'].'</option>';
            }

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }

        public function settings_checkbox_callback($_args) {

            $_value = '';
            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<input type="checkbox" id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']" value="1"';
            if($_value == 1) { $_html .= ' checked'; }
            $_html .= ' />';

            $_html .= '<p class="description">'.$_args['description'].'</p>';

            echo $_html;
        }

        public function settings_payment_methods_callback($_args) {
            global $wpdb;

            $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->postmeta.' WHERE meta_key = "_payment_method_title" GROUP BY meta_value' );

            $_options = get_option($_args['page']);

            $_html = '';
            foreach($results AS $result) {
                if(!isset($_options[$_args['id'].'_'.$result->meta_value])) {
                    $_options[$_args['id'].'_'.$result->meta_value] = 'payed';
                }
                $_html .= $result->meta_value.': ';
                $_html .= '<select id="'.$_args['id'].'_'.$result->meta_value.'" name="'.$_args['page'].'['.$_args['id'].'_'.$result->meta_value.']">';
                $_html .= '<option value="payed"';
                if($_options[$_args['id'].'_'.$result->meta_value] == 'payed') { $_html .= ' selected'; }
                $_html .= '>Bezahlt</option>';
                $_html .= '<option value="not_payed"';
                if($_options[$_args['id'].'_'.$result->meta_value] == 'not_payed') { $_html .= ' selected'; }
                $_html .= '>Offen</option>';
                $_html .= '</select>';
                $_html .= '<br />';
            }

            $_html .= '<p class="description">'.$_args['description'].'</p>';

            echo $_html;
        }

        public function settings_log_callback($_args) {
            if (empty($_args['size'])) {
                $_args['size'] = 120;
            }

            if (empty($_args['rows'])) {
                $_args['rows'] = 20;
            }

            $logFile = realpath(get_temp_dir()).'/woorechnung_log.txt';
            $_value = '';
            if(is_writable($logFile)) {
                if (is_file($logFile)) {
                    $_value = file_get_contents($logFile);
                }
            } else {
                $_value = $logFile.' NOT WRITABLE!';
            }

            $_options = get_option($_args['page']);
            if (isset($_options[$_args['id']])) {
                $_value = $_options[$_args['id']];
            }

            $_html = '<textarea id="'.$_args['id'].'" name="'.$_args['page'].'['.$_args['id'].']" cols="'.$_args['size'].'" rows="'.$_args['rows'].'" ';
            if (!empty($_args['min_length'])) {
                $_html .= 'min-length="'.$_args['min_length'].'" ';
            }
            if (!empty($_args['max_length'])) {
                $_html .= 'max-length="'.$_args['max_length'].'" ';
            }
            $_html .= '>'.$_value.'</textarea>';

            if (!empty($_args['description'])) {
                $_html .= '<p class="description">'.$_args['description'].'</p>';
            }

            echo $_html;
        }
    }
}