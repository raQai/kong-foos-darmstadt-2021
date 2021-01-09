<?php

defined('ABSPATH') or die('Nope!');

class BIWS_Events
{
    protected $plugin_name;

    protected $version;

    private $loader;

    private $taxonomy_loader;

    private $cpt;

    private $slug;

    public function __construct()
    {
        if (defined('BIWS_EVENTS_VERSION')) {
            $this->version = BIWS_EVENTS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'biws-events';
        $this->slug = 'termine';

        $this->plugin_dir_path = plugin_dir_path(dirname(__FILE__));
        $this->text_domain = 'biws-textdomain';

        load_plugin_textdomain($this->text_domain, false, $this->plugin_dir_path . 'lang/');

        $this->load_dependencies();
    }

    private function load_dependencies()
    {
        if (!class_exists('BIWS_EventsLoader')) {
            require $this->plugin_dir_path . 'includes/BIWSEventsLoader.class.php';
        }
        if (!class_exists('BIWS_EventsTaxonomyLoader')) {
            require $this->plugin_dir_path . 'includes/BIWSEventsTaxonomyLoader.class.php';
        }
        if (!class_exists('BIWS_EventsCPT')) {
            require $this->plugin_dir_path . 'includes/BIWSEventsCPT.class.php';
        }
        if (!class_exists('BIWS_EventsSchemaOrg')) {
            require $this->plugin_dir_path . 'includes/BIWSEventsSchemaOrg.class.php';
        }

        $this->loader = new BIWS_EventsLoader();
        $this->taxonomy_loader = new BIWS_EventsTaxonomyLoader($this->slug);
        $this->cpt = new BIWS_EventsCPT($this->slug);
    }

    public function init()
    {
        $this->register_taxonomies();
        $this->register_cpt();

        $this->loader->run();
    }

    private function register_taxonomies()
    {
        $this->taxonomy_loader->register($this->loader);
    }

    private function register_cpt()
    {
        $this->cpt->init($this->loader);
    }
}
