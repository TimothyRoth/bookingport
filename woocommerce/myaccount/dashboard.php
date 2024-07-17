<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
$current_user_id = get_current_user_id();
$email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : wp_get_current_user()->user_email;
$bookingport_settings = new BOOKINGPORT_Settings();
$option_table = get_option($bookingport_settings::$option_table);
/*** Save fields ***/

// E-Mail
// Check if the email address is submitted and not empty
if (!empty($_POST['email']) && isset($_POST['email'])) {
    // Check if the email address already exists for another user
    $existing_user_id = email_exists($email);

    if ($existing_user_id && $existing_user_id !== $current_user_id) {
        // Email address already exists for another user
        wc_add_notice(__('Die von Ihnen gewünschte E-Mail-Adresse ist bereits vergeben.', 'woocommerce'), 'error');
        $email = wp_get_current_user()->user_email;
    } else {
        // Update the email address
        $user_data = [
            'ID' => $current_user_id,
            'user_email' => $email,
        ];
        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            // An error occurred while updating the email address
            wc_add_notice(__('Es ist ein Fehler beim Aktualisieren der E-Mail-Adresse aufgetreten.', 'woocommerce'), 'error');
        } else {
            // Email address updated successfully
            wc_add_notice(__('Die von Ihnen gewünschte E-Mail-Adresse wurde gespeichert.', 'woocommerce'), 'success');
        }
    }
}

// Password fields
if (!empty($_POST['new_password']) && isset($_POST['new_password']) && !empty($_POST['confirm_password']) && isset($_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_password = $_POST['current_password'];

// Verify the current password
    $current_user = wp_get_current_user();
    $is_password_correct = wp_check_password($current_password, $current_user->user_pass, $current_user->ID);

    if (!$is_password_correct) {
        wc_add_notice(__('Das eingegebene aktuelle Passwort ist nicht korrekt.', 'woocommerce'), 'error');
    } elseif ($new_password !== $confirm_password) {
        wc_add_notice(__('Die eingegebenen Passwörter stimmen nicht überein.', 'woocommerce'), 'error');
    } else {
        // Password update logic
        wp_set_password($new_password, $current_user_id);
        wc_add_notice(__('Ihr Passwort wurde erfolgreich geändert.', 'woocommerce'), 'success');

        // Log in the user
        wp_set_current_user($current_user_id);
        wp_set_auth_cookie($current_user_id, true);
        do_action('wp_login', $current_user->user_login, $current_user);
    }
}

// Display notices immediately
wc_print_notices();

?>

<form id="login-form" method="post" action="/mein-konto">
    <h3>Zugangsdaten</h3>
    <p class="user-mail-address">
        <label>E-Mail-Adresse</label>
        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text"
               placeholder="<?= $email; ?>"
               name="email">
    </p>
    <p class="user-new-password">
        <label>Neues Passwort (wenn gewünscht)</label>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text"
               name="new_password">
    </p>
    <div class="password-strength shrink">
        <div id="password-strength-meter"></div>
        <div class="password-strength-label"></div>
    </div>
    <p class="user-confirm-password">
        <label>Passwort wiederholen</label>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text"
               name="confirm_password">
    </p>

    <div class="password-requirements-container">
        <div class="icon">Bedingungen für Ihre Passwortvergabe</div>
        <div class="show_current_pre_cart_selectionquirements">
            <p>Um die Sicherheit Ihres Kontos zu gewährleisten, sollte Ihr Passwort folgende
                Kriterien erfüllen:</p>
            <ul>
                <li>Mindestens 8 Zeichen Länge</li>
                <li>Verwenden Sie eine Kombination aus Groß- und Kleinbuchstaben</li>
                <li>Integrieren Sie Zahlen und Sonderzeichen</li>
                <li>Vermeiden Sie aufeinanderfolgende oder sich wiederholende Zeichen</li>
            </ul>
            <p>Wenn Ihr Passwort diese Anforderungen erfüllt, wird der Balken grün, und Ihr Passwort wird erfolgreich geändert.</p>
        </div>
    </div>

    <p class="user-current-password">
        <label>Aktuelles Passwort  (damit Passwortänderungen gespeichert werden können)</label>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text"
               name="current_password">
    </p>
    <div class="button-row">
        <a href="/<?= $option_table[$bookingport_settings::$option_redirects_dashboard]; ?>" class="btn-secondary">Abbrechen</a>
        <input id="confirm-change-password" type="submit" class="btn-primary" value="Daten speichern">
    </div>
</form>

<?php

/* Variables for shipping form placeholder */

$shipping_company = isset($_POST['shipping_company']) && !empty($_POST['shipping_company']) ? $_POST['shipping_company'] : get_user_meta($current_user_id, 'shipping_company', true);
$shipping_first_name = isset($_POST['shipping_first_name']) && !empty($_POST['shipping_first_name']) ? $_POST['shipping_first_name'] : get_user_meta($current_user_id, 'shipping_first_name', true);
$shipping_last_name = isset($_POST['shipping_last_name']) && !empty($_POST['shipping_last_name']) ? $_POST['shipping_last_name'] : get_user_meta($current_user_id, 'shipping_last_name', true);
$shipping_address = isset($_POST['shipping_address_1']) && !empty($_POST['shipping_address_1']) ? $_POST['shipping_address_1'] : get_user_meta($current_user_id, 'shipping_address_1', true);
$shipping_postcode = isset($_POST['shipping_postcode']) && !empty($_POST['shipping_postcode']) ? $_POST['shipping_postcode'] : get_user_meta($current_user_id, 'shipping_postcode', true);
$shipping_city = isset($_POST['shipping_city']) && !empty($_POST['shipping_city']) ? $_POST['shipping_city'] : get_user_meta($current_user_id, 'shipping_city', true);
$shipping_phone = isset($_POST['shipping_phone']) && !empty($_POST['shipping_phone']) ? $_POST['shipping_phone'] : get_user_meta($current_user_id, 'shipping_phone', true);

/* Save Shipping Account Data */
if (isset($shipping_company) && !empty($shipping_company)) {
    update_user_meta($current_user_id, 'shipping_company', $shipping_company);
}

if (isset($shipping_first_name) && !empty($shipping_first_name)) {
    update_user_meta($current_user_id, 'shipping_first_name', $shipping_first_name);
    update_user_meta($current_user_id, 'account_first_name', $shipping_first_name);
    update_user_meta($current_user_id, 'first_name', $shipping_first_name);
}

if (isset($shipping_last_name) && !empty($shipping_last_name)) {
    update_user_meta($current_user_id, 'shipping_last_name', $shipping_last_name);
    update_user_meta($current_user_id, 'account_last_name', $shipping_last_name);
    update_user_meta($current_user_id, 'last_name', $shipping_last_name);
}

if (isset($shipping_address) && !empty($shipping_address)) {
    update_user_meta($current_user_id, 'shipping_address_1', $shipping_address);
}

if (isset($shipping_postcode) && !empty($shipping_postcode)) {
    update_user_meta($current_user_id, 'shipping_postcode', $shipping_postcode);
}

if (isset($shipping_city) && !empty($shipping_city)) {
    update_user_meta($current_user_id, 'shipping_city', $shipping_city);
}

if (isset($shipping_phone) && !empty($shipping_phone)) {
    update_user_meta($current_user_id, 'shipping_phone', $shipping_phone);
}

?>

<form id="shipping-data-form" method="post" action="/mein-konto">
    <h3>Persönliche Daten</h3>
    <p class="user-new-password">
        <label>Firma</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="shipping_company" placeholder="<?= $shipping_company; ?>">
    </p>
    <p class="user-first_name">
        <label>Vorname</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="shipping_first_name" placeholder="<?= $shipping_first_name; ?>">
    </p>
    <p class="user-last_name">
        <label>Nachname</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="shipping_last_name" placeholder="<?= $shipping_last_name; ?>">
    </p>
    <p class="user-address">
        <label>Straße + Hausnummer</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="shipping_address_1" placeholder="<?= $shipping_address; ?>">
    </p>
    <div class="row">
        <p class="user-postal-code">
            <label>PLZ</label>
            <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="shipping_postcode" placeholder="<?= $shipping_postcode; ?>">
        </p>
        <p class="user-city">
            <label>Ort</label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="shipping_city" placeholder="<?= $shipping_city; ?>">
        </p>
    </div>
    <p class="user-city">
        <label>Telefon</label>
        <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
               name="shipping_phone" placeholder="<?= $shipping_phone; ?>">
    </p>
    <div class="button-row">
        <a href="/<?= $option_table[$bookingport_settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
        <input type="submit" class="btn-primary" value="Daten speichern">
    </div>
</form>

<?php

/* Variables for billing form placeholder and save data */
$billing_company = isset($_POST['billing_company']) && !empty($_POST['billing_company']) ? $_POST['billing_company'] : get_user_meta($current_user_id, 'billing_company', true);
$billing_first_name = isset($_POST['billing_first_name']) && !empty($_POST['billing_first_name']) ? $_POST['billing_first_name'] : get_user_meta($current_user_id, 'billing_first_name', true);
$billing_last_name = isset($_POST['billing_last_name']) && !empty($_POST['billing_last_name']) ? $_POST['billing_last_name'] : get_user_meta($current_user_id, 'billing_last_name', true);
$billing_address = isset($_POST['billing_address_1']) && !empty($_POST['billing_address_1']) ? $_POST['billing_address_1'] : get_user_meta($current_user_id, 'billing_address_1', true);
$billing_postcode = isset($_POST['billing_postcode']) && !empty($_POST['billing_postcode']) ? $_POST['billing_postcode'] : get_user_meta($current_user_id, 'billing_postcode', true);
$billing_city = isset($_POST['billing_city']) && !empty($_POST['billing_city']) ? $_POST['billing_city'] : get_user_meta($current_user_id, 'billing_city', true);
$billing_phone = isset($_POST['billing_phone']) && !empty($_POST['billing_phone']) ? $_POST['billing_phone'] : get_user_meta($current_user_id, 'billing_phone', true);

/* Save Billing Account Data */

if (isset($billing_company) && !empty($billing_company)) {
    update_user_meta($current_user_id, 'billing_company', $billing_company);
}

if (isset($billing_first_name) && !empty($billing_first_name)) {
    update_user_meta($current_user_id, 'billing_first_name', $billing_first_name);
}

if (isset($billing_last_name) && !empty($billing_last_name)) {
    update_user_meta($current_user_id, 'billing_last_name', $billing_last_name);
}

if (isset($billing_address) && !empty($billing_address)) {
    update_user_meta($current_user_id, 'billing_address_1', $billing_address);
}

if (isset($billing_postcode) && !empty($billing_postcode)) {
    update_user_meta($current_user_id, 'billing_postcode', $billing_postcode);
}

if (isset($billing_city) && !empty($billing_city)) {
    update_user_meta($current_user_id, 'billing_city', $billing_city);
}

if (isset($billing_phone) && !empty($billing_phone)) {
    update_user_meta($current_user_id, 'billing_phone', $billing_phone);
}

?>

<form id="billing-data-form" method="post" action="/mein-konto">
    <h3>Rechnungsdaten</h3>
    <p class="user-new-password">
        <label>Firma</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="billing_company" placeholder="<?= $billing_company; ?>">
    </p>
    <p class="user-first_name">
        <label>Vorname</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="billing_first_name" placeholder="<?= $billing_first_name; ?>">
    </p>
    <p class="user-last_name">
        <label>Nachname</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="billing_last_name" placeholder="<?= $billing_last_name; ?>">
    </p>
    <p class="user-address">
        <label>Straße + Hausnummer</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="billing_address_1" placeholder="<?= $billing_address; ?>">
    </p>
    <div class="row">
        <p class="user-postal-code">
            <label>PLZ</label>
            <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="billing_postcode" placeholder="<?= $billing_postcode; ?>">
        </p>
        <p class="user-city">
            <label>Ort</label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="billing_city" placeholder="<?= $billing_city; ?>">
        </p>
    </div>
    <p class="user-city">
        <label>Telefon</label>
        <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
               name="billing_phone" placeholder="<?= $billing_phone; ?>">
    </p>
    <div class="button-row">
        <a href="/<?= $option_table[$bookingport_settings::$option_redirects_dashboard]; ?>" class="btn-secondary">Abbrechen</a>
        <input type="submit" class="btn-primary" value="Daten speichern">
    </div>
</form>