<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="nav-menu is-admin">
    <div class="menu-admin-container">
        <ul id="menu-admin" class="menu">
            <li id="menu-item-134"
                class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>/"
                   aria-current="page">Übersicht</a>
            </li>
            <li id="menu-item-50" class="menu-item">
                <a href="<?= home_url() ?>/mein-konto/">Mein Konto</a>
            </li>
            <li id="menu-item-172" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_admin_map] ?>/">Admin
                    Karte</a>
            <li id="menu-item-170" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>/">Standbuchungen</a>
            </li>
            <li id="menu-item-170" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_expired_offers] ?>/">Abgelaufene Angebote</a>
            </li>
            <li id="menu-item-162" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_admin_customers] ?>/">Kunden</a>
            </li>
            <li id="menu-item-171" class="menu-item">
                <a href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>/">Rechnungsverwaltung</a>
            </li>
            <li class="menu-item menu-item-logout">
                <a href="<?= wp_logout_url(home_url()) ?>">Logout</a>
            </li>
    </div>