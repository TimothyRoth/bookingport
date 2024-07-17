<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="wrapper">
    <div class="entry-content">
        <p>Sie haben Ihre E-Mailadresse erfolgreich verifiziert. Die Einrichtung Ihres Accounts ist nun abgeschlossen.
            Nun können Sie sich auf <a href="/">fettmarkt.com</a> einen Standplatz buchen</p>

        <a class="btn-primary" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>" data-type="URL" data-id="/my-account">Los geht's</a>
    </div>
</div>
