<?php
$user_id = get_current_user_id();

$args = [
    'status' => 'any',
    'type' => 'shop_order',
    'limit' => 5,
    'customer_id' => $user_id,
    'return' => 'ids',
];

$total_order_args = [
    'status' => 'any',
    'type' => 'shop_order',
    'customer_id' => $user_id,
    'return' => 'ids',
];

$total_orders = wc_get_orders($total_order_args);
$order_ids = wc_get_orders($args);
$total_order_count = count($total_orders);
$displayed_order_count = count($order_ids);

?>

<main>
    <div class="wrapper">
        <h1 class="page-headline"><?php the_title(); ?></h1>

        <div class="dropdown-filter select-filter">
            <label for="order-dropdown-filter">Sortieren nach</label>
            <select name="order-dropdown-filter">
                <option value="aufsteigend">Datum (Absteigend)</option>
                <option value="absteigend">Datum (Aufsteigend)</option>
            </select>
        </div>
        <div class="search-filter" id="order-search-filter">
            <input placeholder="Suche..." name="order-search" type="text">
        </div>

        <div class="order-results admin-order-results">
            <?php foreach ($order_ids ?? [] as $order_id) {
                $order = wc_get_order($order_id);
                $order_item_meta = $order->get_items();
                $order_user_id = $order->get_user_id();
                $user = get_user_by('id', $order_user_id);
                $order_user_role = null;
                if(is_object($user) && isset($user->roles[0])) {
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
                    </div>
                </div>

                <?php
            }

            if ($displayed_order_count < $total_order_count) {
                echo '<div class="btn-primary show-more-orders">Mehr anzeigen</div>';
            }
            ?>
        </div>


        <div class="i-am-legend">
            <div class="row green">
                <div class="circle"></div>
                Buchung bezahlt
            </div>
            <div class="row red">
                <div class="circle"></div>
                Zahlung ausstehend
            </div>
        </div>
    </div>
</main>