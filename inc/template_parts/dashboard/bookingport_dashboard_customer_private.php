<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="dashboard-user-wrapper">
    <ul id="menu-customer-private" class="menu">
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/mein-konto/">Mein Konto</a></li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_my_bookings] ?>/">Meine
                Standbuchungen</a></li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>">Neue
                Standbuchung</a></li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>/">Rechnungen</a>
        </li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_faq] ?>">FAQ</a>
        </li>
        <li class="menu-item menu-item-logout"><a
                    href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
        </li>
    </ul>
    <?= do_shortcode('[contact-form-7 id="b242adb" title="Fehlermeldung"]') ?>
</div>