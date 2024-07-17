<?php
/**
 * The archive faq file
 *
 * @link https://gitlab.com/market-port-gmbh-intern/bookingport
 *
 * @package bookingport
 */

get_header();

BOOKINGPORT_Redirect::redirect_logged_out_user_to_login_page();

$option_table = get_option(BOOKINGPORT_Settings::$option_table);
$mail = $option_table[BOOKINGPORT_Settings::$option_email_general_email];
$phone = $option_table[BOOKINGPORT_Settings::$option_general_phone];
$opening_times = $option_table[BOOKINGPORT_Settings::$option_general_opening_times]; ?>

<main>
    <div class="wrapper">
        <div class="hentry content">
            <h1 class="page-headline"><?= get_queried_object()->name ?></h1>

            <?php if (have_posts()) {
                while (have_posts()) {
                    the_post(); ?>
                    <article class="single-faq">
                        <h3 class="faq-title"><?php the_title(); ?></h3>
                        <p class="faq-content"><?php the_content(); ?></p>
                    </article>
                <?php }
            } ?>

            <div class="contact-teaser">
                <div class="top">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/Frage.svg">
                    <p><?= __('Ihre Frage ist hier noch nicht beantwortet oder Sie brauchen Hilfe bei der Buchung?') ?></p>
                </div>
                <div class="bottom">
                    <?php if (!empty($opening_times)) { ?>
                        <div>
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/opening-times-white.svg">
                            <p><?= $opening_times ?></p>
                        </div>

                    <?php } ?>
                    <?php if (!empty($phone)) { ?>
                        <div>
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/phone-white.svg">
                            <a href="tel:<?= $phone ?>"><?= $phone ?></a>
                        </div>

                    <?php } ?>
                    <?php if (!empty($mail)) { ?>
                        <div>
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/mail-white.svg">
                            <a href="mailto:<?= $mail ?>"><?= $mail ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
