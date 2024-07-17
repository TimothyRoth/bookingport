<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.4.0
 */
defined('ABSPATH') || exit;

BOOKINGPORT_Redirect::redirect_logged_out_user_to_login_page();
do_action('woocommerce_before_cart');

$order_remarks = $_SESSION['order_order_remarks'] ?? 'Keine Anmerkungen';
$items_requested_by_customer = $_SESSION['items'] ?? null;

$session_expired = BOOKINGPORT_StandStatusHandler::validate_stands_client_side($items_requested_by_customer);

if ($session_expired) {
    $option_table = get_option(BOOKINGPORT_Settings::$option_table);
    BOOKINGPORT_StandStatusHandler::reset_user_cart_and_session();
    BOOKINGPORT_StandStatusHandler::reset_stands($items_requested_by_customer);
    wp_redirect('/' . $option_table[BOOKINGPORT_Settings::$option_redirects_session_expired]);
} else {
    BOOKINGPORT_StandStatusHandler::renew_reserved_stands_timestamps($items_requested_by_customer);
} ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <?php do_action('woocommerce_before_cart_table'); ?>
    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
        <thead>
        <tr>
            <?php if (current_user_can('privat')) { ?>
                <th class="product-remove"><span
                <th class="product-remove"><span
                            class="screen-reader-text"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
            <?php } ?>
            <th class="product-thumbnail"><span
                        class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
            <th class="product-name"><?php esc_html_e('Stand/Stände', 'woocommerce'); ?></th>
            <!--            <th class="product-price">--><?php //esc_html_e( 'Price', 'woocommerce' ); ?><!--</th>-->
            <!--            <th class="product-quantity">-->
            <?php //esc_html_e( 'Quantity', 'woocommerce' ); ?><!--</th>-->
            <th class="product-subtotal"><?php esc_html_e('Preis', 'woocommerce'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php do_action('woocommerce_before_cart_contents');
        $wc_cart = WC()->cart->get_cart();

        foreach ($wc_cart ?? [] as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                ?>

                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                    <?php if (current_user_can('privat')) { ?>
                        <td class="product-remove <?php
                        // Add 'remove_single_item' class if 'id' key is set
                        if (isset($cart_item['id'])) {
                            echo ' remove_single_item';
                        }
                        // Add 'remove_array' class if 'articles_reserved_by_admin' key is set
                        if (isset($cart_item['articles_reserved_by_admin'])) {
                            echo ' remove_array';
                        }
                        ?>"
                            data-src="<?php
                            // Set 'data-src' attribute to 'id' value if 'id' key is set
                            if (isset($cart_item['id'])) {
                                echo $cart_item['id'];
                            }
                            // If 'articles_reserved_by_admin' key is set, set 'data-src' to a comma-separated list of its keys
                            if (isset($cart_item['articles_reserved_by_admin'])) {
                                $keys = array_keys($cart_item['articles_reserved_by_admin']);
                                echo implode(', ', $keys);
                            }
                            ?>">

                            <?php
                            // Output the remove link for the cart item
                            echo apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_html__('Remove this item', 'woocommerce'),
                                    esc_attr($product_id),
                                    esc_attr($_product->get_sku())
                                ),
                                $cart_item_key
                            );
                            ?>
                        </td>

                    <?php } ?>

                    <td class="product-thumbnail">
                        <?php
                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                        if (!$product_permalink) {
                            echo $thumbnail; // PHPCS: XSS ok.
                        } else {
                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                        }
                        ?>
                    </td>

                    <td class="product-name" data-title="Stand">
                        <?php
                        if (!empty($cart_item['articles_reserved_by_admin'])) {
                            $has_pavillon = false;
                            foreach ($cart_item['articles_reserved_by_admin'] as $item) {
                                if ($item['pavillon'] == 'true') {
                                    $has_pavillon = true;
                                }
                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $item['street'] . ' ' . $item['housenumber'] . ', ' . $item['number'] . '<br/>'));
                            }

                            if ($has_pavillon === true) {
                                echo 'inkl. Pavillon <br/>';
                            }

                            $counter = 0;
                            foreach ($cart_item['articles_reserved_by_admin'] as $item) {
                                if ($counter === 1) {
                                    break;
                                }
                                if (!empty($item['width'])) {
                                    echo 'Breite: ' . $item['width'] . 'm' . '<br/>';
                                }
                                if (!empty($item['depth'])) {
                                    echo 'Tiefe: ' . $item['depth'] . 'm';
                                }
                                $counter++;
                            }

                        } else {
                            $pavillon = $cart_item['pavillon'] === 'true' ? ', inkl. Pavillon' : '';
                            echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $cart_item['street'] . ' ' . $cart_item['housenumber'] . ', ' . $cart_item['number'] . $pavillon));
                        }

                        do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                        // Meta data.
                        echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

                        // Backorder notification.
                        if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                            echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                        }
                        ?>
                    </td>

                    <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                        <?php
                        if ($_product->is_sold_individually()) {
                            $min_quantity = 1;
                            $max_quantity = 1;
                        } else {
                            $min_quantity = 0;
                            $max_quantity = $_product->get_max_purchase_quantity();
                        }

                        $product_quantity = woocommerce_quantity_input(
                            [
                                'input_name' => "cart[$cart_item_key][qty]",
                                'input_value' => $cart_item['quantity'],
                                'max_value' => $max_quantity,
                                'min_value' => $min_quantity,
                                'product_name' => $_product->get_name(),
                            ],
                            $_product,
                            false
                        );

                        echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                        ?>
                    </td>

                    <td class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
                        <?php
                        echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                        ?>
                    </td>
                </tr>
                <?php
            }
        }

        ?>

        <?php do_action('woocommerce_cart_contents'); ?>

        <tr>
            <td colspan="6" class="actions">

                <?php if (wc_coupons_enabled()) { ?>
                    <div class="coupon">
                        <label for="coupon_code"
                               class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label> <input
                                type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>"/>
                        <button type="submit"
                                class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
                                name="apply_coupon"
                                value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_attr_e('Apply coupon', 'woocommerce'); ?></button>
                        <?php do_action('woocommerce_cart_coupon'); ?>
                    </div>
                <?php } ?>

                <!--                <button type="submit" class="button-->
                <?php //echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?><!--" name="update_cart" value="-->
                <?php //esc_attr_e( 'Update cart', 'woocommerce' ); ?><!--">-->
                <?php //esc_html_e( 'Update cart', 'woocommerce' ); ?><!--</button>-->

                <?php do_action('woocommerce_cart_actions'); ?>

                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            </td>
        </tr>

        <?php do_action('woocommerce_after_cart_contents'); ?>
        </tbody>
    </table>
    <?php do_action('woocommerce_after_cart_table'); ?>
</form>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    do_action('woocommerce_cart_collaterals');
    ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>
