<?php

defined('ABSPATH') or die('Nope!');

class BIWS_SimpleTaxonomy
{
    private $posttype;

    private $taxonomy;

    private $slug;

    private $labels;

    public function __construct($posttype, $taxonomy, $slug, $singular, $plural)
    {
        $this->posttype = $posttype;

        $this->taxonomy = $taxonomy;

        $this->slug = $slug;

        $this->labels  = array(
            'name' => _x($plural, 'taxonomy general name'),
            'singular_name' => _x($singular, 'taxonomy singular name'),
            'search_items' =>  __('Search ' . $singular),
            'all_items' => __('All ' . $plural),
            'parent_item' => __('Parent ' . $singular),
            'parent_item_colon' => __('Parent ' . $singular . ':'),
            'edit_item' => __('Edit ' . $singular),
            'update_item' => __('Update ' . $singular),
            'add_new_item' => __('Add New ' . $singular),
            'new_item_name' => __('New ' . $singular . ' Name'),
            'menu_name' => __($plural),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_simple_taxonomy');
    }

    public function register_simple_taxonomy()
    {
        register_taxonomy(
            $this->taxonomy,
            $this->posttype,
            array(
                'hierarchical' => true,
                'labels' => $this->labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_tag_cloud' => false,
                'show_in_rest' => true,
                'query_var' => true,
                'rewrite' => array('slug' => $this->slug),
            )
        );
    }
}
