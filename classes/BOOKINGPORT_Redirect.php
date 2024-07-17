<?php

class BOOKINGPORT_Redirect
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_filter('wp_nav_menu_items', [__CLASS__, 'add_woocommerce_logout_button'], 10, 2);
        add_filter('logout_redirect', [__CLASS__, 'custom_logout_redirect'], 10, 2);
        add_action('template_redirect', [__CLASS__, 'page_redirection_rules']);
    }

    public static function custom_logout_redirect($logout_url, $redirect)
    {
        return home_url('/mein-konto');
    }

    public static function redirect_logged_out_user_to_login_page(): int
    {
        if (!is_user_logged_in()) {
            wp_redirect('/mein-konto');
        }
        return 0;
    }

    public static function page_redirection_rules(): void
    {
        /* block these pages for logged-out users */

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);

        $pagesUser = [

            $option_table[BOOKINGPORT_Settings::$option_redirects_booking_not_available],
            $option_table[BOOKINGPORT_Settings::$option_redirects_customer_requests],
            $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard],
            $option_table[BOOKINGPORT_Settings::$option_redirects_my_bookings],
            $option_table[BOOKINGPORT_Settings::$option_redirects_invoice],
            $option_table[BOOKINGPORT_Settings::$option_redirects_session_expired],
            $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking],
            $option_table[BOOKINGPORT_Settings::$option_redirects_booking_request_send],
            $option_table[BOOKINGPORT_Settings::$option_redirects_email_verification_successful],

            'warenkorb',
            'zur-kasse-gehen',
            'reservierungen-annehmen'

        ];

        $current_slug = get_post_field('post_name', get_queried_object_id());
        if (in_array($current_slug, $pagesUser, true)) {
            self::redirect_logged_out_user_to_login_page();
        }

        $pagesAdmin = [];

        if (isset($option_table[BOOKINGPORT_Settings::$option_save_settings_once])) {
            $pagesAdmin = [
                $option_table[BOOKINGPORT_Settings::$option_redirects_admin_map],
                $option_table[BOOKINGPORT_Settings::$option_redirects_admin_customers],
                $option_table[BOOKINGPORT_Settings::$option_redirects_expired_offers]
            ];
        }

        if (in_array($current_slug, $pagesAdmin, true) && !current_user_can('administrator')) {
            wp_redirect('/');
        }
    }

    public static function add_woocommerce_logout_button($items, $args)
    {
        if ($args->theme_location === 'logged_in' || $args->theme_location === 'admin') {
            // Check if the user is logged in
            if (is_user_logged_in()) {
                // Add the logout link to the menu-sidebar items
                $items .= '<li class="menu-sidebar-item menu-sidebar-item-logout">';
                $items .= '<a href="' . wp_logout_url(home_url()) . '">Logout</a>';
                $items .= '</li>';
            }
        }

        return $items;
    }

}