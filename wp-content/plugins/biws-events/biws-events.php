<?php

/**
 * Plugin Name: BIWS Events
 * Description: Simple Event Plugin
 * Author: Patrick Bogdan
 * Version: 1.3.0
 * 
 * CPT events
 * TAXONOMY event_type
 * TAXONOMY event_tag
 * TAXONOMY event_discipline
 * TAXONOMY event_partner
 * TAXONOMY event_venue
 * TAXONOMY event_table
 * FIELD 'event-highlight'
 * FIELD 'event-organizer'
 * FIELD 'event-organizer-url'
 * FIELD 'event-start-date'
 * FIELD 'event-end-date'
 * FIELD 'event-time-{1-4}-time'
 * FIELD 'event-time-{1-4}-label'
 * FIELD 'event-entry-fee'
 * FIELD 'event-entry-fee-label'
 * FIELD 'event-entry-fee-description'
 * FIELD 'event-link-{1-4}-url'
 * FIELD 'event-link-{1-4}-label'
 * 
 * TODO i18n biws-textdomain
 */

defined('ABSPATH') or die('Nope!');

if (!defined('WPINC')) {
    die;
}

if (!class_exists('BIWS_Events')) {
    define('BIWS_EVENTS_VERSION', '1.2.0');

    $plugin_dir_path = plugin_dir_path(__FILE__);

    require $plugin_dir_path . 'includes/BIWSEvents.class.php';
}

$plugin = new BIWS_Events();
$plugin->init();