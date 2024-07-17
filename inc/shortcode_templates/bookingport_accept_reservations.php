<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="wrapper">
    <h3>Derzeit liegen leider keine Reservierungen für Sie vor.</h3>
    <a class="btn-primary" href="/warenkorb">Zum Warenkorb</a>
    <a class="btn-secondary" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>">Zum
        Dashboard</a>
</div>
