<?php

class BOOKINGPORT_Installation
{

    public static string $product_sku = 'stand';
    public static string $product_name = 'Stand';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('admin_init', [__CLASS__, 'check_woocommerce_dependency_bookingport']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'load_plugin_scripts']);
        add_action('template_include', [__CLASS__, 'register_bookingport_plugin_archive_template']);

        add_filter('page_template', [__CLASS__, 'register_bookingport_plugin_page_template']);
        add_filter('theme_page_templates', [__CLASS__, 'add_bookingport_plugin_page_template']);
        add_filter('woocommerce_locate_template', [__CLASS__, 'custom_woocommerce_template'], 10, 3);

        register_activation_hook(BOOKINGPORT_PLUGIN_FILE_PATH, [__CLASS__, 'create_plugin_pages']);
        register_activation_hook(BOOKINGPORT_PLUGIN_FILE_PATH, [__CLASS__, 'create_woocommerce_product']);
        register_activation_hook(BOOKINGPORT_PLUGIN_FILE_PATH, [__CLASS__, 'change_woocommerce_pages']);
        register_activation_hook(BOOKINGPORT_PLUGIN_FILE_PATH, [__CLASS__, 'set_up_theme_settings']);
    }

    public static function check_woocommerce_dependency_bookingport(): void
    {
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            add_action('admin_notices', [__CLASS__, 'bookingport_woocommerce_dependency_notice']);
            deactivate_plugins(BOOKINGPORT_PLUGIN_BASENAME);
        }
    }

    public static function bookingport_woocommerce_dependency_notice(): void
    {
        echo '<div class="notice notice-error"><p>The Bookingport plugin requires WooCommerce to be installed and activated. Please install and activate WooCommerce to use this plugin.</p></div>';
    }

    public static function set_up_theme_settings(): void
    {

        update_option('blogname', 'Bookingport Marketplace');
        update_option('permalink_structure', '/%postname%/');

        $front_page = get_page_by_path('startseite');

        if ($front_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $front_page->ID);
        }

    }

    public static function create_plugin_pages(): void
    {

        $pages = [

            'Admin Standkarte' => '[bookingport_admin_map]',
            'Dashboard' => '[bookingport_dashboard]',
            'Derzeit keine Buchung möglich' => '[bookingport_booking_not_available]',
            'E-Mail Verifizierung erfolgreich' => '[bookingport_email_verification_successful]',
            'Anfrage wurde gesendet' => '[bookingport_booking_request_send]',
            'Kunden' => '[bookingport_admin_customers]',
            'Meine Anfragen' => '[bookingport_customer_requests]',
            'Meine Standbuchungen' => '[bookingport_my_bookings]',
            'Neuen Stand buchen' => '[bookingport_stand_booking]',
            'Rechnungen' => '[bookingport_invoice]',
            'Registrierung' => '[bookingport_registration]',
            'Registrierung Erfolgreich' => '[bookingport_registration_successful]',
            'Reservierungen Annehmen' => '[bookingport_accept_reservations]',
            'Sitzung abgelaufen' => '[bookingport_session_expired]',
            'Abgelaufene Angebote' => '[bookingport_expired_offers]',

            'Datenschutz' => '',
            'Impressum' => '',
            'AGB' => '',
            'Startseite' => ''

        ];

        foreach ($pages as $title => $content) {
            $page = get_page_by_title($title);

            if (!$page) {
                $page_args = [
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ];

                $result = wp_insert_post($page_args);

                if (is_wp_error($result)) {
                    error_log('Error creating page: ' . $result->get_error_message()); // Add this line for debugging
                } else {
                    error_log('Page created successfully: ' . $title); // Add this line for debugging
                }
            }
        }
    }

    public static function create_woocommerce_product(): void
    {
        if (class_exists('woocommerce')) {

            $product_id = wc_get_product_id_by_sku(self::$product_sku);
            $existing_product = wc_get_product($product_id);

            if (!$existing_product) {
                $product = new WC_Product_Simple();
                $product->set_name(self::$product_name);
                $product->set_regular_price(20);
                $product->set_sku(self::$product_sku);
                $product->set_virtual(true);
                $product->set_manage_stock(false);
                $product->set_stock_status('instock');
                $product->set_status('publish');
                $product->save();
            }
        }
    }

    public static function change_woocommerce_pages(): void
    {
        if (class_exists('woocommerce')) {
            $shop_page_id = wc_get_page_id('shop');
            if ($shop_page_id) {
                wp_delete_post($shop_page_id, true); // Set the second argument to true to force deletion
            }

            $checkout_page_id = wc_get_page_id('checkout');
            if ($checkout_page_id) {
                wp_update_post([
                    'ID' => $checkout_page_id,
                    'post_title' => 'Ihre Standbuchung',
                    'post_name' => 'zur-kasse-gehen'
                ]);
            }

            $cart_page_id = wc_get_page_id('cart');
            if ($cart_page_id) {
                wp_update_post([
                    'ID' => $checkout_page_id,
                    'post_title' => 'Ihre Standbuchung',
                    'post_name' => 'warenkorb'
                ]);
            }

            $account_page_id = wc_get_page_id('myaccount');
            if ($account_page_id) {
                wp_update_post([
                    'ID' => $account_page_id,
                    'post_title' => 'Mein Konto',
                    'post_name' => 'mein-konto'
                ]);
            }
        }
    }

    public static function load_plugin_scripts(): void
    {

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);

        $google_maps_plugin_settings = [
            'google_api_key' => $option_table[BOOKINGPORT_Settings::$option_google_api_key],
            'google_satellite_view' => isset($option_table[BOOKINGPORT_Settings::$option_map_satellite_view]),
            'google_freespace_icon' => $option_table[BOOKINGPORT_Settings::$option_google_freespace_icon],
            'google_map_center_lat' => $option_table[BOOKINGPORT_Settings::$option_map_center_lat],
            'google_map_center_lng' => $option_table[BOOKINGPORT_Settings::$option_map_center_lng],
            'google_map_zoom_level_admin' => $option_table[BOOKINGPORT_Settings::$option_map_zoom_level_admin],
            'google_map_zoom_level_stand_booking' => $option_table[BOOKINGPORT_Settings::$option_map_zoom_level_stand_booking]
        ];

        $bookingport_plugin_settings = [
            'market_prefix' => $option_table[BOOKINGPORT_Settings::$option_market_prefix]
        ];

        if (!is_admin()) {
            wp_deregister_script('jquery');
            wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js', false, '3.6.1', true);
            wp_enqueue_script('jquery');

            wp_enqueue_script('plugin-bundle', BOOKINGPORT_PLUGIN_URI . '/dist/main.min.js', ['wp-i18n'], '0.1', true);
            wp_enqueue_script('pw-script', 'https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js', ['wp-i18n'], '0.1', true);
            wp_localize_script('plugin-bundle', 'ajax', ['url' => admin_url('admin-ajax.php')]);
            wp_localize_script('plugin-bundle', 'googleMapsPluginSettings', $google_maps_plugin_settings);
            wp_localize_script('plugin-bundle', 'bookingport_plugin_settings', $bookingport_plugin_settings);
            wp_enqueue_style('plugin-main', BOOKINGPORT_PLUGIN_URI . '/dist/main.min.css', [], '0.1', 'all');
        }
    }

    public static function register_bookingport_plugin_page_template($template): string
    {
        $bookingport_template_file = 'inc/page_templates/page-template-bookingport.php';

        if (is_singular() && get_post_meta(get_the_ID(), '_wp_page_template', true) === $bookingport_template_file) {
            $template = BOOKINGPORT_PLUGIN_PATH . $bookingport_template_file;
        }

        return $template;
    }

    public static function register_bookingport_plugin_archive_template($template): string
    {

        if (is_post_type_archive(BOOKINGPORT_CptFAQ::$Cpt_FAQ)) {
            $template = BOOKINGPORT_PLUGIN_PATH . 'inc/page_templates/archive-faq.php';
        }

        return $template;
    }

    public static function add_bookingport_plugin_page_template($page_templates): array
    {
        $bookingport_template_file = 'inc/page_templates/page-template-bookingport.php';
        $template_name = 'BookingPort Template';
        $page_templates[$bookingport_template_file] = $template_name;

        return $page_templates;
    }

    public static function custom_woocommerce_template($template, $template_name, $template_path): string
    {

        $custom_template_path = BOOKINGPORT_PLUGIN_PATH . 'woocommerce/';

        if (file_exists($custom_template_path . $template_name)) {
            return $custom_template_path . $template_name;
        }

        return $template;
    }

}
