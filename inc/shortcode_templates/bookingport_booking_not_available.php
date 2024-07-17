<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="wrapper">
    <p>Leider sind derzeit keine Buchungen für private Nutzer möglich. Im Moment haben die gewerblichen Anlieger ein
        Vorbuchungsrecht von ca. 2 Wochen. Wir bitten Sie um etwas Geduld.</p>
    <p class="btn-primary"><a
                href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>">Übersicht</a></p>
</div>
