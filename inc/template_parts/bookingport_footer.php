<footer>
    <div class="footer-menu">
        <div class="wrapper">
            <nav class="footer-top">
                <?php include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/menu-footer/bookingport_menu_footer.php'); ?>
            </nav>
            <div class="footer-mid">
                <p>Technischer Dienstleister:</p>
                <a href="https://www.marketport.de" target="_blank">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/marketport-logo.svg">
                </a>
            </div>
            <div class="footer-bottom">
                <?php
                $options_table = get_option(BOOKINGPORT_Settings::$option_table);
                $market_name = $options_table[BOOKINGPORT_Settings::$option_market_owner];
                ?>
                <p>© <span id="current-year"></span><?= date("Y") ?> <span
                            id="company-name"> <?= $market_name ?></span></p>
            </div>
        </div>
    </div>
</footer>