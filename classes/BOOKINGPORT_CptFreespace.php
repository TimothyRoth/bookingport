<?php

class BOOKINGPORT_CptFreespace
{
    public static string $Cpt_Freespace = 'bookingport_fspace';
    public static string $Cpt_Freespace_Lat = 'freespace_latitude';
    public static string $Cpt_Freespace_Lng = 'freespace_longitude';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'registerCPTFreespace']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('admin_footer', [__CLASS__, 'admin_footer']);
        add_action('save_post', [__CLASS__, 'save_fields']);
    }

    public static function registerCPTFreespace(): void
    {
        $labels = [
            'name' => _x('Freiflächen', 'post type general name', 'bookingport'),
            'singular_name' => _x('Freifläche', 'post type singular name', 'bookingport'),
            'menu_name' => __('Freiflächen', 'bookingport'),
            'name_admin_bar' => __('Freifläche', 'bookingport'),
            'archives' => __('Freiflächen Archiv', 'bookingport'),
            'attributes' => __('Freiflächen Attribute', 'bookingport'),
            'parent_item_colon' => __('Übergeordnete Freifläche:', 'bookingport'),
            'all_items' => __('Alle Freiflächen', 'bookingport'),
            'add_new_item' => __('Neue Freifläche hinzufügen', 'bookingport'),
            'add_new' => __('Neue Freifläche hinzufügen', 'bookingport'),
            'new_item' => __('Neue Freifläche', 'bookingport'),
            'edit_item' => __('Freifläche bearbeiten', 'bookingport'),
            'update_item' => __('Freifläche aktualisieren', 'bookingport'),
            'view_item' => __('Freifläche anzeigen', 'bookingport'),
            'view_items' => __('Freiflächen anzeigen', 'bookingport'),
            'search_items' => __('Freiflächen durchsuchen', 'bookingport'),
            'not_found' => __('Keine Freifläche gefunden', 'bookingport'),
            'not_found_in_trash' => __('Keine Freifläche im Papierkorb gefunden', 'bookingport'),
            'uploaded_to_this_item' => __('Zu dieser Freifläche hochgeladen', 'bookingport'),
            'items_list' => __('Freiflächenliste', 'bookingport'),
            'items_list_navigation' => __('Freiflächenlisten-Navigation', 'bookingport'),
            'filter_items_list' => __('Freiflächenliste filtern', 'bookingport'),
        ];

        $args = [
            'label' => __('Freiflächen', 'bookingport'),
            'description' => __('Verwaltung von Freiflächen', 'bookingport'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-admin-site',
            'menu_position' => 4,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            '_builtin' => false,
            'query_var' => true,
            'rewrite' => ['slug' => self::$Cpt_Freespace, 'with_front' => true],
            'supports' => ['title', 'thumbnail', 'editor'],
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_Freespace, $args);
        flush_rewrite_rules();
    }

    public static function add_meta_boxes(): void
    {

        add_meta_box(
            'Geodaten',
            __('Geodaten', 'bookingport'),
            [__CLASS__, 'freespace_geodata_callback'],
            self::$Cpt_Freespace,
            'side',
            'low'
        );
    }

    public static function freespace_geodata_callback($post): void
    {
        wp_nonce_field(self::$Cpt_Freespace . 'PostMeta_data', self::$Cpt_Freespace . 'PostMeta_nonce'); ?>

        <label for="<?= self::$Cpt_Freespace_Lat ?>">Latitude</label>
        <input id="<?= self::$Cpt_Freespace_Lat ?>" name="<?= self::$Cpt_Freespace_Lat ?>" type="text"
               class="box-input"
               value="<?php echo get_post_meta($post->ID, self::$Cpt_Freespace_Lat, true); ?>"
               placeholder="<?php _e('Latitude', 'bookingport'); ?>"/>

        <label for="<?= self::$Cpt_Freespace_Lng ?>">Longitude</label>
        <input id="<?= self::$Cpt_Freespace_Lng ?>" name="<?= self::$Cpt_Freespace_Lng ?>" type="text" class="box-input"
               value="<?php echo get_post_meta($post->ID, self::$Cpt_Freespace_Lng, true); ?>"
               placeholder="<?php _e('Longitude', 'bookingport'); ?>"/>

        <?php
    }

    public static function save_fields($post_id)
    {
        if (!isset($_POST[self::$Cpt_Freespace . 'PostMeta_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST[self::$Cpt_Freespace . 'PostMeta_nonce'];
        if (!wp_verify_nonce($nonce, self::$Cpt_Freespace . 'PostMeta_data')) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Update metafields
        if (isset($_POST['freespace_latitude'])) {
            update_post_meta($post_id, 'freespace_latitude', sanitize_textarea_field($_POST['freespace_latitude']));
        }
        if (isset($_POST['freespace_longitude'])) {
            update_post_meta($post_id, 'freespace_longitude', sanitize_textarea_field($_POST['freespace_longitude']));
        }

        return $post_id;
    }

    public static function admin_footer(): void
    {
        ?>
        <style>
            .box-input {
                width: 100%;
                padding: 5px 10px;
                margin-bottom: 15px;
            }

            img {
                max-width: 100%;
            }

            ::-webkit-input-placeholder {
                font-style: italic;
            }

            ::-moz-placeholder {
                font-style: italic;
            }

            :-ms-input-placeholder {
                font-style: italic;
            }

            :-moz-placeholder {
                font-style: italic;
            }

        </style>

        <?php

    }


}