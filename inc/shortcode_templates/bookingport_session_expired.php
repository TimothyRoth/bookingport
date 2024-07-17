<?php if (is_home() && !is_front_page()) { ?>
    <h1 class="page-title"><?php echo get_the_title(get_option('page_for_posts')); ?></h1>
<?php } ?>

<?php
$bookingport_settings = new BOOKINGPORT_Settings();
$option_table = get_option($bookingport_settings::$option_table);
?>
<div class="wrapper">
    <h2 class="page-headline">Sitzung abgelaufen</h2>
    <div class=" entry-content">
        <p>Ihre Sitzung ist leider abgelaufen.</p>
        <a class="btn-primary" href="/<?= $option_table[$bookingport_settings::$option_redirects_stand_booking] ?>">Zu Ihren Standbuchungen</a>
    </div>
</div>
