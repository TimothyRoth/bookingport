<?php

class BOOKINGPORT_PreCartHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_delete_item', [__CLASS__, 'delete_item']);
        add_action('wp_ajax_nopriv_delete_item', [__CLASS__, 'delete_item']);

        add_action('wp_ajax_filter_stands', [__CLASS__, 'filter_stands']);
        add_action('wp_ajax_nopriv_filter_stands', [__CLASS__, 'filter_stands']);

        add_action('wp_ajax_show_current_pre_cart_selection', [__CLASS__, 'show_current_pre_cart_selection']);
        add_action('wp_ajax_nopriv_show_current_pre_cart_selection', [__CLASS__, 'show_current_pre_cart_selection']);

    }

    /**
     * @throws JsonException
     */
    public static function show_current_pre_cart_selection(): void
    {
        $response = [
            'current' => [],
            'reserved' => []
        ];

        $reserved_stands = $_SESSION['items'] ?? null;

        foreach ($reserved_stands ?? [] as $reserved_stand_id) {
            $response['reserved'][] = get_the_title($reserved_stand_id);
        }

        if (!empty($_POST['items'])) {

            $current_items = $_POST['items'];
            foreach ($current_items ?? [] as $current_item_id) {
                $response['current'][] = get_the_title($current_item_id);
            }
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));

    }

    /**
     * @throws JsonException
     */
    public
    static function filter_stands(): void
    {

        $response = [];
        $search_query = sanitize_text_field($_POST['searchQuery']);

        /* This value comes from the add listener marker event in the map */
        $clicked_stand = $_POST['itemID'];

        $filter_args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'order' => 'ASC',
            'post_status' => 'publish',
            's' => $search_query, // Add search query parameter
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                    'compare' => '=',
                ]
            ]
        ];

        /* If we have the add listener marker event we filter the result for that specific item*/
        if (isset($clicked_stand) && !empty($clicked_stand)) {
            $filter_args['p'] = $clicked_stand;
        }

        $filtered_stands = new WP_Query($filter_args);
        $product_ID = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
        $stand_lat = null;
        $stand_lng = null;

        if ($filtered_stands->found_posts > 0) {
            foreach ($filtered_stands->posts ?? [] as $filtered_stand) {
                $ID = $filtered_stand->ID;
                $has_pavillon = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                $street = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $size = '3m/1 Tapeziertisch';
                $price = wc_get_product($product_ID)->get_price();
                $number = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';

                if (empty($stand_lat) && empty($stand_lng)) {
                    $stand_lat = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true);
                    $stand_lng = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true);
                }

                $stand = [
                    'id' => $ID,
                    'geo' => [
                        'lat' => $stand_lat,
                        'lng' => $stand_lng
                    ],
                    'street' => $street . ' ' . $housenumber,
                    'number' => $number,
                    'has_pavillon' => ($has_pavillon === '1'),
                    'image_urls' => [
                        'marker' => $imageUrl . 'stand-marker-blue.svg',
                        'number' => $imageUrl . 'stand-number-blue.svg',
                        'space' => $imageUrl . 'space-blue.svg',
                        'pavillon' => $imageUrl . 'pavillon-blue.svg',
                    ]
                ];

                if (current_user_can('privat')) {
                    $stand['size'] = $size;
                    $stand['price'] = $price;
                }

                $response[] = $stand;

            }

        } else {
            $response[] = ['message' => 'Leider konnten wir keine Stände finden, die Ihrer Suchanfrage entsprechen.'];
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public
    static function delete_item(): void
    {
        $item_ID = $_POST['itemToDelete'];
        $current_session = $_SESSION['items'] ?? [];
        $updated_session = array_diff($current_session, [$item_ID]);
        $updated_session = json_decode(json_encode($updated_session, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $_SESSION['items'] = $updated_session;

        // IF the stand status is not a customer requested stand, the metadata will be reset. Else it will only be removed from the session
        // Without this adjustment, stands can not hold their request status when being added or deleted from pre-cart by the admin
        $stand_status = get_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true);
        if ($stand_status !== BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer) {
            BOOKINGPORT_StandStatusHandler::reset_stand_data($item_ID);
        }

        wp_die(json_encode($item_ID, JSON_THROW_ON_ERROR));
    }
}