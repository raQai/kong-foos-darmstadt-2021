<?php

defined('ABSPATH') or die('Nope!');

class BIWS_EventDiscipline
{
    private $posttype;

    private $taxonomy;

    private $slug;

    private $labels;

    public function __construct($posttype)
    {
        $this->posttype = $posttype;

        $this->taxonomy = 'event_discipline';

        $this->slug = 'discipline';

        $this->labels = array(
            'name' => _x('Disciplines', 'taxonomy general name'),
            'singular_name' => _x('Discipline', 'taxonomy singular name'),
            'search_items' =>  __('Search Discipline'),
            'all_items' => __('All Disciplines'),
            'parent_item' => __('Parent Discipline'),
            'parent_item_colon' => __('Parent Discipline:'),
            'edit_item' => __('Edit Discipline'),
            'update_item' => __('Update Discipline'),
            'add_new_item' => __('Add New Discipline'),
            'new_item_name' => __('New Discipline Name'),
            'menu_name' => __('Disciplines'),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_event_discipline');

        $loader->add_action('event_discipline_add_form_fields', $this, 'biws_add_discipline_fields');
        $loader->add_action('created_event_discipline', $this, 'biws_save_discipline_meta', 10, 2);
        $loader->add_action('event_discipline_edit_form_fields', $this, 'biws_edit_discipline_fields', 10, 2);
        $loader->add_action('edited_event_discipline', $this, 'biws_update_discipline_meta', 10, 2);
        $loader->add_filter('manage_edit-event_discipline_columns', $this, 'biws_add_discipline_columns');
        $loader->add_filter('manage_event_discipline_custom_column', $this, 'biws_add_discipline_column_content', 10, 3);
    }

    public function register_event_discipline()
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

    public function biws_add_discipline_fields($taxonomy)
    {
?>
        <div class="form-field term-order-wrap">
            <label for="order"><?php _e('Order', 'biws-textdomain'); ?></label>
            <input name="order" id="order" type="number" value="0">
        </div>
    <?php
    }


    public function biws_save_discipline_meta($term_id, $tt_id)
    {
        if (isset($_POST['order']) && '' !== $_POST['order']) {
            add_term_meta($term_id, 'order', filter_var($_POST['order'], FILTER_SANITIZE_NUMBER_INT));
        }
    }

    public function biws_edit_discipline_fields($term, $taxonomy)
    {
        $order = get_term_meta($term->term_id, 'order', true);
    ?>
        <tr class="form-field term-order-wrap">
            <th scope="row">
                <label for="order"><?php _e('Order', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="order" id="order" type="number" value="<?php echo $order; ?>">
            </td>
        </tr>
<?php
    }

    public function biws_update_discipline_meta($term_id, $tt_id)
    {
        if (isset($_POST['order']) && '' !== $_POST['order']) {
            update_term_meta($term_id, 'order', filter_var($_POST['order'], FILTER_SANITIZE_NUMBER_INT));
        } else {
            delete_term_meta($term_id, 'order');
        }
    }

    public function biws_add_discipline_columns($columns)
    {
        $columns['order'] = __('Order', 'biws-textdomain');
        return $columns;
    }


    public function biws_add_discipline_column_content($content, $column_name, $term_id)
    {
        if ($column_name === 'order') {
            $order = get_term_meta($term_id, 'order', true);
            return $content . $order;
        }
        return $content;
    }
}
