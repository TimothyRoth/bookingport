<?php
if (!current_user_can('administrator')) {
    wp_redirect('/');
}

current_user_can('administrator') ? $is_admin = true : $is_admin = false;

$args = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'posts_per_page' => 5,
];

$filteredStands = new WP_Query($args); ?>

<div class="map" id="admin-stand-overview-map"></div>

<div class="download-excel-modal">
    <h3>Die Standbuchungsliste wird für Sie vorbereitet<br/>
        Dieser Vorgang kann einige Zeit in Anspruch nehmen <br/>
        Wir bitten Sie daher um etwas Geduld</h3>
</div>

<div class="wrapper">
    <div class="i-am-legend">
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
            <p>Verfügbare Stände</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-red.svg">
            <p>Gebucht / Reserviert</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/maps/Info_blau.svg">
            <p>Info zur Freifläche</p>
        </div>
    </div>
</div>

<div class="wrapper">
    <h1 class="page-headline"><?php the_title(); ?></h1>
    <div class="select-filter">
        <label for="filter-stand-status">Sortieren nach</label>
        <select name="filter-stand-status">
            <option value="all">Alle Stände</option>
            <option value="accessible">Frei</option>
            <option value="booked">Gebucht</option>
            <option value="reserved">Vom Kunden angefragt</option>
            <option value="admin-reserved-requested">Vom Admin reserviert</option>
            <option value="expired">Aus abgelaufenen Angeboten</option>
        </select>
    </div>

    <div class="search-filter">
        <input name="admin-filter-stands" type="text" class="search-stand-filter" placeholder="Suche...">
    </div>

    <div class="booking-overview">
        <p id="accessible">
            Frei: <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) ?>
            Stände</p>
        <p id="booked">
            Gebucht: <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold) ?>
            Stände</p>
        <p id="reserved">Vom Kunden
            angefragt: <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer) ?>
            Stände</p>
        <p id="admin-reserved">
            Vom Admin
            reserviert: <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin) ?>
            Stände</p>
        <p id="expired">Stände aus abgelaufenen
            Angeboten: <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired) ?></p>
    </div>

    <div class="export-bookings btn-secondary">
        Buchungsliste exportieren
        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/Buchungsliste.svg">
    </div>

    <div id="filter-result-container">
        <?php
        if ($filteredStands->found_posts > 0) {
            $option_table = get_option(BOOKINGPORT_Settings::$option_table);
            $invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];
            foreach ($filteredStands->posts ?? [] as $p) {
                $id = $p->ID;
                $has_pavillon = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $current_user_id = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, true);
                $user = get_user_by('ID', $current_user_id);
                $userRole = null;

                if (is_object($user) && isset($user->roles[0])) {
                    $userRole = $user->roles[0];
                }

                $billing_first_name = get_user_meta($current_user_id, 'billing_first_name', true);
                $billing_last_name = get_user_meta($current_user_id, 'billing_last_name', true);
                $user_name = $billing_first_name . ' ' . $billing_last_name;


                $status = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'In Reservierung',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'Gebucht',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'Vom Admin Reserviert',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'Vom Kunden angefragt',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => '
                    Abgelaufenes Angebot',
                    default => null
                };

                $invoice_id = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, true);

                if (!empty($invoice_id)) {
                    $status .= " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id}' /><input type='submit' value='(Zur Rechnung)' /></form> ";
                }

                $stand_class = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'reserved',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'booked',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'admin-reserved-requested',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'customer-requested',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => 'expired',
                    default => null
                };

                ob_start(); ?>
                <div class="single-result">
                    <div
                            class="inner-content <?= $stand_class ?>">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                            <p class="stand-street"><?= $street . ' ' . $housenumber ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-blue.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                        <?php if ($has_pavillon) { ?>
                            <div class="row selected-stand-pavillon">
                                <img class="stand-pavillon-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                                <p class="stand-pavillon">Pavillon möglich</p>
                            </div>
                        <?php } ?>

                        <?php if (!$has_pavillon) { ?>
                            <div class="row selected-stand-pavillon">
                                <img class="stand-pavillon-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                                <p class="stand-pavillon">Kein Pavillon möglich</p>
                            </div>
                        <?php } ?>

                        <?php if ($is_admin && get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true) !== BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) { ?>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                                <p><?= $status ?></p>
                            </div>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                                <p><?php echo $user_name . ' (' . ucfirst($userRole) . ')'; ?></p>
                            </div>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-blue.svg">
                                <a href="mailto<?= $user->user_email ?>"><?= $user->user_email ?></a>
                            </div>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/phone-blue.svg">
                                <a href="tel:<?= $user->billing_phone ?>"><?= $user->billing_phone ?></a>
                            </div>

                            <div class="change-customer">
                                <a class="customer-change-button"
                                   href="<?= get_home_url() ?>/wp-admin/user-edit.php?user_id=<?= $current_user_id ?>"
                                   id="change_customer">
                                    Zur Kundenverwaltung
                                </a>
                            </div>

                            <form class="redirect-to-customer-invoices" method="POST"
                                  action="<?= $invoice_page_link ?>">
                                <input type="hidden" name="invoice_info" value="<?= $current_user_id ?>">
                                <input type="submit" value="Alle Rechnungen des Kunden"/>
                            </form>

                            <div class="btn-tertiary trigger-reset-stand-modal" id="<?= $id ?>">Stand
                                zurücksetzen
                            </div>

                            <div class="reset-stand-modal modal">
                                <div class="inner-modal">
                                    <h3>Sind Sie sicher, dass Sie den Stand <?= $street . ' ' . $housenumber ?>
                                        mit der Standnummer
                                        <?= $number ?> zurücksetzen möchten?</h3>
                                    <div class="button-row">
                                        <div class="btn-primary btn reset-stand" id="<?= $id ?>">Stand zurücksetzen</div>
                                        <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                echo ob_get_clean();
            }
            if ($filteredStands->found_posts > 5) { ?>
                <div class="btn-secondary show-more-stands">Mehr anzeigen</div>
            <?php }
        } ?>
    </div>
</div>
