<?php

class BOOKINGPORT_CustomerDataHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_add_order_note', [__CLASS__, 'add_order_note']);
        add_action('wp_ajax_nopriv_add_order_note', [__CLASS__, 'add_order_note']);

        add_action('wp_ajax_filter_customers', [__CLASS__, 'filter_customers']);
        add_action('wp_ajax_nopriv_filter_customers', [__CLASS__, 'filter_customers']);

        add_action('wp_ajax_filter_invoices', [__CLASS__, 'filter_invoices']);
        add_action('wp_ajax_nopriv_filter_invoices', [__CLASS__, 'filter_invoices']);

        add_action('wp_ajax_print_all_invoices', [__CLASS__, 'print_all_invoices']);
        add_action('wp_ajax_nopriv_print_all_invoices', [__CLASS__, 'print_all_invoices']);

        add_action('wp_ajax_print_user_invoice', [__CLASS__, 'print_user_invoice']);
        add_action('wp_ajax_nopriv_print_user_invoice', [__CLASS__, 'print_user_invoice']);
    }

    /**
     * @throws JsonException
     */
    public static function add_order_note(): void
    {
        $order_meta = $_POST['orderMeta'];
        $order_id = $order_meta['orderID'];
        $order_note_text = $order_meta['noteText'];

        $order = wc_get_order($order_id);
        $order->add_order_note($order_note_text);

        wp_die(json_encode($order_meta, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function filter_customers(): void
    {
        $dropdown_filter_value = $_POST['dropdown'];
        $search_filter_value = $_POST['search'];
        $users_to_display = $_POST['amount'];
        $response['html'] = 'Leider wurde kein Kunde gefunden, der Ihren Suchparametern entspricht';
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];


        $args = [
            'role' => $dropdown_filter_value,
            'role__not_in' => 'administrator',
        ];

        // If the search filter value contains "@" symbol, search by email
        if (str_contains($search_filter_value, '@')) {
            $args['search'] = $search_filter_value;
        } else {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => 'first_name',
                    'value' => $search_filter_value,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'last_name',
                    'value' => $search_filter_value,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'account_first_name',
                    'value' => $search_filter_value,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'account_last_name',
                    'value' => $search_filter_value,
                    'compare' => 'LIKE',
                ],
            ];

            // Extract first name and last name from search query
            $search_terms = explode(' ', $search_filter_value);
            $first_name = $search_terms[0] ?? '';
            $last_name = $search_terms[1] ?? '';

            if ($first_name && $last_name) {
                $args['meta_query'][] = [
                    'relation' => 'AND',
                    [
                        'key' => 'billing_first_name',
                        'value' => $first_name,
                        'compare' => 'LIKE',
                    ],
                    [
                        'key' => 'billing_last_name',
                        'value' => $last_name,
                        'compare' => 'LIKE',
                    ],
                ];
            }
        }

        $users = get_users($args);
        $displayed_users = 1;

        if (count($users) > 0) {
            $response = [];

            foreach ($users ?? [] as $user) {

                if ($displayed_users > $users_to_display) {
                    break;
                }

                $registration_date = $user->user_registered;
                $first_name = get_user_meta($user->ID, 'billing_first_name', true);
                $last_name = get_user_meta($user->ID, 'billing_last_name', true);
                $phone = get_user_meta($user->ID, 'shipping_phone', true);
                $token = get_user_meta($user->ID, 'verification_token', true);
                $active_status = ($user->user_status == 0) ? 'Inaktiv' : 'Aktiv';
                $account_verified = false;
                $user_role = null;

                if (is_object($user) && isset($user->roles[0])) {
                    $user_role = $user->roles[0];
                }

                if (empty($token)) {
                    $account_verified = true;
                }

                $email = $user->user_email;

                ob_start(); ?>

                <div class="user-row <?php if ($account_verified) {
                    echo 'verified';
                } ?>">
                    <div class="open-additional-customer-information">
                        <div class="stripe vertical"></div>
                        <div class="stripe horizonal"></div>
                    </div>
                    <div class="registration-date">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/date-blue.svg">
                        <p><?php echo date('Y-m-d', strtotime($registration_date)); ?><?php if (!$account_verified) {
                                echo ' (Bestätigung ausstehend)';
                            } ?></p>
                    </div>
                    <div class="name">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                        <p><?= $first_name . ' ' . $last_name ?></p>
                    </div>
                    <div class="role">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                        <p><?= $user_role ?></p>
                    </div>
                    <div class="hidden-user-information">
                        <div class="active-status <?= $active_status ?>">
                            <?php if ($active_status === 'Aktiv') { ?>
                                <img
                                        src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/Aktiv_Grün.svg">
                            <?php } else { ?>
                                <img
                                        src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/Aktiv_Rot.svg">
                            <?php } ?>
                            <p>Aktiv: <?php echo $active_status === 'Aktiv' ? 'Ja' : 'Nein'; ?></p>
                        </div>
                        <div class="phone">
                            <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/phone-blue.svg">
                            <a href="tel:<?= $phone ?>"><?= $phone ?></a>
                        </div>
                        <div class="email">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-blue.svg">
                            <a href="mailto:<?= $email ?>"><?= $email ?></a>
                        </div>
                        <form class="redirect-to-customer-invoices" method="POST"
                              action="<?= $invoice_page_link ?>">
                            <input type="hidden" name="invoice_info" value="<?= $user->ID ?>">
                            <input type="submit" value="Alle Rechnungen des Kunden"/>
                        </form>
                        <div class="change-customer">
                            <label for="change_customer">Kundendaten einsehen:</label>
                            <a class="btn-primary"
                               href="<?php echo get_home_url() . '/wp-admin/user-edit.php?user_id=' . $user->ID; ?>"
                               id="change_customer">
                                Verwaltung
                            </a>
                        </div>
                    </div>
                </div>

                <?php $response['html'][] = ob_get_clean();

                $response['user_stand_booking_meta'][] = [
                    'user_id' => $user->ID,
                    'user_email' => $email,
                    'user_name' => $first_name . ' ' . $last_name
                ];

                $displayed_users++;
            }
            $response['html'][] = '<p class="post-count">Zeige ' . $displayed_users < count($users) ? $displayed_users - count($users) : 1 . ' bis ' . $displayed_users - 1 . ' von ' . count($users) . ' Daten</p>';

            if (count($users) > $users_to_display) {
                $response['html'][] = '<div class="show-more-users btn-secondary">Mehr anzeigen</div>';
            }

        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function filter_invoices(): void
    {

        $search_query = $_POST['orderMeta']['search'];
        $dropdown = $_POST['orderMeta']['dropdown'];
        $amount = $_POST['orderMeta']['amount'];
        $html = [];

        $args = [
            'status' => 'any',
            'type' => 'shop_order',
            'limit' => -1,
            'return' => 'ids',
        ];

        if (!isset($search_query) || $search_query === '') {
            $args['limit'] = $amount;
        }

        if (!current_user_can('administrator')) {
            $user_id = get_current_user_id();
            $args['customer_id'] = $user_id;
        }

        if ($dropdown === 'aufsteigend') {
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
        }

        if ($dropdown === 'absteigend') {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        $matched_orders = [];

        $all_orders = wc_get_orders($args);

        // Perform search on each order
        foreach ($all_orders ?? [] as $single_order) {
            $order = wc_get_order($single_order);
            $order_customer_id = $order->get_user_id();
            $billing_first_name = $order->get_billing_first_name();
            $order_price = $order->get_total();
            $billing_last_name = $order->get_billing_last_name();
            $billing_company = $order->get_billing_company();
            $billing_address_1 = $order->get_billing_address_1();
            $billing_address_2 = $order->get_billing_address_2();
            $billing_city = $order->get_billing_city();
            $billing_state = $order->get_billing_state();
            $billing_postcode = $order->get_billing_postcode();
            $billing_country = $order->get_billing_country();
            $billing_email = $order->get_billing_email();
            $billing_phone = $order->get_billing_phone();
            $shipping_first_name = $order->get_shipping_first_name();
            $shipping_last_name = $order->get_shipping_last_name();
            $shipping_company = $order->get_shipping_company();
            $shipping_address_1 = $order->get_shipping_address_1();
            $shipping_address_2 = $order->get_shipping_address_2();
            $shipping_city = $order->get_shipping_city();
            $shipping_state = $order->get_shipping_state();
            $shipping_postcode = $order->get_shipping_postcode();
            $shipping_country = $order->get_shipping_country();
            $customer_ip_address = $order->get_customer_ip_address();
            $order_invoice_number = $order->get_order_number();
            $full_name = $billing_first_name . ' ' . $billing_last_name;

            // Perform partial match search on relevant fields
            if (
                stripos($order_customer_id, $search_query) !== false ||
                stripos($billing_first_name, $search_query) !== false ||
                stripos($full_name, $search_query) !== false ||
                stripos($order_invoice_number, $search_query) !== false ||
                stripos($order_price, $search_query) !== false ||
                stripos($billing_last_name, $search_query) !== false ||
                stripos($billing_company, $search_query) !== false ||
                stripos($billing_address_1, $search_query) !== false ||
                stripos($billing_address_2, $search_query) !== false ||
                stripos($billing_city, $search_query) !== false ||
                stripos($billing_state, $search_query) !== false ||
                stripos($billing_postcode, $search_query) !== false ||
                stripos($billing_country, $search_query) !== false ||
                stripos($billing_email, $search_query) !== false ||
                stripos($billing_phone, $search_query) !== false ||
                stripos($shipping_first_name, $search_query) !== false ||
                stripos($shipping_last_name, $search_query) !== false ||
                stripos($shipping_company, $search_query) !== false ||
                stripos($shipping_address_1, $search_query) !== false ||
                stripos($shipping_address_2, $search_query) !== false ||
                stripos($shipping_city, $search_query) !== false ||
                stripos($shipping_state, $search_query) !== false ||
                stripos($shipping_postcode, $search_query) !== false ||
                stripos($shipping_country, $search_query) !== false ||
                stripos($customer_ip_address, $search_query) !== false
            ) {
                $matched_orders[] = $order->get_id();
            }
        }


        $displayed_orders = 0;

        foreach ($matched_orders ?? [] as $order_id) {

            $order = wc_get_order($order_id);
            $order_item_meta = $order->get_items();
            $order_user_id = $order->get_user_id();
            $user = get_user_by('id', $order_user_id);

            $order_user_role = null;

            if (is_object($user) && isset($user->roles[0])) {
                $order_user_role = $user->roles[0];
            }

            $order_price = $order->get_total();
            $order_date = $order->get_date_created()->date('Y-m-d');
            $order_user = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $order_invoice_number = $order->get_order_number();
            $order_payment_status = $order->get_status() === 'completed' ? 'Ja' : 'Nein';
            $order_user_phone = $order->get_billing_phone();
            $order_user_email = $order->get_billing_email();
            $order_meta = null;

            foreach ($order_item_meta ?? [] as $meta) {
                if (isset($meta['Kombipaket'])) {
                    $order_meta = $meta['Kombipaket'];
                    break;
                }

                $order_meta .= $meta['Straße'] . ' ' . $meta['Hausnummer'] . ', ' . $meta['Standnummer'] . '<br/>';
            }

            $color_theme = $order_payment_status === 'Ja' ? 'green' : 'red';
            ob_start();
            ?>

            <div class="single-order <?= $color_theme ?>">
                <div class="open-additional-order-information">
                    <div class="stripe vertical"></div>
                    <div class="stripe horizonal"></div>
                </div>
                <div class="row order-date">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/date-' . $color_theme . '.svg' ?>">
                    <p><?= $order_date ?></p>
                </div>
                <div class="row order-user-name">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/customer-' . $color_theme . '.svg' ?>">
                    <p><?= $order_user ?> <br/>Benutzerrolle: <?= ucfirst($order_user_role) ?></p>
                </div>
                <div class="row order-user-stands">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stand-cat-' . $color_theme . '.svg' ?>">
                    <p><?= $order_meta;
                        $order_meta = null; ?></p>
                </div>
                <div class="row order-stand-id">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stand-number-' . $color_theme . '.svg' ?>">
                    <p>Rechnungsbetrag: <?= $order_price ?>€</p>
                </div>
                <div class="row order-invoice-number">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/order-' . $color_theme . '.svg' ?>">
                    <p>Rechnungsnummer: <?= $order_invoice_number ?></p>
                </div>
                <div class="row invoice-pdf">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/download-pdf-' . $color_theme . '.svg' ?>">
                    <a class="generate-user-invoice" id="invoice-<?= $order_id ?>">Download als PDF</a>
                </div>
                <div class="hidden-order-information">
                    <div class="payment-status">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/check-' . $color_theme . '.svg' ?>">
                        <p>Bezahlt: <?= $order_payment_status === 'Ja' ? 'bezahlt' : 'ausstehend' ?></p>
                    </div>
                    <div class="phone">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/phone-' . $color_theme . '.svg' ?>">
                        <a href="tel:<?= $order_user_phone ?>"><?= $order_user_phone ?></a>
                    </div>
                    <div class="email">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/mail-' . $color_theme . '.svg' ?>">
                        <a href="mailto:<?= $order_user_email ?>"><?= $order_user_email ?></a>
                    </div>
                    <?php if (current_user_can('administrator')) { ?>
                        <div class="change-order">
                            <label for="change_order">Buchungsdaten einsehen:</label>
                            <a href="<?= get_home_url() . '/wp-admin/post.php?post=' . $order_invoice_number . '&action=edit' ?>"
                               class="btn-primary" id="change_order">
                                Verwaltung
                            </a>
                        </div>
                        <div class="order-remarks">
                            <label for="add_order_note">Status/Notizen</label>
                            <textarea data-src="<?= $order_id ?>" placeholder="Notizen hier eingeben"
                                      name="add_order_note"
                                      id="add_order_note"></textarea>
                            <div class="btn-primary admin_add_order_note_button">Anmerkung hinzufügen</div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php
            $displayed_orders++;
            $html[] = ob_get_clean();

        }

        if ($amount <= $displayed_orders) {
            $html[] = '<div class="btn-primary show-more-orders">Mehr anzeigen</div>';
        }


        wp_die(json_encode($html, JSON_THROW_ON_ERROR));
    }

    public
    static function print_all_invoices(): void
    {
        $args = [
            'status' => 'any',
            'type' => 'shop_order',
            'return' => 'ids',
            'limit' => -1
        ];

        $order_ids = wc_get_orders($args);
        $invoices = [];

        foreach ($order_ids ?? [] as $order_id) {
            $invoices[] = $order_id;
        }


        wp_send_json($invoices);
    }

    public
    static function print_user_invoice(): void
    {
        $order_ID = $_POST['orderID'];
        $order = wc_get_order($order_ID);

        $invoice = wcpdf_get_document('invoice', $order, true);
        $pdf_data = $invoice->get_pdf();

        wp_send_json(['pdf_data' => base64_encode($pdf_data)]);
    }

}