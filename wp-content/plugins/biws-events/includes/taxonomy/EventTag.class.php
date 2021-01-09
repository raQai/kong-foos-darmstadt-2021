<?php

defined('ABSPATH') or die('Nope!');

class BIWS_EventTag
{
    private $posttype;

    private $taxonomy;

    private $slug;

    private $labels;

    public function __construct($posttype)
    {
        $this->posttype = $posttype;

        $this->taxonomy = 'event_tag';

        $this->slug = 'tag';

        $this->labels = array(
            'name' => _x('Tags', 'taxonomy general name'),
            'singular_name' => _x('Tag', 'taxonomy singular name'),
            'search_items' =>  __('Search Tag'),
            'all_items' => __('All Tags'),
            'parent_item' => __('Parent Tag'),
            'parent_item_colon' => __('Parent Tag:'),
            'edit_item' => __('Edit Tag'),
            'update_item' => __('Update Tag'),
            'add_new_item' => __('Add New Tag'),
            'new_item_name' => __('New Tag Name'),
            'menu_name' => __('Tags'),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_event_tag');

        $loader->add_action('event_tag_add_form_fields', $this, 'biws_add_tag_fields');
        $loader->add_action('created_event_tag', $this, 'biws_save_tag_meta', 10, 2);
        $loader->add_action('event_tag_edit_form_fields', $this, 'biws_edit_tag_fields', 10, 2);
        $loader->add_action('edited_event_tag', $this, 'biws_update_tag_meta', 10, 2);
        $loader->add_filter('manage_edit-event_tag_columns', $this, 'biws_add_tag_columns');
        $loader->add_filter('manage_event_tag_custom_column', $this, 'biws_add_tag_column_content', 10, 3);
    }

    public function register_event_tag()
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

    public function biws_add_tag_fields($taxonomy)
    {
?>
        <div class="form-field term-color-wrap">
            <label for="tag-color"><?php _e('Color', 'biws-textdomain'); ?></label>
            <input name="tag-color" id="tag-color" type="color" style="width:4rem;height:3rem" value="" aria-required="true">
        </div>
    <?php
    }


    public function biws_save_tag_meta($term_id, $tt_id)
    {
        if (isset($_POST['tag-color']) && '' !== $_POST['tag-color']) {
            add_term_meta($term_id, 'tag-color', sanitize_title($_POST['tag-color']));
        }
    }

    public function biws_edit_tag_fields($term, $taxonomy)
    {
        $tag_color = get_term_meta($term->term_id, 'tag-color', true);
    ?>
        <tr class="form-field form-required term-color-wrap">
            <th scope="row">
                <label for="tag-color"><?php _e('Color', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="tag-color" id="tag-color" type="color" style="width:4rem;height:3rem" value="#<?php echo $tag_color; ?>" aria-required="true">
            </td>
        </tr>
<?php
    }

    public function biws_update_tag_meta($term_id, $tt_id)
    {
        if (isset($_POST['tag-color']) && '' !== $_POST['tag-color']) {
            update_term_meta($term_id, 'tag-color', sanitize_title($_POST['tag-color']));
        }
    }

    public function biws_add_tag_columns($columns)
    {
        $columns['tag_color'] = __('Color', 'biws-textdomain');
        return $columns;
    }


    public function biws_add_tag_column_content($content, $column_name, $term_id)
    {
        if ($column_name === 'tag_color') {
            $meta = get_term_meta($term_id, 'tag-color', true);
            $color = esc_attr($meta);
            return $content . '<code style="background-color:#' . $color . ';color:#fff;padding:.3rem.8rem;display:inline-block;border-radius:6px;">#' . $color . '</code>';
        }
        return $content;
    }
}
