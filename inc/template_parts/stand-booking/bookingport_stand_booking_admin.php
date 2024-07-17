<div id="error-messages"></div>

<?php
$user_ID = get_current_user_id();
$session_items = isset($_SESSION['items']) ? $_SESSION['items'] : [];
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

$args = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'post__in' => $session_items,
];

$selectedItems = new WP_Query($args);

$customer_requests_args = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => BOOKINGPORT_CptMarket::$Cpt_MarketType,
            'value' => BOOKINGPORT_CptMarket::$Cpt_MarketRequest,
            'compare' => '=',
        ]
    ]
];

$customer_requests = new WP_Query($customer_requests_args);
?>

<?php if (isset($_SESSION['proceed_customer_data']) && !empty($_SESSION['proceed_customer_data'])) { ?>
    <div class="proceeded-customer-message">
        <p>Sie haben die Anfrage <?= $_SESSION['proceed_customer_data']['request'] ?> des Kunden (Kundennummer: <?= $_SESSION['proceed_customer_data']['customer_id'] ?>,
            Kundenname: <?= $_SESSION['proceed_customer_data']['customer_name']; ?>), erfolgreich bearbeitet</p>
    </div>
    <?php
    $_SESSION['proceed_customer_data'] = [];
} ?>

<div class="request-container">
    <?php if ($customer_requests->found_posts > 0) { ?>
    <h3 id="current-request-counter">Aktuelle Anfragen: <?= count($customer_requests->posts) ?></h3>
    <div class="inner-content">
        <?php foreach ($customer_requests->posts ?? [] as $single_request) {

            $id = $single_request->ID;
            $request_association_name = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationName, true);
            $request_association_sortiment = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true);
            $user_id = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketUserID, true);
            $request_width = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
            $request_depth = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
            $request_pavillon = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketPavillon, true);
            $request_comment = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketComment, true);
            $requested_stand_id = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);
            $user = get_user_by('ID', $user_id);

            $billing_first_name = get_user_meta($user_id, 'billing_first_name', true);
            $billing_last_name = get_user_meta($user_id, 'billing_last_name', true);
            $full_name = $billing_first_name . ' ' . $billing_last_name; ?>
            <div class="single-request">
                <div class="request-id-container">
                    <h3><?= get_the_title($id); ?></h3>
                </div>
                <div>
                    <img class="stand-marker-image"
                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                    <p>Von: <?= $full_name ?></p>
                    <input type="hidden" name="request_user_id" value="<?= $user_id; ?>"/>
                    <input type="hidden" name="request_user_name" value="<?= $full_name; ?>"/>
                </div>
                <div>
                    <img class="stand-marker-image"
                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-blue.svg">
                    <a href="mailto:<?= $user->user_email; ?>">E-Mail: <?= $user->user_email; ?></a>
                </div>

                <?php if (isset($request_association_name) and !empty($request_association_name)) { ?>
                    <div>
                        <img class="stand-marker-image"
                             src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                        <p>Vereinsname: <?= $request_association_name; ?></p>
                    </div>

                <?php } ?>
                <?php if (isset($request_association_sortiment) && !empty($request_association_sortiment)) { ?>
                    <div>
                        <img class="stand-marker-image"
                             src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                        <p>Sortiment: <?= $request_association_sortiment; ?></p>
                    </div>
                <?php } ?>
                <div>
                    <img class="stand-marker-image"
                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                    <p>
                        Wunschstraße: <?= get_the_title($requested_stand_id); ?></p>
                </div>
                <div>
                    <img class="stand-marker-image"
                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-blue.svg">
                    <p>Angeforderte Breite: <?= $request_width; ?>m</p>
                </div>

                <?php if (!empty($request_depth)) { ?>
                    <div>
                        <img class="stand-marker-image"
                             src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-blue.svg">
                        <p>Angeforderte Tiefe: <?= $request_depth; ?>m</p>
                    </div>

                <?php } ?>
                <div>
                    <img class="stand-marker-image"
                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                    <p><?php echo 'Pavillon gewünscht: ' . ($request_pavillon === "true" ? 'Ja' : 'Nein'); ?></p>
                </div>
                <?php if (!empty($request_comment)) { ?>
                    <label for="user_remarks"><strong>Anmerkungen des Kunden:</strong></label>
                    <textarea name="user_remarks" readonly><?= $request_comment; ?></textarea>
                <?php } ?>
                <div class="request-button-wrapper">
                    <div class="deny-request btn-primary" data-request-id="<?= $id ?>"
                         data-stand-id="<?= $requested_stand_id; ?>">Anfrage ablehnen
                    </div>
                    <div class="edit-request btn-primary" data-request-id="<?= $id ?>"
                         data-stand-id="<?= $requested_stand_id; ?>">Anfrage bearbeiten
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php } else { ?>
    <h3>Sie haben derzeit keine offenen Anfragen</h3>
<?php } ?>


<div class="no-selected-stands <?php if (!empty($session_items)) echo 'hide'; ?>">
    <p id="choose-stands-disclaimer">Einen oder mehrere Standplätze wählen</p>
    <div class="btn-primary trigger-privat-stand-booking-modal">Standplatz auf Karte wählen</div>
    <a class="faq-link" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_faq]; ?>">
        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/faq.svg">
        Sie haben Fragen zum Buchungsvorgang?
    </a>

</div>

<!-- IF Condition für wenn Standplätze ausgewählt wurden -->
<div class="selected-stands <?php if (empty($session_items)) echo 'hide' ?>">
    <div id="selected-stands-container">
        <?php
        if ($selectedItems->found_posts > 0 and !empty($session_items)) {
            $counter = 1;
            $BOOKINGPORT_CptStands = new BOOKINGPORT_CptStands();
            foreach ($selectedItems->posts ?? [] as $p) {
                $id = $p->ID;
                $has_pavillon = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                if (current_user_can('administrator')) $has_pavillon = false;
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                if (!current_user_can('administrator')) {
                    $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
                    $price = wc_get_product($product_id)->get_price();
                } ?>

                <div class="single-result">
                    <div class="upper-row">
                        <p>Stand <?= $counter; ?></p>
                        <div class="delete-item" data-src="<?= $id; ?>">Stand löschen <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/delete.svg">
                        </div>
                    </div>
                    <div class="inner-content">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-white.svg">
                            <p class="stand-street"><?= $street . ' ' . $housenumber; ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number; ?> </p>
                        </div>
                        <?php if (isset($price)) { ?>
                            <div class="row selected-stand-price">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                                <p class="stand-price"><?= $price; ?> €</p>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if ($has_pavillon) { ?>
                        <div class="checkbox-container">
                            <input id="user_pavillon<?= $counter; ?>" type="checkbox" name="user_pavillon"
                                   data-src="<?= $id; ?>" <?php if (isset($_SESSION['ordersWithPavillon'][$id]) && $_SESSION['ordersWithPavillon'][$id] === 'true') {
                                echo 'checked';
                            } ?>>
                            <label for="user_pavillon_<?= $counter; ?>">Ich möchte am Stand einen Pavillon
                                aufbauen </label>
                        </div>
                    <?php } ?>
                </div>
                <?php
                $counter++;
            };
        }
        ?>
    </div>
    <div class="btn-secondary trigger-privat-stand-booking-modal">Standauswahl ändern?</div>

    <div id="edit-request" class="select-request-container container">
        <div class="select-request-container-content"></div>
        <input type="hidden" name="request_id" value=""/>
    </div>

    <div class="select-customer-container container">
        <h3>Kunden wählen (Bitte direkt aus dem Dropdown wählen)*</h3>
        <input type="text" name="select_customer" id="select-customer" placeholder="Wählen Sie den Kunden" required>
        <div class="customer-results">
            <ul></ul>
        </div>
    </div>

    <div class="select-stand-size-container container">
        <h3>Maße zuweisen</h3>
        <input type="number" name="select_width" id="select-width" min="1" placeholder="Standbreite in m*" required>
        <input type="number" name="select_depth" id="select_depth" min="1" placeholder="Standtiefe in m">
    </div>

    <div class="select-stand-pavillon-confirmation container">
        <input type="checkbox" name="user_pavillon_confirmation" id="user_pavillon_confirmation">
        <label for="user_pavillon_confirmation">Pavillon genehmigen</label>
    </div>

    <div class="select-stand-price-container container">
        <h3>Preis zuweisen*</h3>
        <input type="number" name="select_price" id="select-width" min="1" placeholder="Standpreis in €" required>
    </div>

    <div class="button-row">

        <input type="submit" class="btn-primary <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
               value="Für den Kunden vorbereiten">
        <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
    </div>
</div>