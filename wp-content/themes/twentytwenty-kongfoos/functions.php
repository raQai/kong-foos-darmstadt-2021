<?php

/* set default theme colors */
$kf_background_color = '0f0f0f'; //'0B1208';
$kf_text = '#f9f9f9';
$kf_accent = '#6D9806';
$kf_secondary = '#2a2a2a'; //'#56704c';
$kf_borders = '#1a1a1a'; //'#2f3a2b';

set_theme_mod('background_color', $kf_background_color);
set_theme_mod('cover_template_overlay_background_color', '#' . $kf_background_color);
set_theme_mod('cover_template_fixed_background', true);
set_theme_mod('enable_header_search', false);
set_theme_mod('show_author_bio', false);
set_theme_mod(
   'accent_accessible_colors',
   array(
      'content' => array(
         'text' => $kf_text,
         'accent' => $kf_accent,
         'secondary' => $kf_secondary,
         'borders' => $kf_borders,
         'background' => '#' . $kf_background_color,
      ),
      'header-footer' => array(
         'text' => $kf_text,
         'accent' => $kf_accent,
         'secondary' => $kf_secondary,
         'borders' => $kf_borders,
         'background' => '#' . $kf_background_color,
      ),
   )
);

function add_slug_body_class($classes)
{
   if (is_singular('page')) {
      global $post;
      if (isset($post)) {
         $classes[] = $post->post_type . '-' . $post->post_name;
      }
   }
   return $classes;
}
add_filter('body_class', 'add_slug_body_class');

add_action('init', function () {
   add_post_type_support('page', 'excerpt');
});

add_filter('document_title_separator', function ($sep) {
   return "|";
});

/* FIXME do not enqueue parent style, remove redundant styles, minifiy css */
/*
function enqueue_parent_styles()
{
   wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
*/
/*
function kongfoos_add_google_fonts()
{
   wp_enqueue_style('kongfoos-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto&display=swap', false);
}
add_action('wp_enqueue_scripts', 'kongfoos_add_google_fonts');
*/

add_filter('twentytwenty_show_categories_in_entry_header', '__return_false');
add_filter('twentytwenty_post_meta_location_single_top', function ($post_meta) {
   return array('post-date');
});
add_filter('twentytwenty_post_meta_location_single_bottom', '__return_empty_array');

/**
 * Disable the emoji's
 */
function disable_emojis()
{
   remove_action('wp_head', 'print_emoji_detection_script', 7);
   remove_action('admin_print_scripts', 'print_emoji_detection_script');
   remove_action('wp_print_styles', 'print_emoji_styles');
   remove_action('admin_print_styles', 'print_emoji_styles');
   remove_filter('the_content_feed', 'wp_staticize_emoji');
   remove_filter('comment_text_rss', 'wp_staticize_emoji');
   remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

   // Remove from TinyMCE
   add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
}
add_action('init', 'disable_emojis');

/**
 * Filter out the tinymce emoji plugin.
 */
function disable_emojis_tinymce($plugins)
{
   if (is_array($plugins)) {
      return array_diff($plugins, array('wpemoji'));
   } else {
      return array();
   }
}


/**
 * dequeue regular 2020 index js
 */
function kf_parent_js_dequeue()
{
   wp_dequeue_script('twentytwenty-js');
}

/**
 * enqueue child index js
 */
function kf_js_enqueue_scripts()
{
   wp_enqueue_script('kongfoos-js', get_stylesheet_directory_uri() . '/assets/js/index.js', false, true);
}

/**
 * dequeue frontend jquery
 */
function kf_jquery_scripts()
{
   if (is_admin() || $GLOBALS['pagenow'] === 'wp-login.php') {
      return;
   }

   wp_dequeue_script('jquery');
   wp_deregister_script('jquery');
}

/**
 * dequeue print styles
 */
function kf_print_styles()
{
   wp_dequeue_style('twentytwenty-print-style');
   wp_deregister_style('twentytwenty-print-style');
}

/**
 * dequeue cf7 frontend scripts
 */
function kf_cf7_scripts()
{
   if (!is_page(array('kontakt'))) {
      wp_dequeue_script('google-recaptcha');
      wp_deregister_script('google-recaptcha');
      wp_dequeue_script('contact-form-7');
      wp_deregister_script('contact-form-7');
   }
}
/**
 * dequeue cf7 frontend styles
 */
function kf_cf7_styles()
{
   if (!is_page(array('kontakt'))) {
      wp_dequeue_style('contact-form-7');
      wp_deregister_style('contact-form-7');
   }
}

add_action('wp_enqueue_scripts', 'kf_js_enqueue_scripts');
add_action('wp_print_scripts', 'kf_parent_js_dequeue', 100);
add_action('wp_print_scripts', 'kf_jquery_scripts');
add_action('wp_print_styles', 'kf_print_styles');
add_action('wp_print_scripts', 'kf_cf7_scripts');
add_action('wp_print_styles', 'kf_cf7_styles');

/**
 * disable wp dashboard events and news
 */
function remove_dashboard_widgets()
{
   global $wp_meta_boxes;

   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

function defer_parsing_of_js($url)
{
   if (is_user_logged_in()) {
      return $url;
   } //don't break WP Admin
   if (FALSE === strpos($url, '.js')) {
      return $url;
   }
   return str_replace(' src', ' defer src', $url);
}

add_filter('script_loader_tag', 'defer_parsing_of_js', 10);