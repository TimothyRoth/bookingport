<?php

/**
 * Plugin Name: booking port marketplace for Woocommerce
 * Plugin URI: www.timothy-roth.de
 * Description: A plugin for managing marketplaces and bookings via WooCommerce. This plugin requires the prior installation of WooCommerce.
 * Version: 1.0.0
 * Author: Timothy Roth
 * Author URI: www.timothy-roth.de
 * License: GPL2
 * Text Domain: wp_bookingport
 * Domain Path: /languages
 */

define('BOOKINGPORT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BOOKINGPORT_PLUGIN_URI', plugin_dir_url(__FILE__));
define('BOOKINGPORT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BOOKINGPORT_PLUGIN_FILE_PATH', __FILE__);

load_plugin_textdomain('wp_bookingport', FALSE, basename(__DIR__) . '/languages/');

spl_autoload_register(static function ($class_name) {
    $class_file = BOOKINGPORT_PLUGIN_PATH . '/classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        include $class_file;
    }
});

// include the file exporter class (external class)
require_once(BOOKINGPORT_PLUGIN_PATH . "/vendor/autoload.php");

// Initialize instances of classes
$instances = [
    'BOOKINGPORT_Installation',
    'BOOKINGPORT_Settings',
    'BOOKINGPORT_User',
    'BOOKINGPORT_Header',
    'BOOKINGPORT_Footer',
    'BOOKINGPORT_CptStands',
    'BOOKINGPORT_CptFreespace',
    'BOOKINGPORT_CptMarket',
    'BOOKINGPORT_CptFAQ',
    'BOOKINGPORT_RegistrationMailValidation',
    'BOOKINGPORT_WCHandler',
    'BOOKINGPORT_MapHandler',
    'BOOKINGPORT_StandStatusHandler',
    'BOOKINGPORT_Cron',
    'BOOKINGPORT_FileExporter',
    'BOOKINGPORT_CartHandler',
    'BOOKINGPORT_PreCartHandler',
    'BOOKINGPORT_MarketHandler',
    'BOOKINGPORT_CustomerDataHandler',
    'BOOKINGPORT_PDFHandler',
    'BOOKINGPORT_Shortcodes',
    'BOOKINGPORT_Redirect',
];

foreach ($instances as $instance_name) {
    ${$instance_name} = new $instance_name();
    ${$instance_name}::init();
}

// #toDo - requires fixing -> should use wc_session instead.
add_action('wp_loaded', function () {
    if (!session_id()) {
        session_start();
    }
});


