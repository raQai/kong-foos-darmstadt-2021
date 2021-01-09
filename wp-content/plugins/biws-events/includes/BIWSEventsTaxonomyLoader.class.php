<?php

defined('ABSPATH') or die('Nope!');

class BIWS_EventsTaxonomyLoader
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
        $this->load_dependencies();
    }

    private function load_dependencies()
    {
        $plugin_dir_path = plugin_dir_path(__FILE__);

        if (!class_exists('BIWS_SimpleTaxonomy')) {
            require $plugin_dir_path . 'taxonomy/SimpleTaxonomy.class.php';
        }
        if (!class_exists('BIWS_EventDiscipline')) {
            require $plugin_dir_path . 'taxonomy/EventDiscipline.class.php';
        }
        if (!class_exists('BIWS_EventTag')) {
            require $plugin_dir_path . 'taxonomy/EventTag.class.php';
        }
        if (!class_exists('BIWS_EventVenue')) {
            require $plugin_dir_path . 'taxonomy/EventVenue.class.php';
        }
        if (!class_exists('BIWS_EventPartner')) {
            require $plugin_dir_path . 'taxonomy/EventPartner.class.php';
        }
    }

    public function register($loader)
    {
        $event_type = new BIWS_SimpleTaxonomy($this->slug, 'event_type', 'category', 'Category', 'Categories');
        $event_type->init($loader);

        $event_discipline = new BIWS_EventDiscipline($this->slug);
        $event_discipline->init($loader);

        $event_tags = new BIWS_EventTag($this->slug);
        $event_tags->init($loader);

        $event_venue = new BIWS_EventVenue($this->slug);
        $event_venue->init($loader);

        $event_table = new BIWS_SimpleTaxonomy($this->slug, 'event_table', 'table', 'Table', 'Tables');
        $event_table->init($loader);

        $event_partner = new BIWS_EventPartner($this->slug);
        $event_partner->init($loader);

        unset($event_type);
        unset($event_tags);
        unset($event_discipline);
        unset($event_venue);
        unset($event_table);
        unset($event_partner);
    }
}
