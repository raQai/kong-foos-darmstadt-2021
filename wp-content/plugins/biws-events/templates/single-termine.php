<?php

/**
 * Template Name: Event Cover Template
 * Template Post Type: termine (BIWS CPT)
 */

$plugin_dir = plugin_dir_path(__FILE__);
$custom_template_path = $plugin_dir . 'template-parts/content-cover.php';
$custom_template_exsists = file_exists($custom_template_path);

get_header();
?>

<main id="site-content" role="main">

    <?php

    if (have_posts()) {
        while (have_posts()) {
            the_post();

            if ($custom_template_exsists) {
                include $custom_template_path;
            } else {
                get_template_part('template-parts/content-cover');
            }
        }
    }

    ?>

</main><!-- #site-content -->

<?php get_template_part('template-parts/footer-menus-widgets'); ?>

<?php
get_footer();

unset($plugin_dir);
unset($custom_template_path);
unset($custom_template_exists);
?>