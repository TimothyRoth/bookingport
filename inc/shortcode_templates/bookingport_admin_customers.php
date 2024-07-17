<div class="wrapper">
    <h1 class="page-headline"><?php the_title(); ?></h1>
    <div class="dropdown-filter select-filter">
        <label for="customer-dropdown-filter">Sortieren nach</label>
        <select name="customer-dropdown-filter">
            <option value="">Alle Kunden</option>
            <option value="privat">Privat Verkäufer</option>
            <option value="gewerblich">Gewerbliche Verkäufer</option>
            <option value="verein">Vereine</option>
        </select>
    </div>
    <div class="search-filter">
        <input placeholder="Suche..." name="customer-search" type="text">
    </div>

    <div class="customer-search-results">
        <?php
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];

        $args = [
            'role__not_in' => 'administrator',
            'roles' => 'privat', 'gewerbe', 'verein',
        ];

        $users = get_users($args);
        $displayedUsers = 1;

        foreach ($users ?? [] as $user) {
            if ($displayedUsers > 5) {
                break;
            }
            $registration_date = $user->user_registered;
            $first_name = get_user_meta($user->ID, 'billing_first_name', true);
            $last_name = get_user_meta($user->ID, 'billing_last_name', true);
            $active_status = ($user->user_status == 0) ? 'Inaktiv' : 'Aktiv';
            $phone = get_user_meta($user->ID, 'shipping_phone', true);
            $email = $user->user_email;
            $token = get_user_meta($user->ID, 'verification_token', true);
            $active_status = ($user->user_status == 0) ? 'Inaktiv' : 'Aktiv';
            $account_verified = false;

            $userRole = null;
            if (is_object($user) && isset($user->roles[0])) {
                $userRole = $user->roles[0];
            }

            if (empty($token)) {
                $account_verified = true;
            }
            ob_start(); ?>

            <div class="user-row">
                <div class="open-additional-customer-information">
                    <div class="stripe vertical"></div>
                    <div class="stripe horizonal"></div>
                </div>
                <div class="registration-date">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/date-blue.svg">
                    <p><?php echo date('Y-m-d', strtotime($registration_date)); ?><?php if (!$account_verified) {
                            echo '(Bestätigung ausstehend)';
                        } ?></p>
                </div>
                <div class="name">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                    <p><?= $first_name . ' ' . $last_name ?></p>
                </div>
                <div class="role">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                    <p><?= $userRole ?></p>
                </div>
                <div class="hidden-user-information">
                    <div class="active-status <?= $active_status ?>">
                        <?php if ($active_status === 'Aktiv') { ?>
                            <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/Aktiv_Grün.svg">
                        <?php } else { ?>
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/Aktiv_Rot.svg">
                        <?php } ?>
                        <p>Aktiv: <?php echo $active_status === 'Aktiv' ? 'Ja' : 'Nein'; ?></p>
                    </div>
                    <div class="phone">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/phone-blue.svg">
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

            <?php echo ob_get_clean();
            $displayedUsers++;
        }

        if (count($users) > $displayedUsers) { ?>
            <p class="post-count">Zeige 1 bis 5 von <?= count($users) ?> Daten</p>
        <?php } ?>
        <?php if (count($users) > 5) {
            echo '<div class="show-more-users btn-secondary">Mehr anzeigen</div>';
        }
        ?>
    </div>

</div>