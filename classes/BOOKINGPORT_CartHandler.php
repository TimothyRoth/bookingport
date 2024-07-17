<?php

class BOOKINGPORT_CartHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_empty_cart', [__CLASS__, 'empty_cart']);
        add_action('wp_ajax_nopriv_empty_cart', [__CLASS__, 'empty_cart']);

        add_action('wp_ajax_add_items_to_cart', [__CLASS__, 'add_items_to_cart']);
        add_action('wp_ajax_nopriv_add_items_to_cart', [__CLASS__, 'add_items_to_cart']);

        add_action('wp_ajax_add_order_remarks', [__CLASS__, 'add_order_remarks']);
        add_action('wp_ajax_nopriv_add_order_remarks', [__CLASS__, 'add_order_remarks']);

        add_action('wp_ajax_update_order_remarks', [__CLASS__, 'update_order_remarks']);
        add_action('wp_ajax_nopriv_update_order_remarks', [__CLASS__, 'update_order_remarks']);
    }

    /**
     * @throws JsonException
     */
    public static function empty_cart(): void
    {
        WC()->cart->empty_cart();
        $success = 'All cart items have been successfully removed';
        wp_die(json_encode($success, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function add_items_to_cart(): void
    {
        $user_session_items = $_SESSION['items'];
        $response = 'success';
        $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

        if (!empty($user_session_items)) {

            $new_cart_item_args = [
                'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
                'post__in' => $user_session_items
            ];

            $new_cart_items = new WP_Query($new_cart_item_args);
            $cart_items_meta_data = [];

            if ($new_cart_items->found_posts > 0) {

                foreach ($new_cart_items->posts ?? [] as $new_cart_item) {

                    $ID = $new_cart_item->ID;
                    $street = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                    $housenumber = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                    $size = '3m/1 Tapeziertisch';
                    $number = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);

                    $new_cart_item_meta = [
                        'id' => $ID,
                        'street' => $street,
                        'housenumber' => $housenumber,
                        'size' => $size,
                        'number' => $number,
                        'pavillon' => $_SESSION['ordersWithPavillon'][$ID]
                    ];

                    $current_cart = WC()->cart->get_cart();

                    // check if the stand/item is part of the current cart
                    $is_duplicate_cart_item = false;
                    $duplicate_cart_item_id = null;

                    foreach ($current_cart ?? [] as $cart_item_key => $cart_item) {
                        if (isset($cart_item['id']) && $cart_item['id'] === $new_cart_item_meta['id']) {
                            $is_duplicate_cart_item = true;
                            $duplicate_cart_item_id = $cart_item_key;
                            break;
                        }
                    }

                    // if the stand/item is a duplicate then replace the duplicate else create a new cart item
                    $cart_item_key = !$is_duplicate_cart_item ?
                        WC()->cart->add_to_cart($product_id, 1, 0, [], $new_cart_item_meta) :
                        self::replace_cart_item_with_duplicate($duplicate_cart_item_id, $new_cart_item_meta, $product_id);

                    $cart_items_meta_data[$cart_item_key] = $new_cart_item_meta;

                }
            }

            // Store the cart item metadata in the session to keep the session synchronized with the current cart
            $_SESSION['cart_items_meta_data'] = $cart_items_meta_data;

        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    private static function replace_cart_item_with_duplicate($existing_cart_item_key, $add_to_cart_item_meta, $product_id)
    {
        $cart_item_data = WC()->cart->get_cart_item($existing_cart_item_key);
        $cart_item_data['data'] = $add_to_cart_item_meta;
        WC()->cart->remove_cart_item($existing_cart_item_key);
        return WC()->cart->add_to_cart($product_id, 1, 0, [], $add_to_cart_item_meta);
    }

    /**
     * @throws JsonException
     */
    public static function add_order_remarks(): void
    {
        $order_remarks = $_POST['remarks'];
        $_SESSION['order_remarks'] = $order_remarks;
        $response = $_SESSION['order_remarks'] = $order_remarks;
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function update_order_remarks(): void
    {
        $order_remarks = $_POST['remarks'];
        $_SESSION['order_remarks'] = $order_remarks;
        $response = $_SESSION['order_remarks'] = $order_remarks;
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

}