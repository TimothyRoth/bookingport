<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="nav-menu user-logged-in <?php if (current_user_can('privat')) {
    echo 'privat';
} ?>">
    <div class="menu-eingeloggter-benutzer-container">
        <ul id="menu-eingeloggter-benutzer" class="menu">
            <li id="menu-item-135"
                class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>/"
                   aria-current="page">Übersicht</a>
            </li>
            <li id="menu-item-42" class="menu-item">
                <a href="<?= home_url() ?>/mein-konto/">Mein Konto</a>
            </li>
            <li id="menu-item-176" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_my_bookings] ?>/">Meine
                    Standbuchungen</a>
            </li>
            <li id="menu-item-447" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_customer_requests] ?>/">Meine
                    Anfragen</a>
            </li>
            <li id="menu-item-46" class="menu-item">
                <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>">Neue
                    Standbuchung</a>
            </li>
            <li id="menu-item-177" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>/">Rechnungen</a>
            </li>
            <li id="menu-item-115" class="menu-item"><a
                        href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_faq] ?>">FAQ</a>
            </li>
            <li class="menu-item menu-item-logout">
                <a href="<?= wp_logout_url(home_url()) ?>">Logout</a>
            </li>
    </div>
</div>