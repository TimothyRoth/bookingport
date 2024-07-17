<?php
$user = wp_get_current_user();
$userRole = null;
if (is_object($user) && isset($user->roles[0])) {
    $userRole = $user->roles[0];
}

/*Declaring variables for consistent results preventing typos */
$private = 'privat';
$commercial = 'gewerblich';
$association = 'verein';

/* Declaring the specific Frontend Output for the booking type depending on the user role */
$formatted_role = match ($userRole) {
    $private => 'Private Anbieter',
    $commercial => 'Gewerblich',
    $association => 'Vereine',
    default => null
};

$option_table = get_option(BOOKINGPORT_Settings::$option_table);

if (isset($formatted_role) && isset($option_table[BOOKINGPORT_Settings::$option_allow_stand_booking])) {
    wp_redirect($option_table[BOOKINGPORT_Settings::$option_redirects_dashboard]);
    exit();
}

if ($userRole === $private && !isset($option_table[BOOKINGPORT_Settings::$option_allow_private_users])) {
    wp_redirect($option_table[BOOKINGPORT_Settings::$option_redirects_booking_not_available]);
    exit();
} ?>

<div class="wrapper">

    <h1 class="page-headline"><?= current_user_can('administrator') ? 'Anfragen bearbeiten' : the_title() ?></h1>

    <div class="form">

        <?php if (!current_user_can('administrator')) { ?>
            <h3 class="headline-conditions">Rahmenbedingungen</h3>
            <p>
                <label for="booking_year">Buchungsjahr</label>
                <input class="prefilled-input" type="text" name="booking_year" placeholder="<?= date('Y') ?>">
            </p>
            <p>
                <label for="booking_type">Verkäufer</label>
                <input class="prefilled-input" type="text" name="booking_type"
                       placeholder="<?= $formatted_role ?>">
            </p>
        <?php } ?>

        <?php if ($userRole === $private) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_private.php');
        } ?>

        <?php if ($userRole === $commercial) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_business.php');
        } ?>

        <?php if ($userRole === $association) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_association.php');
        } ?>

        <?php if (current_user_can('administrator')) {
            include(BOOKINGPORT_PLUGIN_PATH . 'inc/template_parts/stand-booking/bookingport_stand_booking_admin.php');
        } ?>

        <?php if (!current_user_can('administrator')) { ?>
            <p class="disclaimer">* Pflichtfelder</p>
        <?php } ?>
    </div>
</div>
<!-- adding to close the main tag inside the shortcode template to exclude the modal => otherwise the modal will slide out with the <main> container -->
</main>

<?php if (current_user_can('administrator') || current_user_can('privat')) { ?>
    <div class="modal" id="privat-stand-booking-modal">
        <div class="close-stand-booking-modal" id="close-stand-booking-modal">
            <div class="stripe"></div>
            <div class="stripe"></div>
        </div>
        <h2 id="map-overview">Übersichtskarte</h2>
        <div class="customer-stand-booking-map map" id="customer-stand-booking-map"></div>
        <div class="map-explanation">
            In der <a href="#map-overview">Übersichtskarte</a> können Sie einzelne Stände anwählen, sowie
            zusätzliche
            Informationen zu Eingängen, Ständen, Gassen in
            der Nähe erhalten.
            Den angewählten Stand können Sie dann weiter unten in der <a href="#stand-selection">Standauswahl</a>
            auswählen und zu Ihrer "aktuellen
            Auswahl" hinzufügen.
            Mit "Auswahl bestätigen" gelangen Sie weiter zu Ihren Reservierungen.
        </div>
        <div class="info-wrapper">
            <div class="i-am-legend">
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                    <p>Verfügbare Stände</p>
                </div>
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-grey.svg">
                    <p>Belegte Stände</p>
                </div>

                <div class="row">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/maps/Info_blau.svg">
                    <p>Info zur Freifläche</p>
                </div>
            </div>
            <div class="stand-filter">
                <label for="show-accessible-stands">Nur verfügbare Stände anzeigen</label>
                <input id="show-accessible-stands" type="checkbox" value="accessible" name="show-accessible-stands">
            </div>
        </div>
        <h3 id="stand-selection">Standauswahl</h3>
        <div class="customer-current-stand-status-info-container">
            <div class="currently-reserved-wrapper">
                <?php if (!current_user_can('administrator')) { ?>
                    <h4>Bereits für Sie reserviert</h4>
                <?php } ?>
                <?php if (current_user_can('administrator')) { ?>
                    <h4>Bereits in Reservierung</h4>
                <?php } ?>
                <div class="show-current-reservations"></div>
            </div>
            <div class="current-selection-wrapper">
                <h4>Aktuelle Auswahl</h4>
                <div class="show-current-selection"></div>
            </div>
        </div>
        <div class="form">
            <p id="search-filter-wrapper">
                <input type="text" name="filter_prefered_street" placeholder="Wunschstraße eingeben">
            </p>
            <div id="filter-prefered-street-result-container"></div>
            <div class="btn-primary close-privat-stand-booking-modal" id="submit-stand-booking">Auswahl übernehmen</div>
        </div>
    </div>
<?php } ?>

