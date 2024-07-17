<div id="error-messages"></div>

<h3 class="headline-stand-selection" id="headline-stand-selection">Standauswahl</h3>
<?php
$user_ID = get_current_user_id();
$session_items = $_SESSION['items'] ?? [];
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

$args = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'post__in' => $session_items,
];

$selectedItems = new WP_Query($args);

?>

<div class="no-selected-stands <?php if (!empty($session_items)) {
    echo 'hide';
} ?>">
    <p id="choose-stands-disclaimer">Einen oder mehrere Standplätze wählen</p>
    <div class="btn-primary trigger-privat-stand-booking-modal">Standplatz auf Karte wählen</div>
    <a class="faq-link" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_faq] ?>">
        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/faq.svg">
        Sie haben Fragen zum Buchungsvorgang?
    </a>

</div>

<div class="selected-stands <?php if (empty($session_items)) {
    echo 'hide';
} ?>">
    <div id="selected-stands-container">
        <?php if ($selectedItems->found_posts > 0 && !empty($session_items)) {
            $counter = 1;
            $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

            foreach ($selectedItems->posts ?? [] as $p) {
                $id = $p->ID;
                $has_pavillon = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';
                $price = wc_get_product($product_id)->get_price();
                $size = current_user_can('privat', 'administrator') ? '3m/1 Tapeziertisch' : '';
                ?>

                <div class="single-result">
                    <div class="upper-row">
                        <p>Stand <?= $counter ?></p>
                        <div class="delete-item" data-src="<?= $id ?>">Stand löschen <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/delete.svg">
                        </div>
                    </div>
                    <div class="inner-content">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-white.svg">
                            <p class="stand-street"><?= $street . ' ' . $housenumber ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                        <div class="row selected-stand-space">
                            <img class="stand-space-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-white.svg">
                            <p class="stand-space"><?= $size ?></p>
                        </div>
                        <div class="row selected-stand-price">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-price"><?= $price ?> €</p>
                        </div>
                    </div>
                    <?php if ($has_pavillon) { ?>
                        <div class="checkbox-container">
                            <input type="checkbox" id="user_pavillon_<?= $counter ?>" name="user_pavillon"
                                   data-src="<?= $id ?>" <?php if (isset($_SESSION['ordersWithPavillon'][$id]) && $_SESSION['ordersWithPavillon'][$id] === 'true') {
                                echo 'checked';
                            } ?>>
                            <label for="user_pavillon_<?= $counter ?>"> Ich möchte am Stand einen Pavillon
                                aufbauen </label>
                        </div>
                    <?php } ?>
                </div>
                <?php
                $counter++;
            }
        }
        ?>
    </div>
    <div class="btn-secondary trigger-privat-stand-booking-modal">Standauswahl ändern?</div>
    <div class="remarks-container">
        <h3>Sonstiges</h3>
        <p>Anmerkungen / Wünsche</p>
        <textarea name="user_remarks" id="user-remarks"
                  placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."></textarea>
    </div>
    <div class="button-row">


        <input type="submit" onclick="" class="btn-primary <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
               value="Zahlung und Buchungsabschluss">
        <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
    </div>
</div>
