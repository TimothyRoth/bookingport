<?php
if (isset($_SESSION['username'])) {
    ?>

    <div class="wrapper">
        <div class="headline">
            <h1>Herzlich Willkommen</h1>
            <h2><?= $_SESSION['username'] ?></h2>
        </div>
        <div>
            <p>Wir haben Ihre Anfrage erhalten.</p>
            <p>In kürze erhalten Sie von uns eine E-Mail, in der Sie Ihre Registrierung abschließen können.  (Bitte
                prüfen Sie auch Ihren Spamordner)</p>
        </div>
    </div>

<?php }