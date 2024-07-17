<?php

class BOOKINGPORT_CptStands
{
    public static string $Cpt_Stands = 'bookingport_stands';
    public static string $Cpt_Stand_Meta_GeoLatitude = 'stand_meta_geoLatitude';
    public static string $Cpt_Stand_Meta_GeoLongitude = 'stand_meta_geoLongitude';
    public static string $Cpt_Stand_Meta_GeoStreetname = 'stand_meta_geoStreetname';
    public static string $Cpt_Stand_Meta_GeoStreetNumber = 'stand_meta_geoStreetNumber';
    public static string $Cpt_Stand_Meta_GeneralNumber = 'stand_meta_generalNumber';
    public static string $Cpt_Stand_Meta_GeneralSellStatus = 'stand_meta_generalSellStatus';
    public static string $Cpt_Stand_Meta_GeneralInvoiceID = 'stand_meta_generalInvoiceID';
    public static string $Cpt_Stand_Meta_GeneralSellStatusLastChange = 'stand_meta_generalSellStatusLastChange';
    public static string $Cpt_Stand_Meta_GeneralPavillon = 'stand_meta_generalPavillon';
    public static string $Cpt_Stand_Meta_GeneralSellUserId = 'stand_meta_generalSellUserId';
    public static string $Cpt_Stand_Meta_GeneralSellUserName = 'stand_meta_generalSellUserName';
    public static string $Cpt_Stand_Status_Free = '0';
    public static string $Cpt_Stand_Status_Reserved = '1';
    public static string $Cpt_Stand_Status_Sold = '2';
    public static string $Cpt_Stand_Status_Reserved_By_Admin = '3';
    public static string $Cpt_Stand_Status_Requested_By_Customer = '4';
    public static string $Cpt_Stand_Status_Admin_Offer_Expired = '5';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'registerCPTStands']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_fields']);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'stand_meta_geo',
            __('Stand: Geo', 'bookingport'),
            [__CLASS__, 'stand_meta_geodata_callback'],
            self::$Cpt_Stands,
            'advanced',
            'default'
        );

        add_meta_box(
            'stand_meta_general',
            __('Stand: General', 'bookingport'),
            [__CLASS__, 'stand_meta_general_callback'],
            self::$Cpt_Stands,
            'advanced',
            'default'
        );

    }

    public static function registerCPTStands(): void
    {

        $labels = [
            'name' => _x('Stände', 'post type general name', 'bookingport'),
            'singular_name' => _x('Stand', 'post type singular name', 'bookingport'),
            'add_new' => _x('Hinzufügen', 'Stand hinzufügen', 'bookingport'),
            'add_new_item' => __('Neuen Stand hinzufügen', 'bookingport'),
            'edit_item' => __('Stand bearbeiten', 'bookingport'),
            'new_item' => __('Neuer Stand', 'bookingport'),
            'view_item' => __('Stand anzeigen', 'bookingport'),
            'search_items' => __('Nach Ständen suchen', 'bookingport'),
            'not_found' => __('Keine Stände gefunden', 'bookingport'),
            'not_found_in_trash' => __('Keine Stände im Papierkorb', 'bookingport'),
            'parent_item_colon' => ''
        ];

        $args = [
            'label' => __('Stände', 'bookingport'),
            'description' => __('Stände Beschreibung', 'bookingport'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-admin-multisite',
            'menu_position' => 2,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            '_builtin' => false,
            'query_var' => true,
            'rewrite' => ['slug' => self::$Cpt_Stands, 'with_front' => true],
            'supports' => ['title', 'editor'],
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_Stands, $args);
        flush_rewrite_rules();
    }

    public static function stand_meta_general_callback($post): void
    {

        wp_nonce_field(self::$Cpt_Stands . 'PostMeta_data', self::$Cpt_Stands . 'PostMeta_nonce');

        $number = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralNumber, true);
        $sellStatusLastChange = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellStatusLastChange, true);
        $invoiceID = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralInvoiceID, true);
        $sellUserId = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserId, true);
        $sellUserName = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserName, true);
        $pavillon = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralPavillon, true);
        $sellStatus_Label = self::get_sell_status_label($post->ID);

        if ($sellStatusLastChange) {
            $sellStatusLastChange = date('m.d.Y H:i:s', (int)$sellStatusLastChange);
        } ?>

        <div>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>">Stand-Nummer</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>" value="<?= $number ?>"></p>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatus ?>">Verkaufs-Status</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatus ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatus ?>"
                       value="<?= $sellStatus_Label ?>" disabled>
            </p>
            <?php if (!empty($invoiceID)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralInvoiceID ?>">Rechnungsnummer</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralInvoiceID ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralInvoiceID ?>"
                           value="<?= $invoiceID ?>" disabled>
                </p>
            <?php } ?>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChange ?>">Datum der letzten
                    Statusänderung</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChange ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChange ?>"
                       value="<?= $sellStatusLastChange ?>" disabled></p>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserId ?>">User-ID</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserId ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellUserId ?>" value="<?= $sellUserId ?>" disabled>
            </p>
            <?php if (!empty($sellUserName)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserName ?>">User-Name</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserName ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralSellUserName ?>" value="<?= $sellUserName ?>" disabled>
                </p>
            <?php } ?>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralPavillon ?>">Pavilion</label>
                <input type="checkbox" name="<?= self::$Cpt_Stand_Meta_GeneralPavillon ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralPavillon ?>"
                       value="1" <?php checked($pavillon, '1'); ?>>
            </p>
        </div>

        <?php

    }

    public static function stand_meta_geodata_callback($post): void
    {

        wp_nonce_field(self::$Cpt_Stands . 'PostMeta_data', self::$Cpt_Stands . 'PostMeta_nonce');

        $geoLatitude = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoLatitude, true);
        $geoLongitude = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoLongitude, true);
        $streetName = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoStreetname, true);
        $streetNumber = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoStreetNumber, true);

        ?>
        <div>
            <label for="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>">Latitude:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>" value="<?= $geoLatitude ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>">Longitude:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>" value="<?= $geoLongitude ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>">Straße:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>" value="<?= $streetName ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>">Hausnummer:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>" value="<?= $streetNumber ?>">
        </div>
        <?php
    }

    public static function save_fields($post_id)
    {

        if (!isset($_POST[self::$Cpt_Stands . 'PostMeta_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST[self::$Cpt_Stands . 'PostMeta_nonce'];
        if (!wp_verify_nonce($nonce, self::$Cpt_Stands . 'PostMeta_data')) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        /*  Save the status of the stand as free the first time, the stand is added to the custom post type */
        if (empty(get_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatus, true))) {
            update_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatus, self::$Cpt_Stand_Status_Free);
        }

        $meta_fields = [
            self::$Cpt_Stand_Meta_GeoLatitude,
            self::$Cpt_Stand_Meta_GeoLongitude,
            self::$Cpt_Stand_Meta_GeoStreetname,
            self::$Cpt_Stand_Meta_GeoStreetNumber,
            self::$Cpt_Stand_Meta_GeneralSellStatus,
            self::$Cpt_Stand_Meta_GeneralNumber,
            self::$Cpt_Stand_Meta_GeneralSellStatusLastChange,
            self::$Cpt_Stand_Meta_GeneralSellUserId
        ];

        foreach ($meta_fields ?? [] as $meta_field) {
            if (isset($_POST[$meta_field])) {
                update_post_meta($post_id, $meta_field, $_POST[$meta_field]);
            }
        }

        $pavillon_value = isset($_POST[self::$Cpt_Stand_Meta_GeneralPavillon]) ? '1' : '0';
        update_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralPavillon, $pavillon_value);

        return $post_id;
    }

    public static function get_sell_status_label($post_id): string
    {

        $sell_status = get_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatus, true);

        return match ($sell_status) {
            self::$Cpt_Stand_Status_Free => 'Frei',
            self::$Cpt_Stand_Status_Reserved => 'Reserviert',
            self::$Cpt_Stand_Status_Sold => 'Verkauft',
            self::$Cpt_Stand_Status_Reserved_By_Admin => 'Reserviert (Admin)',
            self::$Cpt_Stand_Status_Requested_By_Customer => 'Angefragt',
            self::$Cpt_Stand_Status_Admin_Offer_Expired => 'Abgelaufen',
            default => 'kein Status'
        };
    }

    public static function get_amount_of_stands_by_status(string $status): int
    {
        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'meta_query' => [

                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => $status,
                    'compare' => '=',
                ],
            ]
        ];

        $stands = new WP_Query($args);
        return count($stands->posts);
    }
}
