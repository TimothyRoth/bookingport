<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>
<div class="market-overview">
    <table>
        <thead>
            <tr>
                <td>Anzahl der Nutzer</td>
                <td>Jahresumsatz (<?= date('Y') ?>)</td>
                <td>Gebuchte Stände</td>
                <td>Freie Stände</td>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <?= BOOKINGPORT_WCHandler::get_total_user_amount() ?>
            </td>
            <td>
                <?= BOOKINGPORT_WCHandler::get_total_sales_volume(date('Y')) ?>€
            </td>
            <td>
                <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold) ?>
            </td>
            <td>
                <?= BOOKINGPORT_CptStands::get_amount_of_stands_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) ?>
            </td>
        </tr>
        </tbody>
    </table>

</div>

<div class="menu-admin-container">
    <ul id="menu-admin" class="menu">
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/mein-konto/">Mein Konto</a></li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_admin_map] ?>/">Admin
                Karte</a></li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>/">Standbuchungen</a>
        </li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_expired_offers] ?>/">Abgelaufene Angebote</a>
        </li>
        <li class="menu-item"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_admin_customers] ?>/">Kunden</a>
        </li>
        <li class="menu"><a
                    href="<?= get_home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>/">Rechnungsverwaltung</a>
        </li>
        <li class="menu-item menu-item-logout"><a
                    href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
        </li>
    </ul>
</div>