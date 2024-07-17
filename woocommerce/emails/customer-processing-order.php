<?php
/**
 * Customer processing order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email);
$home_url = get_home_url();
$option_table = get_option(BOOKINGPORT_Settings::$option_table);
?>

<?php /* translators: %s: Customer first name */ ?>
    <p><?php printf(esc_html__('Sehr geehrte/r %1$s %2$s,', 'woocommerce'), esc_html($order->get_billing_first_name()), esc_html($order->get_billing_last_name())); ?></p>
<?php /* translators: %s: Order number */ ?>
    <p><?php printf(esc_html__('vielen Dank für Ihre Standbuchung! Wir haben Ihre Buchung #%s erhalten und werden Sie umgehend bearbeiten.', 'woocommerce'), esc_html($order->get_order_number())); ?></p>
    <p><?php printf(esc_html__('Ihre Rechnung finden Sie im Anhang dieser E-Mail.', 'woocommerce')); ?></p>
    <p><?php printf('<a href="' . $home_url . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] . '">Hier</a> gelangen Sie zu all Ihren Rechnungen.', 'woocommerce'); ?></p>
<?php


/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo '<p>' . wp_kses_post(wpautop(wptexturize($additional_content))) . '</p>';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
