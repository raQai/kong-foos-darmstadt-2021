<?php

defined('ABSPATH') or die('Nope!');

class BIWS_EventVenue
{
    private $posttype;

    private $taxonomy;

    private $slug;

    private $labels;

    public function __construct($posttype)
    {
        $this->posttype = $posttype;

        $this->taxonomy = 'event_venue';

        $this->slug = 'venue';

        $this->labels = array(
            'name' => _x('Venues', 'taxonomy general name'),
            'singular_name' => _x('Venue', 'taxonomy singular name'),
            'search_items' =>  __('Search Venue'),
            'all_items' => __('All Venues'),
            'parent_item' => __('Parent Venue'),
            'parent_item_colon' => __('Parent Venue:'),
            'edit_item' => __('Edit Venue'),
            'update_item' => __('Update Venue'),
            'add_new_item' => __('Add New Venue'),
            'new_item_name' => __('New Venue Name'),
            'menu_name' => __('Venues'),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_event_venue');

        $loader->add_action('event_venue_add_form_fields', $this, 'biws_add_venue_fields');
        $loader->add_action('created_event_venue', $this, 'biws_save_venue_meta', 10, 2);
        $loader->add_action('event_venue_edit_form_fields', $this, 'biws_edit_venue_fields', 10, 2);
        $loader->add_action('edited_event_venue', $this, 'biws_update_venue_meta', 10, 2);
        $loader->add_filter('manage_edit-event_venue_columns', $this, 'biws_add_venue_columns');
        $loader->add_filter('manage_event_venue_custom_column', $this, 'biws_add_venue_column_content', 10, 3);
    }

    public function register_event_venue()
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

    public function biws_add_venue_fields($taxonomy)
    {
?>
        <div class="form-field term-street-wrap">
            <label for="venue-street"><?php _e('Street and Nr', 'biws-textdomain'); ?></label>
            <input name="venue-street" id="venue-street" type="text" value="" size="40" placeholder="Beispielstraße 9">
        </div>

        <div class="form-field term-zipcode-wrap">
            <label for="venue-zipcode"><?php _e('Zipcode', 'biws-textdomain'); ?></label>
            <input name="venue-zipcode" id="venue-zipcode" type="text" value="" size="40" placeholder="12345">
        </div>

        <div class="form-field form-required term-city-wrap">
            <label for="venue-city"><?php _e('City', 'biws-textdomain'); ?></label>
            <input name="venue-city" id="venue-city" type="text" value="" size="40" aria-required="true" placeholder="Ortsname">
        </div>
    <?php
    }


    public function biws_save_venue_meta($term_id, $tt_id)
    {
        if (isset($_POST['venue-street']) && '' !== $_POST['venue-street']) {
            add_term_meta($term_id, 'venue-street', sanitize_text_field($_POST['venue-street']));
        }
        if (isset($_POST['venue-zipcode']) && '' !== $_POST['venue-zipcode']) {
            add_term_meta($term_id, 'venue-zipcode', sanitize_text_field($_POST['venue-zipcode']));
        }
        if (isset($_POST['venue-city']) && '' !== $_POST['venue-city']) {
            add_term_meta($term_id, 'venue-city', sanitize_text_field($_POST['venue-city']));
        }
    }

    public function biws_edit_venue_fields($term, $taxonomy)
    {
        $venue_street = get_term_meta($term->term_id, 'venue-street', true);
        $venue_zipcode = get_term_meta($term->term_id, 'venue-zipcode', true);
        $venue_city = get_term_meta($term->term_id, 'venue-city', true);
    ?>
        <tr class="form-field term-street-wrap">
            <th scope="row">
                <label for="venue-street"><?php _e('Street and Nr', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="venue-street" id="venue-street" type="text" value="<?php echo $venue_street; ?>" size="40">
            </td>
        </tr>

        <tr class="form-field term-zipcode-wrap">
            <th scope="row">
                <label for="venue-zipcode"><?php _e('Zipcode', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="venue-zipcode" id="venue-zipcode" type="text" value="<?php echo $venue_zipcode; ?>" size="40">
            </td>
        </tr>

        <tr class="form-field form-required term-city-wrap">
            <th scope="row">
                <label for="venue-city"><?php _e('City', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="venue-city" id="venue-city" type="text" value="<?php echo $venue_city; ?>" size="40" aria-required="true">
            </td>
        </tr>
<?php
    }

    public function biws_update_venue_meta($term_id, $tt_id)
    {
        if (isset($_POST['venue-street']) && '' !== $_POST['venue-street']) {
            update_term_meta($term_id, 'venue-street', sanitize_text_field($_POST['venue-street']));
        }
        if (isset($_POST['venue-zipcode']) && '' !== $_POST['venue-zipcode']) {
            update_term_meta($term_id, 'venue-zipcode', sanitize_text_field($_POST['venue-zipcode']));
        }
        if (isset($_POST['venue-city']) && '' !== $_POST['venue-city']) {
            update_term_meta($term_id, 'venue-city', sanitize_text_field($_POST['venue-city']));
        }
    }

    public function biws_add_venue_columns($columns)
    {
        $columns['venue_street'] = __('Street', 'biws-textdomain');
        $columns['venue_zipcode'] = __('Zipcode', 'biws-textdomain');
        $columns['venue_city'] = __('City', 'biws-textdomain');
        return $columns;
    }

    public function biws_add_venue_column_content($content, $column_name, $term_id)
    {
        if ($column_name === 'venue_street') {
            $meta = get_term_meta($term_id, 'venue-street', true);
            return $content . esc_attr($meta);
        } else if ($column_name === 'venue_zipcode') {
            $meta = get_term_meta($term_id, 'venue-zipcode', true);
            return $content . esc_attr($meta);
        } else if ($column_name === 'venue_city') {
            $meta = get_term_meta($term_id, 'venue-city', true);
            return $content . esc_attr($meta);
        }
        return $content;
    }
}
