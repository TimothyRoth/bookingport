<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="nav-menu user-logged-out">
    <div class="menu-ausgeloggter-benutzer-container">
        <ul id="menu-ausgeloggter-benutzer" class="menu">
            <li id="menu-item-37"
                class="menu-item">
                <a href="<?= home_url() ?>/mein-konto/" aria-current="page">Login</a>
            </li>
            <li id="menu-item-38" class="menu-item"><a
                        href="<?= home_url() ?>/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_registration] ?>/">Registrieren</a>
            </li>
        </ul>
    </div>

    <?php include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/menu-footer/bookingport_menu_footer.php'); ?>
</div>