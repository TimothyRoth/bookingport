<div class="wrapper">

    <h1 class="wp-block-heading page-headline">Herzlich Willkommen</h1>
    <p>Hier können Sie einen Stand buchen, gebuchte Stände einsehen oder Ihre Daten ändern.</p>

    <?php
    if (current_user_can('administrator')) {
        include(BOOKINGPORT_PLUGIN_PATH . 'inc/template_parts/dashboard/bookingport_dashboard_admin.php');
    }

    if (current_user_can('privat')) {
        include(BOOKINGPORT_PLUGIN_PATH . 'inc/template_parts/dashboard/bookingport_dashboard_customer_private.php');
    }

    if (is_user_logged_in() && !current_user_can('administrator') && !current_user_can('privat')) {
        include(BOOKINGPORT_PLUGIN_PATH . 'inc/template_parts/dashboard/bookingport_dashboard_customer.php');
    } ?>

</div>