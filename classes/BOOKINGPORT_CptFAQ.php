<?php

class BOOKINGPORT_CptFAQ
{

    public static string $Cpt_FAQ = 'faq';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'register_faq_post_type']);
    }

    public static function register_faq_post_type(): void
    {

        $labels = [
            'name' => _x('FAQ', 'Post Type General Name', 'bookingport'),
            'singular_name' => _x('FAQ', 'Post Type Singular Name', 'bookingport'),
            'menu_name' => __('FAQ', 'bookingport'),
            'name_admin_bar' => __('FAQ', 'bookingport'),
            'archives' => __('FAQ archiv', 'bookingport'),
            'attributes' => __('FAQ attribute', 'bookingport'),
            'parent_item_colon' => __('Übergeordnete FAQs:', 'bookingport'),
            'all_items' => __('Alle FAQs', 'bookingport'),
            'add_new_item' => __('Neuen FAQ hinzufügen', 'bookingport'),
            'add_new' => __('Neuen FAQ hinzufügen', 'bookingport'),
            'new_item' => __('Neuer FAQ', 'bookingport'),
            'edit_item' => __('FAQ bearbeiten', 'bookingport'),
            'update_item' => __('FAQ updaten', 'bookingport'),
            'view_item' => __('FAQ ansehen', 'bookingport'),
            'view_items' => __('FAQ ansehen', 'bookingport'),
            'search_items' => __('FAQ suchen', 'bookingport'),
            'not_found' => __('FAQ nicht gefunden', 'bookingport'),
            'not_found_in_trash' => __('FAQ nicht im Papierkorb gefunden', 'bookingport'),
            'insert_into_item' => __('Insert into faq', 'bookingport'),
            'uploaded_to_this_item' => __('Für diesen FAQ hochladen', 'bookingport'),
            'items_list' => __('FAQ liste', 'bookingport'),
            'items_list_navigation' => __('FAQ liste navigation', 'bookingport'),
            'filter_items_list' => __('Filter FAQ liste', 'bookingport'),
        ];

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);

        !empty($option_table[BOOKINGPORT_Settings::$option_save_settings_once]) ? $rewrite_slug = $option_table[BOOKINGPORT_Settings::$option_redirects_faq] : $rewrite_slug = self::$Cpt_FAQ;

        $args = [
            'label' => __('FAQ', 'bookingport'),
            'description' => __('FAQ verwalten', 'bookingport'),
            'labels' => $labels,
            'supports' => ['title', 'editor'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-businessman',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => $rewrite_slug, 'with_front' => true],
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_FAQ, $args);
        flush_rewrite_rules();

    }

}