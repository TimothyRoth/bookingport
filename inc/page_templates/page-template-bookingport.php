<?php
/**
 * Template Name: booking port template
 *
 * This is the custom template all pages using a booketport shortcode.
 *
 * @link https://gitlab.com/market-port-gmbh-intern/bookingport
 *
 * @package bookingport
 */

get_header(); ?>
    <main>
        <?php if (have_posts()):
            while (have_posts()) : the_post();
                the_content();
            endwhile;
        endif; ?>
    </main>
<?php get_footer(); ?>