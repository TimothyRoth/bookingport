<?php

class BOOKINGPORT_Settings
{
    public static string $option_table = 'bookingport_settings';
    public static string $option_google_api_key = 'bookingport_general_google_api_key';
    public static string $option_google_freespace_icon = 'bookingport_general_google_freespace_icon';
    public static string $option_market_name = 'bookingport_general_market_name';
    public static string $option_market_owner = 'bookingport_general_market_owner';
    public static string $option_allow_private_users = 'bookingport_booking_allow_private_users';
    public static string $option_allow_stand_booking = 'bookingport_booking_allow_stand_booking';
    public static string $option_market_prefix = 'bookingport_market_prefix';
    public static string $option_market_logo = 'bookingport_general_market_logo';
    public static string $option_market_date = 'bookingport_general_market_date';
    public static string $option_market_header_video = 'bookingport_general_market_header_video';
    public static string $option_email_booking_request = 'bookingport_email_booking_request';
    public static string $option_price_per_meter = 'bookingport_booking_price_per_meter';
    public static string $option_product_price = 'bookingport_product_price';
    public static string $option_email_general_email = 'bookingport_general_email';
    public static string $option_general_phone = 'bookingport_general_phone';
    public static string $option_general_street = 'bookingport_general_street';
    public static string $option_general_city = 'bookingport_general_city';
    public static string $option_general_opening_times = 'bookingport_general_opening_times';
    public static string $option_general_use_header = 'bookingport_general_use_header';
    public static string $option_general_use_footer = 'bookingport_general_use_footer';
    public static string $option_email_registration_successful_subject = 'bookingport_email_registration_successful_subject';
    public static string $option_email_registration_successful_title = 'bookingport_email_registration_successful_title';
    public static string $option_email_registration_successful_body = 'bookingport_email_registration_successful_body';
    public static string $option_email_registration_successful_footer = 'bookingport_email_registration_successful_footer';
    public static string $option_email_registration_successful_registration_page = 'bookingport_email_registration_successful_registration_page';
    public static string $option_email_registration_successful_logo = 'bookingport_email_registration_successful_logo';
    public static string $option_email_proceed_customer_request_subject = 'bookingport_email_proceed_customer_request_subject';
    public static string $option_email_proceed_customer_request_title = 'bookingport_email_proceed_customer_request_title';
    public static string $option_email_proceed_customer_request_body = 'bookingport_email_proceed_customer_request_body';
    public static string $option_email_proceed_customer_request_footer = 'bookingport_email_proceed_customer_request_footer';
    public static string $option_email_proceed_customer_request_registration_page = 'bookingport_email_proceed_customer_request_registration_page';
    public static string $option_email_proceed_customer_request_logo = 'bookingport_email_proceed_customer_request_logo';
    public static string $option_email_request_successfully_processed_subject = 'bookingport_email_request_successfully_processed_subject';
    public static string $option_email_request_successfully_processed_title = 'bookingport_email_request_successfully_processed_title';
    public static string $option_email_request_successfully_processed_body = 'bookingport_email_request_successfully_processed_body';
    public static string $option_email_request_successfully_processed_footer = 'bookingport_email_request_successfully_processed_footer';
    public static string $option_email_request_successfully_processed_registration_page = 'bookingport_email_request_successfully_processed_registration_page';
    public static string $option_email_request_successfully_processed_logo = 'bookingport_email_request_successfully_processed_logo';
    public static string $option_redirects_admin_map = 'bookingport_redirects_admin_map';

    public static string $option_redirects_dashboard = 'bookingport_redirects_dashboard';
    public static string $option_redirects_booking_not_available = 'bookingport_redirects_booking_not_available';
    public static string $option_redirects_email_verification_successful = 'bookingport_redirects_email_verification_succesful';
    public static string $option_redirects_booking_request_send = 'bookingport_redirects_booking_request_send';
    public static string $option_redirects_admin_customers = 'bookingport_redirects_admin_customers';
    public static string $option_redirects_expired_offers = 'bookingport_redirects_expired_offers';
    public static string $option_redirects_customer_requests = 'bookingport_redirects_customer_requests';
    public static string $option_redirects_my_bookings = 'bookingport_redirects_my_bookings';

    public static string $option_redirects_stand_booking = 'bookingport_redirects_stand_booking';
    public static string $option_redirects_invoice = 'bookingport_redirects_invoice';
    public static string $option_redirects_registration = 'bookingport_redirects_registration';
    public static string $option_redirects_registration_successful = 'bookingport_redirects_registration_successful';
    public static string $option_redirects_session_expired = 'bookingport_redirects_session_expired';
    public static string $option_redirects_faq = 'bookingport_redirects_faq';
    public static string $option_redirects_data_privacy = 'bookingport_redirects_data_privacy';
    public static string $option_redirects_imprint = 'bookingport_redirects_imprint';
    public static string $option_redirects_tos = 'bookingport_redirects_tos';
    public static string $option_map_center_lat = 'bookingport_map_center_lat';
    public static string $option_map_center_lng = 'bookingport_map_center_lng';
    public static string $option_map_zoom_level_admin = 'bookingport_map_zoom_level_admin';
    public static string $option_map_zoom_level_stand_booking = 'bookingport_map_zoom_level_stand_booking';
    public static string $option_map_satellite_view = 'bookingport_map_satellite_view';
    public static string $option_save_settings_once = 'bookingport_general_save_settings_once';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('admin_init', [__CLASS__, 'bookingport_register_settings_and_options']);
        add_action('admin_menu', [__CLASS__, 'add_bookingport_menu_entry'], 100);
    }

    public static function add_bookingport_menu_entry(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Bookingport Marketplace', 'wp_bookingport'),
            __('Bookingport Marketplace', 'wp_bookingport'),
            'manage_options',
            'Bookingport Marketplace Settings',
            [__CLASS__, 'render_bookingport_settings_page']
        );
    }

    public static function bookingport_register_settings_and_options(): void
    {
        $default_values = [
            self::$option_save_settings_once => '',
            self::$option_google_api_key => '',
            self::$option_google_freespace_icon => '',
            self::$option_market_prefix => '#A',
            self::$option_market_name => '',
            self::$option_market_owner => '',
            self::$option_market_logo => '',
            self::$option_market_date => '',
            self::$option_market_header_video => '',
            self::$option_email_booking_request => '',
            self::$option_price_per_meter => '',
            self::$option_email_general_email => '',
            self::$option_general_phone => '',
            self::$option_general_street => '',
            self::$option_general_city => '',
            self::$option_general_opening_times => '',
            self::$option_email_registration_successful_subject => '',
            self::$option_email_registration_successful_title => '',
            self::$option_email_registration_successful_body => '',
            self::$option_email_registration_successful_footer => '',
            self::$option_email_registration_successful_registration_page => '',
            self::$option_email_registration_successful_logo => '',
            self::$option_email_proceed_customer_request_subject => '',
            self::$option_email_proceed_customer_request_title => '',
            self::$option_email_proceed_customer_request_body => '',
            self::$option_email_proceed_customer_request_footer => '',
            self::$option_email_proceed_customer_request_registration_page => '',
            self::$option_email_proceed_customer_request_logo => '',
            self::$option_email_request_successfully_processed_subject => '',
            self::$option_email_request_successfully_processed_title => '',
            self::$option_email_request_successfully_processed_body => '',
            self::$option_email_request_successfully_processed_footer => '',
            self::$option_email_request_successfully_processed_registration_page => '',
            self::$option_email_request_successfully_processed_logo => '',
            self::$option_map_center_lat => '',
            self::$option_map_center_lng => '',
            self::$option_map_zoom_level_admin => '18',
            self::$option_map_zoom_level_stand_booking => '18',
            self::$option_redirects_admin_map => 'admin-standkarte',
            self::$option_redirects_expired_offers => 'abgelaufene-angebote',
            self::$option_redirects_dashboard => 'dashboard',
            self::$option_redirects_booking_not_available => 'derzeit-keine-buchung-moeglich',
            self::$option_redirects_email_verification_successful => 'e-mail-verifizierung-erfolgreich',
            self::$option_redirects_booking_request_send => 'ihre-buchungsanfrage-wurde-gesendet',
            self::$option_redirects_admin_customers => 'kunden',
            self::$option_redirects_customer_requests => 'meine-anfragen/',
            self::$option_redirects_my_bookings => 'meine-standbuchungen',
            self::$option_redirects_stand_booking => 'neuen-stand-buchen',
            self::$option_redirects_invoice => 'rechnungen',
            self::$option_redirects_registration => 'registrierung',
            self::$option_redirects_registration_successful => 'registrierung-erfolgreich',
            self::$option_redirects_session_expired => 'sitzung-abgelaufen',
            self::$option_redirects_faq => 'faq',
            self::$option_redirects_data_privacy => 'datenschutz',
            self::$option_redirects_imprint => 'impressum',
            self::$option_redirects_tos => 'agb'
        ];

        add_option(self::$option_table, $default_values);

    }

    public static function render_bookingport_settings_page(): void
    {
        if (isset($_POST['bookingport_settings'], $_POST['_wpnonce']) && $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['_wpnonce'], 'bookingport-settings-nonce')) {
            // Update the option with new values
            update_option(self::$option_table, $_POST[self::$option_table]);

            // save the new price for the woocommerce product
            if (class_exists('woocommerce') && isset($_POST[self::$option_product_price]) && !empty($_POST[self::$option_product_price])) {
                $new_price = $_POST[self::$option_product_price];
                $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
                $product = wc_get_product($product_id);
                $product->set_regular_price($new_price);
                $product->save();
            }

            echo '<div class="updated"><p>Ihre Einstellungen wurden gespeichert.</p></div>';
        }

        $bookingport_options = get_option(self::$option_table);
        ?>

        <div class="wrapper">
            <h2>Bookingport Marketplace Settings</h2>
            <p>Beim aktivieren des Plugins werden automatisch die entsprechenden Seiten inklusive
                Shortcodes
                erstellt. Zudem werden die entsprechenden Slugs der WooCommerce Seiten
                überschrieben.<br/>
                Wenn Sie die Seitenstruktur ändern wollen, können Sie die Shortcodes auf jeder
                beliebigen oder
                selbst erstellten Seite
                benutzen. Für diese Seiten wurde automatisch das Page Template: <strong>BookingPort
                    Template</strong> erstellt. <br/>
                Bitte bedenken Sie, dass Sie die Templates für und Sidebar dann im Plugin
                entsprechend
                anpassen müssen, um die Seitennaviation zu gewährleisten.
            </p>

            <form method="post" action="<?php echo admin_url('admin.php?page=Bookingport+Marketplace+Settings'); ?>">
                <?php wp_nonce_field('bookingport-settings-nonce'); ?>

                <div class="bookingport-settings-tabs">
                    <div class="bookingport-settings-tab active" id="general">Allgemein</div>
                    <div class="bookingport-settings-tab" id="plugin-settings">Einstellungen</div>
                    <div class="bookingport-settings-tab" id="map">Google Maps</div>
                    <div class="bookingport-settings-tab" id="redirects">Weiterleitungen</div>
                    <div class="bookingport-settings-tab" id="shortcodes">Shortcodes</div>
                    <div class="bookingport-settings-tab" id="booking">Buchungseinstellungen</div>
                    <div class="bookingport-settings-tab" id="email">E-Mail</div>
                </div>

                <div class="bookingport-settings-content-wrapper">
                    <div class="bookingport-settings-content active" id="general-content">
                        <p>
                            <label for="<?= self::$option_market_owner ?>"><strong>Name des
                                    Veranstalters</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_owner ?>"
                                   name="bookingport_settings[<?= self::$option_market_owner ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_owner]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_market_name ?>"><strong>Name des Marktes</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_name ?>"
                                   name="bookingport_settings[<?= self::$option_market_name ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_name]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_general_phone ?>"><strong>Telefonnummer</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_general_phone ?>"
                                   name="bookingport_settings[<?= self::$option_general_phone ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_general_phone]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_general_city ?>"><strong>Stadt</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_general_city ?>"
                                   name="bookingport_settings[<?= self::$option_general_city ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_general_city]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_general_street ?>"><strong>Straße</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_general_street ?>"
                                   name="bookingport_settings[<?= self::$option_general_street ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_general_street]); ?>">
                        </p>
                        <p>
                            <label
                                    for="<?= self::$option_general_opening_times ?>"><strong>Erreichbarkeit</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_general_opening_times ?>"
                                   name="bookingport_settings[<?= self::$option_general_opening_times ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_general_opening_times]); ?>">
                        </p>
                    </div>
                    <div class="bookingport-settings-content" id="plugin-settings-content">
                        <p class="checkbox">
                            <input type="checkbox" id="<?= self::$option_general_use_header ?>"
                                   name="bookingport_settings[<?= self::$option_general_use_header ?>]"
                                   value="1" <?php checked('1', isset($bookingport_options[self::$option_general_use_header]) && $bookingport_options[self::$option_general_use_header] === '1'); ?>>
                            <label for="<?= self::$option_general_use_header ?>"><strong>Plugin Header
                                    deaktivieren</strong></label>
                        </p>

                        <p class="checkbox">
                            <input type="checkbox" id="<?= self::$option_general_use_footer ?>"
                                   name="bookingport_settings[<?= self::$option_general_use_footer ?>]"
                                   value="1" <?php checked('1', $bookingport_options[self::$option_general_use_footer] ?? '0'); ?>>
                            <label for="<?= self::$option_general_use_footer ?>"><strong>Plugin Footer
                                    deaktivieren</strong></label>
                        </p>


                        <p>
                            <label for="<?= self::$option_market_logo ?>"><strong>Marktlogo URL</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_logo ?>"
                                   name="bookingport_settings[<?= self::$option_market_logo ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_logo]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_market_date ?>"><strong>Datum es Marktes
                                    (von-bis)</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_date ?>"
                                   name="bookingport_settings[<?= self::$option_market_date ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_date]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_market_header_video ?>"><strong>Header Video
                                    URL</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_header_video ?>"
                                   name="bookingport_settings[<?= self::$option_market_header_video ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_header_video]); ?>">
                        </p>
                    </div>
                    <div class="bookingport-settings-content" id="map-content">
                        <p>
                            <label for="<?= self::$option_google_api_key ?>"><strong>Google Api Key</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_google_api_key ?>"
                                   name="bookingport_settings[<?= self::$option_google_api_key ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_google_api_key]); ?>">
                        </p>
                        <p class="checkbox">
                            <input type="checkbox" id="<?= self::$option_map_satellite_view ?>"
                                   name="bookingport_settings[<?= self::$option_map_satellite_view ?>]"
                                   value="1" <?php checked('1', isset($bookingport_options[self::$option_map_satellite_view]) && $bookingport_options[self::$option_map_satellite_view] === '1'); ?>>
                            <label for="<?= self::$option_map_satellite_view ?>"><strong>Satellitenansicht
                                    aktivieren</strong></label>
                        </p>
                        <p>
                            <label for="<?= self::$option_map_center_lng ?>"><strong>Längengrad des
                                    Kartenmittelpunkts</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_map_center_lng ?>"
                                   name="bookingport_settings[<?= self::$option_map_center_lng ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_map_center_lng]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_map_center_lat ?>"><strong>Breitengrad des
                                    Kartenmittelpunkts</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_map_center_lat ?>"
                                   name="bookingport_settings[<?= self::$option_map_center_lat ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_map_center_lat]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_google_freespace_icon ?>"><strong>Icon für
                                    Freiflächen</strong></label>
                            <br/>
                            <input type="url" id="<?= self::$option_google_freespace_icon ?>"
                                   name="bookingport_settings[<?= self::$option_google_freespace_icon ?>]"
                                   placeholder="Image URL"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_google_freespace_icon]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_map_zoom_level_admin ?>"><strong>Map Zoom Admin
                                    Karte</strong></label>
                            <br/>
                            <input type="number" id="<?= self::$option_map_zoom_level_admin ?>"
                                   name="bookingport_settings[<?= self::$option_map_zoom_level_admin ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_map_zoom_level_admin]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_map_zoom_level_stand_booking ?>"><strong>Map Zoom
                                    Standbuchungen</strong></label>
                            <br/>
                            <input type="number" id="<?= self::$option_map_zoom_level_stand_booking ?>"
                                   name="bookingport_settings[<?= self::$option_map_zoom_level_stand_booking ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_map_zoom_level_stand_booking]); ?>">
                        </p>

                    </div>
                    <div class="bookingport-settings-content" id="email-content">
                        <div class="email-tabs">
                            <div class="email-tab active" id="email-general">Allgemein</div>
                            <div class="email-tab" id="email-registration-sucessful">Registrierung Erfolgreich</div>
                            <div class="email-tab" id="email-proceed-customer-request">Kundenanfrage bearbeiten</div>
                            <div class="email-tab" id="email-request-successfully-processed">Anfrage erfolgreich
                                bearbeitet
                            </div>
                        </div>
                        <div class="email-content-wrapper">
                            <div class="email-content active" id="email-general-content">
                                <p>
                                    <label for="<?= self::$option_email_general_email ?>"><strong>Allgemeine
                                            E-Mail-Adresse</strong></label>
                                    <br/>
                                    <input type="email" id="<?= self::$option_email_general_email ?>"
                                           name="bookingport_settings[<?= self::$option_email_general_email ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_general_email]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_booking_request ?>"><strong>E-Mail-Adresse für
                                            Buchungsanfragen</strong></label>
                                    <br/>
                                    <input type="email" id="<?= self::$option_email_booking_request ?>"
                                           name="bookingport_settings[<?= self::$option_email_booking_request ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_booking_request]); ?>">
                                </p>
                            </div>
                            <div class="email-content" id="email-registration-sucessful-content">
                                <p>
                                    <label for="<?= self::$option_email_registration_successful_logo ?>"><strong>E-Mail
                                            Logo URL</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_registration_successful_logo ?>"
                                           name="bookingport_settings[<?= self::$option_email_registration_successful_logo ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_registration_successful_logo]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_registration_successful_subject ?>"><strong>Betreff</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_registration_successful_subject ?>"
                                           name="bookingport_settings[<?= self::$option_email_registration_successful_subject ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_registration_successful_subject]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_registration_successful_title ?>"><strong>E-Mail
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_registration_successful_title ?>"
                                           name="bookingport_settings[<?= self::$option_email_registration_successful_title ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_registration_successful_title]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_registration_successful_registration_page ?>"><strong>Homepage
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_registration_successful_registration_page ?>"
                                           name="bookingport_settings[<?= self::$option_email_registration_successful_registration_page ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_registration_successful_registration_page]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_registration_successful_body ?>"><strong>Body</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_registration_successful_body ?>"
                                              name="bookingport_settings[<?= self::$option_email_registration_successful_body ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_registration_successful_body]); ?></textarea>
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_registration_successful_footer ?>"><strong>Fußbereich</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_registration_successful_footer ?>"
                                              name="bookingport_settings[<?= self::$option_email_registration_successful_footer ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_registration_successful_footer]); ?></textarea>
                                </p>
                            </div>
                            <div class="email-content" id="email-proceed-customer-request-content">
                                <p>
                                    <label for="<?= self::$option_email_proceed_customer_request_logo ?>"><strong>E-Mail
                                            Logo URL</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_proceed_customer_request_logo ?>"
                                           name="bookingport_settings[<?= self::$option_email_proceed_customer_request_logo ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_proceed_customer_request_logo]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_proceed_customer_request_subject ?>"><strong>Betreff</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_proceed_customer_request_subject ?>"
                                           name="bookingport_settings[<?= self::$option_email_proceed_customer_request_subject ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_proceed_customer_request_subject]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_proceed_customer_request_title ?>"><strong>E-Mail
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text" id="<?= self::$option_email_proceed_customer_request_title ?>"
                                           name="bookingport_settings[<?= self::$option_email_proceed_customer_request_title ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_proceed_customer_request_title]); ?>">
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_proceed_customer_request_registration_page ?>"><strong>Homepage
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_proceed_customer_request_registration_page ?>"
                                           name="bookingport_settings[<?= self::$option_email_proceed_customer_request_registration_page ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_proceed_customer_request_registration_page]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_proceed_customer_request_body ?>"><strong>Body</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_proceed_customer_request_body ?>"
                                              name="bookingport_settings[<?= self::$option_email_proceed_customer_request_body ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_proceed_customer_request_body]); ?></textarea>
                                </p>
                                <p>
                                    <label for="<?= self::$option_email_proceed_customer_request_footer ?>"><strong>Fußbereich</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_proceed_customer_request_footer ?>"
                                              name="bookingport_settings[<?= self::$option_email_proceed_customer_request_footer ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_proceed_customer_request_footer]) ?></textarea>
                                </p>
                            </div>
                            <div class="email-content" id="email-request-successfully-processed-content">
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_logo ?>"><strong>E-Mail
                                            Logo URL</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_request_successfully_processed_logo ?>"
                                           name="bookingport_settings[<?= self::$option_email_request_successfully_processed_logo ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_request_successfully_processed_logo]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_subject ?>"><strong>Betreff</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_request_successfully_processed_subject ?>"
                                           name="bookingport_settings[<?= self::$option_email_request_successfully_processed_subject ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_request_successfully_processed_subject]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_title ?>"><strong>E-Mail
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_request_successfully_processed_title ?>"
                                           name="bookingport_settings[<?= self::$option_email_request_successfully_processed_title ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_request_successfully_processed_title]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_registration_page ?>"><strong>Homepage
                                            Titel</strong></label>
                                    <br/>
                                    <input type="text"
                                           id="<?= self::$option_email_request_successfully_processed_registration_page ?>"
                                           name="bookingport_settings[<?= self::$option_email_request_successfully_processed_registration_page ?>]"
                                           value="<?php echo esc_attr($bookingport_options[self::$option_email_request_successfully_processed_registration_page]); ?>">
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_body ?>"><strong>Body</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_request_successfully_processed_body ?>"
                                              name="bookingport_settings[<?= self::$option_email_request_successfully_processed_body ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_request_successfully_processed_body]); ?></textarea>
                                </p>
                                <p>
                                    <label
                                            for="<?= self::$option_email_request_successfully_processed_footer ?>"><strong>Fußbereich</strong></label>
                                    <br/>
                                    <textarea id="<?= self::$option_email_request_successfully_processed_footer ?>"
                                              name="bookingport_settings[<?= self::$option_email_request_successfully_processed_footer ?>]"><?php echo stripslashes($bookingport_options[self::$option_email_request_successfully_processed_footer]); ?></textarea>
                                </p>
                            </div>

                        </div>
                    </div>
                    <div class="bookingport-settings-content" id="redirects-content">
                        <p>
                            <label for="<?= self::$option_redirects_admin_map ?>"><strong>Admin
                                    Standkarte</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_admin_map ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_admin_map ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_admin_map]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_dashboard ?>"><strong>Dashboard</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_dashboard ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_dashboard ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_dashboard]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_booking_not_available ?>"><strong>Derzeit keine
                                    Buchung möglich</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_booking_not_available ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_booking_not_available ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_booking_not_available]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_email_verification_successful ?>"><strong>E-Mail
                                    Verifizierung erfolgreich</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_email_verification_successful ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_email_verification_successful ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_email_verification_successful]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_booking_request_send ?>"><strong>Ihre
                                    Buchungsanfrage wurde gesendet</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_booking_request_send ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_booking_request_send ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_booking_request_send]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_admin_customers ?>"><strong>Kunden</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_admin_customers ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_admin_customers ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_admin_customers]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_customer_requests ?>"><strong>Meine
                                    Anfragen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_customer_requests ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_customer_requests ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_customer_requests]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_my_bookings ?>"><strong>Meine
                                    Standbuchungen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_my_bookings ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_my_bookings ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_my_bookings]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_stand_booking ?>"><strong>Neuen Stand
                                    buchen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_stand_booking ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_stand_booking ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_stand_booking]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_expired_offers ?>"><strong>Abgelaufene
                                    Angebote</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_expired_offers ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_expired_offers ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_expired_offers]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_invoice ?>"><strong>Rechnungen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_invoice ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_invoice ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_invoice]); ?>">
                        </p>

                        <p>
                            <label
                                    for="<?= self::$option_redirects_registration ?>"><strong>Registrierung</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_registration ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_registration ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_registration]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_registration_successful ?>"><strong>Registrierung
                                    erfolgreich</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_registration_successful ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_registration_successful ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_registration_successful]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_session_expired ?>"><strong>Sitzung
                                    abgelaufen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_session_expired ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_session_expired ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_session_expired]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_faq ?>"><strong>FAQ</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_faq ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_faq ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_faq]); ?>">
                        </p>

                        <p>
                            <label
                                    for="<?= self::$option_redirects_data_privacy ?>"><strong>Datenschutz</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_data_privacy ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_data_privacy ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_data_privacy]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_imprint ?>"><strong>Impressum</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_imprint ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_imprint ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_imprint]); ?>">
                        </p>

                        <p>
                            <label for="<?= self::$option_redirects_tos ?>"><strong>AGB</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_redirects_tos ?>"
                                   name="bookingport_settings[<?= self::$option_redirects_tos ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_redirects_tos]) ?>">
                        </p>

                    </div>
                    <div class="bookingport-settings-content" id="booking-content">
                        <p>
                            <?php

                            /* get woocommerce product price */

                            if (class_exists('woocommerce')) {
                                $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
                                $product = wc_get_product($product_id);
                                $product_price = $product->get_price();
                            }

                            ?>
                            <label for="<?= self::$option_product_price ?>"><strong>Preis für Privatkunden in
                                    €</strong></label> <br/>
                            <input type="number" name="<?= self::$option_product_price ?>"
                                   id="<?= self::$option_product_price ?>"
                                   value="<?= $product_price ?? '' ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_price_per_meter ?>"><strong>Preis pro m für Anlieger in
                                    €</strong></label>
                            <br/>
                            <input type="number" id="<?= self::$option_price_per_meter ?>"
                                   name="bookingport_settings[<?= self::$option_price_per_meter ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_price_per_meter]); ?>">
                        </p>
                        <p>
                            <label for="<?= self::$option_market_prefix ?>"><strong>Präfix für Angebote und
                                    Anfragen</strong></label>
                            <br/>
                            <input type="text" id="<?= self::$option_market_prefix ?>"
                                   name="bookingport_settings[<?= self::$option_market_prefix ?>]"
                                   value="<?php echo esc_attr($bookingport_options[self::$option_market_prefix]); ?>">
                        </p>
                        <p class="checkbox">
                            <input type="checkbox" id="<?= self::$option_allow_private_users ?>"
                                   name="bookingport_settings[<?= self::$option_allow_private_users ?>]"
                                   value="1" <?php checked('1', $bookingport_options[self::$option_allow_private_users] ?? '0'); ?>>
                            <label for="<?= self::$option_allow_private_users ?>"><strong>Standbuchungen für private
                                    Nutzer aktivieren</strong></label>
                        </p>
                        <br/>
                        <p class="checkbox">
                            <input type="checkbox" id="<?= self::$option_allow_stand_booking ?>"
                                   name="bookingport_settings[<?= self::$option_allow_stand_booking ?>]"
                                   value="1" <?php checked('1', $bookingport_options[self::$option_allow_stand_booking] ?? '0'); ?>>
                            <label for="<?= self::$option_allow_stand_booking ?>"><strong>Standbuchungen für alle Nutzer
                                    deaktivieren</strong></label>
                        </p>

                    </div>
                    <div class="bookingport-settings-content" id="shortcodes-content">
                        <div class="plugin-info" id="plugin-info">
                            <p>
                                <strong>Folgende Shortcodes können überall auf der Seite verwendet werden:</strong>
                                <br/><br/>
                                [bookingport_registration] <br/>
                                [bookingport_stand_booking] <br/>
                                [bookingport_invoice] <br/>
                                [bookingport_admin_map] <br/>
                                [bookingport_admin_customers] <br/>
                                [bookingport_customer_requests] <br/>
                                [bookingport_accept_reservations] <br/>
                                [bookingport_my_bookings] <br/>
                                [bookingport_registration_successful] <br/>
                                [bookingport_session_expired] <br/>
                                [bookingport_dashboard] <br/>
                                [bookingport_booking_not_available] <br/>
                                [bookingport_email_verification_successful] <br/>
                                [bookingport_expired_offers] <br/>
                                [bookingport_booking_request_send] <br/><br/
                            </p>
                        </div>
                    </div>
                </div>

                <div class="save-settings" id="save-settings">
                    <input type="hidden" id="<?= self::$option_save_settings_once ?>"
                           name="bookingport_settings[<?= self::$option_save_settings_once ?>]"
                           value="1">
                </div>
                <?php

                submit_button('Änderungen speichern', 'primary', 'submit', false);

                if (!isset(get_option(self::$option_table)[self::$option_save_settings_once])) {
                    echo '<div class="reminder" id="reminder">Bitte speichern Sie die Einstellungen nach der ersten Installation des Plugins.</div>';
                } ?>

            </form>
        </div>

        <style>

            .reminder {
                color: red;
                font-style: italic;
                margin-top: 10px;
            }

            .email-content {
                display: none;
            }

            .email-content.active {
                display: block;
            }

            .bookingport-settings-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .bookingport-settings-tab.active {
                display: block;
            }

            .email-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .email-tab {
                padding: 5px 10px;
                border: 2px solid #2271b1;
                border-radius: 5px;
                color: #2c3338;
                cursor: pointer;
                font-weight: 600;
            }

            .email-tab.active {
                background: #2271b1;
                color: #fff;
            }

            .bookingport-settings-tab {
                cursor: pointer;
                color: rgba(240, 246, 252, .7);
                background: #2c3338;
                padding: 10px;
            }

            .bookingport-settings-tab.active {
                background: #2271b1;
                color: #fff;
            }

            .bookingport-settings-content-wrapper {
                margin-bottom: 50px;
            }

            .bookingport-settings-content-wrapper input[type="text"],
            .bookingport-settings-content-wrapper input[type="email"] {
                width: calc(100% - 16px);
                max-width: 500px;
            }

            .bookingport-settings-content-wrapper textarea {
                width: calc(100% - 16px);
                max-width: 500px;
                min-height: 300px;
            }

            .bookingport-settings-content {
                display: none;
                margin-top: 50px;
            }

            .bookingport-settings-content p label {
                margin-bottom: 5px;
                display: inline-block;
            }

            .bookingport-settings-content p.checkbox label {
                margin-bottom: 0;
            }

            .bookingport-settings-content.active {
                display: block;
            }
        </style>

        <script>
            jQuery(document).ready(function ($) {
                const tabs = $('.bookingport-settings-tab');
                const content = $('.bookingport-settings-content');

                tabs.each(function (index, tab) {
                    const thisTab = $(tab);
                    thisTab.on('click', function () {
                        tabs.not(thisTab).removeClass('active');
                        const tabID = thisTab.attr('id');
                        content.removeClass('active');
                        jQuery('#' + tabID + '-content').addClass('active');
                        thisTab.addClass('active');
                    });
                });

                const emailTabs = $('.email-tab');
                const emailContent = $('.email-content');

                emailTabs.each(function (index, emailTab) {
                    const thisTab = $(emailTab);
                    thisTab.on('click', function () {
                        console.log(thisTab)
                        emailTabs.not(thisTab).removeClass('active');
                        const tabID = thisTab.attr('id');
                        emailContent.removeClass('active');
                        jQuery('#' + tabID + '-content').addClass('active');
                        thisTab.addClass('active');
                    });
                });

            });
        </script>
    <?php }
}
