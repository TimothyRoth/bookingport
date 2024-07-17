<?php

class BOOKINGPORT_Footer
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_footer', [__CLASS__, 'bookingport_footer']);
    }

    public static function bookingport_footer(): void
    {

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);

        if (!isset($option_table[BOOKINGPORT_Settings::$option_general_use_footer])) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/bookingport_footer.php');
        }
    }
}