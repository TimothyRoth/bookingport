<?php

class BOOKINGPORT_Header
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_head', [__CLASS__, 'bookingport_header']);
    }

    public static function bookingport_header(): void
    {
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);

        if (!isset($option_table[BOOKINGPORT_Settings::$option_general_use_header])) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/bookingport_header.php');
        }
    }
}