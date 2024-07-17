<?php

class BOOKINGPORT_Shortcodes

{
    public static array $shortcodes = [
        'bookingport_registration',
        'bookingport_stand_booking',
        'bookingport_invoice',
        'bookingport_admin_map',
        'bookingport_admin_customers',
        'bookingport_customer_requests',
        'bookingport_accept_reservations',
        'bookingport_my_bookings',
        'bookingport_registration_successful',
        'bookingport_session_expired',
        'bookingport_offers_and_requests',
        'bookingport_dashboard',
        'bookingport_booking_not_available',
        'bookingport_email_verification_successful',
        'bookingport_booking_request_send',
        'bookingport_expired_offers'
    ];

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        foreach (self::$shortcodes ?? [] as $shortcode) {
            self::register_shortcode($shortcode);
        }

        add_filter('body_class', [__CLASS__, 'add_template_body_class']);

        /* This hook applies the bookingport page template to all pages that are using plugin shortcodes */
        add_filter('page_template', [__CLASS__, 'force_plugin_page_template']);
    }

    public static function register_shortcode($shortcode): void
    {
        $template_path = BOOKINGPORT_PLUGIN_PATH . '/inc/shortcode_templates/' . $shortcode . '.php';

        if (file_exists($template_path)) {
            add_shortcode($shortcode, function () use ($template_path) {
                include($template_path);
            });
        }
    }

    public static function add_template_body_class($classes)
    {
        foreach (self::$shortcodes ?? [] as $shortcode) {
            if (is_singular() && has_shortcode(get_post()->post_content, $shortcode)) {
                $classes[] = $shortcode;
            }
        }
        return $classes;
    }

    public static function force_plugin_page_template($template)
    {
        foreach (self::$shortcodes ?? [] as $shortcode) {
            if (is_singular() && has_shortcode(get_post()->post_content, $shortcode)) {
                $custom_template = BOOKINGPORT_PLUGIN_PATH . '/inc/page_templates/page-template-bookingport.php';

                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }
        return $template;
    }

}