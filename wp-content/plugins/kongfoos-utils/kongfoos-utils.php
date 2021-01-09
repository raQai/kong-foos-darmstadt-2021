<?php

/**
 * Plugin Name: Kongfoos Utils
 * Description: CPT für Trainingszeiten, Tische, Angebote, Social Media
 * Author: Patrick Bogdan
 * Version: 1.0.0
 * TODO:
 * add Training CPT
 * handle latest posts with additional button
 */

defined('ABSPATH') or die('Nope!');

/**
 * CPT Vendors
 */
function kf_vendors_cpt()
{
   $labels = array(
      'name' => _x('Tische', 'kongfoos-textdomain'),
      'singular_name' => _x('Tisch', 'kongfoos-textdomain'),
   );
   $args = array(
      'label' => __('Tische', 'kongfoos-textdomain'),
      'description' => __('Tischhersteller und Modelle', 'kongfoos-textdomain'),
      'labels' => $labels,
      'supports' => array('title', 'thumbnail', 'page-attributes'),
      'hierarchical' => true,
      'public' => true,
      'menu_icon' => 'dashicons-awards',
      'show_ui' => true,
      'show_in_menu' => true,
      'menu_position' => 5,
      'exclude_from_search' => true,
   );
   register_post_type('kf_vendors', $args);
}

// CPT Vendors shortcode kf_vendors_cpt
function kf_vendors_cpt_short_code()
{
   $args = array(
      'post_type' => 'kf_vendors',
      'post_status' => 'publish',
      'orderby' => 'menu_order',
      'order' => 'DESC',
   );

   $string = '';
   $query = new WP_Query($args);
   if ($query->have_posts()) {
      $string .= '<ul class="kf-vendors-items">';
      while ($query->have_posts()) {
         $query->the_post();
         $string .= '<li class="kf-vendors-item">';
         $string .= '<img class="kf-vendors-logo" src="' . get_the_post_thumbnail_url(get_the_ID(), 'full') . '" alt="' . get_the_title(get_the_ID()) . '">';
         $string .= '</li>';
      }
      $string .= '</ul>';
   }
   wp_reset_postdata();
   return $string;
}

add_action('init', 'kf_vendors_cpt');
add_shortcode('kf_vendors_list', 'kf_vendors_cpt_short_code');

/**
 * CPT Offers
 */
function kf_offers_cpt()
{
   $labels = array(
      'name' => _x('Angebote', 'kongfoos-textdomain'),
      'singular_name' => _x('Angebot', 'kongfoos-textdomain'),
   );
   $args = array(
      'label' => __('Angebote', 'kongfoos-textdomain'),
      'description' => __('Veranstaltungsangebote', 'kongfoos-textdomain'),
      'labels' => $labels,
      'supports' => array('title', 'thumbnail', 'page-attributes'),
      'hierarchical' => true,
      'public' => true,
      'menu_icon' => 'dashicons-cart',
      'show_ui' => true,
      'show_in_menu' => true,
      'menu_position' => 5,
      'exclude_from_search' => true,
   );
   register_post_type('kf_offers', $args);
}

// CPT Vendors shortcode kf_offers_cpt
function kf_offers_cpt_short_code()
{
   $args = array(
      'post_type' => 'kf_offers',
      'post_status' => 'publish',
      'orderby' => 'menu_order',
      'order' => 'ASC',
   );

   $string = '';
   $query = new WP_Query($args);
   if ($query->have_posts()) {
      $string .= '<ul class="kf-offers-items">';
      while ($query->have_posts()) {
         $query->the_post();
         $string .= '<li class="kf-offers-item">';
         $string .= '<figure class="kf-offers-figure">';
         $string .= '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'full') . '" alt="' . get_the_title(get_the_ID()) . '">';
         $string .= '<figcaption>' . get_the_title(get_the_ID()) . '</figcaption>';
         $string .= '</figure>';
         $string .= '</li>';
      }
      $string .= '</ul>';
   }
   wp_reset_postdata();
   return $string;
}

add_action('init', 'kf_offers_cpt');
add_shortcode('kf_offers_list', 'kf_offers_cpt_short_code');

/**
 * CPT Opening hours
 * TODO
 */
// CPT Opening hours shortcode kf_opening_cpt
// TODO make this handle arguments like ID to only display certain IDs
//      display shortcode for each opening for easy access

/**
 * CPT Social Media
 * TODO
 */
// CPT Social media shortcode kf_social_cpt
// TODO make this handle arguments different sizes?

/**
 * Enable shortcodes in footer text widget
 * http://stephanieleary.com/2010/02/using-shortcodes-everywhere/
 */
add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode');