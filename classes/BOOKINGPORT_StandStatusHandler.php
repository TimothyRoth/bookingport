<?php

class BOOKINGPORT_StandStatusHandler
{

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {

        add_action('wp_ajax_update_sell_status', [__CLASS__, 'update_sell_status']);
        add_action('wp_ajax_nopriv_update_sell_status', [__CLASS__, 'update_sell_status']);

        add_action('wp_ajax_item_pavillon_status', [__CLASS__, 'item_pavillon_status']);
        add_action('wp_ajax_nopriv_item_pavillon_status', [__CLASS__, 'item_pavillon_status']);

        add_action('wp_ajax_reset_stand_meta', [__CLASS__, 'reset_stand_meta']);
        add_action('wp_ajax_nopriv_reset_stand_meta', [__CLASS__, 'reset_stand_meta']);

    }

    public static function set_stand_status_to_sold($itemIDs): void
    {
        foreach ($itemIDs as $itemID) {
            update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold);
        }
    }

    public static function save_order_id_in_stand_meta(array $order_meta): void
    {
        if (!empty($order_meta['items'])) {
            $args = [
                'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
                'posts_per_page' => -1,
                'post__in' => $order_meta['items']
            ];

            $ordered_stands = new WP_Query($args);

            if ($ordered_stands->found_posts > 0) {
                foreach ($ordered_stands->posts ?? [] as $ordered_stand) {
                    update_post_meta($ordered_stand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, $order_meta['order_id']);
                }
                session_destroy();
            }
        }

    }

    public static function reset_stand_data($itemID): void
    {
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free);
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, null);
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName, null);
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, null);
    }

    public static function set_stand_to_expired($itemID): void
    {
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired);
        update_post_meta($itemID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
    }

    public static function renew_reserved_stands_timestamps($items): void
    {
        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post__in' => $items
        ];

        $stands = new WP_Query($args);

        foreach ($stands->posts ?? [] as $stand) {
            update_post_meta($stand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
        }
    }

    public static function reset_user_cart_and_session(): void
    {
        session_start();
        session_destroy();
        WC()->cart->empty_cart();
    }

    public static function validate_stands_client_side($items): bool
    {
        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post__in' => $items
        ];

        $stands = new WP_Query($args);
        $current_user = get_current_user_id();

        foreach ($stands->posts ?? [] as $stand) {
            $timestamp = get_post_meta($stand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, true);
            $current_product_owner = get_post_meta($stand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, true);
            $currentTime = time();
            $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;

            if ((int)$current_product_owner !== (int)$current_user) {
                return true;
            }

            if ($timeDifferenceInMin >= 10) {
                return true;
            }
        }

        return false;
    }

    public static function handle_customer_reserved_stand_status_server_side(): void
    {

        $customer_reserved_stands_args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved,
                    'compare' => '=',
                ]
            ]
        ];

        $customer_reserved_stands = new WP_Query($customer_reserved_stands_args);

        foreach ($customer_reserved_stands->posts ?? [] as $customer_reserved_stand) {

            $timestamp = get_post_meta($customer_reserved_stand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, true);
            $currentTime = time();
            $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;

            if ($timeDifferenceInMin >= 10) {
                self::reset_stand_data($customer_reserved_stand->ID);
            }
        }

    }

    public static function handle_admin_reserved_stand_status_server_side(): void
    {

        $admin_reserved_stands_args = [
            'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
                    'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusAdminAccepted,
                    'compare' => '=',
                ]
            ]
        ];

        $admin_reserved_requests = new WP_Query($admin_reserved_stands_args);

        foreach ($admin_reserved_requests->posts ?? [] as $admin_reserved_request) {
            // get the time when the admin made the offer and current time and check if the difference is greater than 48 hours
            $timestamp = get_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$Cpt_MarketOfferTime, true);
            $currentTime = time();
            $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;
            if ($timeDifferenceInMin >= 1) { #toDo back to 2880 when Testing

                // send an e-mail to the admin, that the offer has expired
                $headers = [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=UTF-8'
                ];

                $additional_headers = implode("\r\n", $headers);
                $option_table = get_option(BOOKINGPORT_Settings::$option_table);
                $admin_email = $option_table[BOOKINGPORT_Settings::$option_email_booking_request];
                $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];
                $message = "Hallo admin, <br/> Das Angebot mit der Angebotsnummer " . $market_prefix . $admin_reserved_request->ID . " wurde nicht innerhalb des vorgegebenen Zeitfensters von 48 Stunden vom Kunden angenommen.";
                $subject = 'Abgelaufenes Angebot ' . $market_prefix . $admin_reserved_request->ID;
                wp_mail($admin_email, $subject, $message, $additional_headers);

                // change the status to expired and set the post to draft when the offer is expired
                update_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired);
                wp_update_post([
                    'ID' => $admin_reserved_request->ID,
                    'post_status' => 'draft'
                ]);

                // reset all stands that are part of the offer
                $stands = get_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);

                foreach ($stands ?? [] as $stand) {
                    self::set_stand_to_expired($stand);
                }
            }
        }
    }

    /**
     * @throws JsonException
     */
    public static function update_sell_status(): void
    {
        $response = [];
        $error_messages = [];
        $stands_requested_by_user = $_POST['items'] ?? [];
        $is_edit_request = $_POST['editRequest'];
        $current_pre_cart_session = $_SESSION['items'] ?? [];
        $requesting_user_id = get_current_user_id();
        $requesting_user = get_user_by('id', $requesting_user_id);
        $requesting_user_name = $requesting_user->billing_first_name . ' ' . $requesting_user->billing_last_name;

        foreach ($stands_requested_by_user ?? [] as $item_ID) {
            $user_id_is_saved_in_product = get_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, true);
            if ($user_id_is_saved_in_product === true && $user_id_is_saved_in_product != $requesting_user_id) {
                $error_msg = [
                    'message' => 'Leider wurde der Stand ' . get_the_title($item_ID) . ' bereits an einen anderen Benutzer vergeben.'
                ];
                $error_messages[] = $error_msg;
            } else {
                // check if the stand sell status is the correct one. If so, update the stand meta accordingly
                $stand_sell_status = get_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true);
                if ($stand_sell_status !== BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer) {
                    update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved);
                    update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, $requesting_user_id);
                    update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName, $requesting_user_name);
                    update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
                }

                // add the items to the user session, if they are not already part of it
                if (!in_array($item_ID, $current_pre_cart_session, true)) {
                    $current_pre_cart_session[] = $item_ID;
                }
            }
        }

        if (!empty($current_pre_cart_session)) {

            $requested_items_args = [
                'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
                'post__in' => $current_pre_cart_session
            ];

            $requested_items = new WP_Query($requested_items_args);
            $product_ID = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

            foreach ($requested_items->posts ?? [] as $requested_item) {
                $ID = $requested_item->ID;
                $has_pavillon = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                $street = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $number = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';
                $price = wc_get_product($product_ID)->get_price();
                $size = '';

                if (current_user_can('privat') || current_user_can('administrator')) {
                    $size = '3m/1 Tapeziertisch';
                }

                $requested_item = [
                    'id' => $ID,
                    'street' => $street . ' ' . $housenumber,
                    'number' => $number,
                    'image_urls' => [
                        'marker' => $imageUrl . 'stand-marker-white.svg',
                        'number' => $imageUrl . 'stand-number-white.svg',
                        'space' => $imageUrl . 'space-white.svg',
                        'pavillon' => $imageUrl . 'pavillon-white.svg',
                        'delete' => $imageUrl . 'delete.svg'
                    ]
                ];


                if (current_user_can('privat')) {
                    $requested_item['size'] = $size;
                    $requested_item['price'] = $price;
                    $requested_item['has_pavillon'] = ($has_pavillon === '1');
                }

                $response[] = $requested_item;
            }

            $_SESSION['items'] = $current_pre_cart_session;

        }

        $output = [
            'response' => $response,
            'error_messages' => $error_messages,
            'current_cart_selection' => $current_pre_cart_session,
            'is_edit_request' => $is_edit_request
        ];

        wp_die(json_encode($output, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function item_pavillon_status(): void
    {
        $is_checked = $_POST['isChecked'];
        $item_ID = $_POST['itemID'] ?? null;
        $_SESSION['ordersWithPavillon'][$item_ID] = $is_checked;
        $response = $_SESSION['ordersWithPavillon'];
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function reset_stand_meta(): void
    {
        $item_to_delete = $_POST['itemToDelete'];
        $response = 200;

        update_post_meta($item_to_delete, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free);
        update_post_meta($item_to_delete, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
        update_post_meta($item_to_delete, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, null);
        update_post_meta($item_to_delete, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName, null);
        update_post_meta($item_to_delete, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, null);

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    public static function reset_stands($items): void
    {

        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post__in' => $items
        ];

        $stands = new WP_Query($args);

        foreach ($stands->posts ?? [] as $stand) {
            $stand_id = $stand->ID;
            $stand_status = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true);
            if ($stand_status === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved) {
                self::reset_stand_data($stand->ID);
            }
        }
    }

    public static function reset_all_stands(): bool
    {
        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
        ];

        $stands = new WP_Query($args);

        foreach ($stands->posts ?? [] as $stand) {
            self::reset_stand_data($stand->ID);
        }

        return false;
    }
}


