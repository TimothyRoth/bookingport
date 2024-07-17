<?php

if (current_user_can('administrator')) {
    include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/invoice/bookingport_invoice_admin.php');
}

if (!current_user_can('administrator')) {
    include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/invoice/bookingport_invoice_user.php');
}
