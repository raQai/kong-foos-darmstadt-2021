<?php

defined('ABSPATH') or die('Nope!');

class BIWS_EventPartner
{
    private $posttype;

    private $taxonomy;

    private $slug;

    private $labels;

    public function __construct($posttype)
    {
        $this->posttype = $posttype;

        $this->taxonomy = 'event_partner';

        $this->slug = 'discipline';

        $this->labels = array(
            'name' => _x('Partners', 'taxonomy general name'),
            'singular_name' => _x('Partner', 'taxonomy singular name'),
            'search_items' =>  __('Search Partner'),
            'all_items' => __('All Partners'),
            'parent_item' => __('Parent Partner'),
            'parent_item_colon' => __('Parent Partner:'),
            'edit_item' => __('Edit Partner'),
            'update_item' => __('Update Partner'),
            'add_new_item' => __('Add New Partner'),
            'new_item_name' => __('New Partner Name'),
            'menu_name' => __('Partners'),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_event_partner');

        $loader->add_action('event_partner_add_form_fields', $this, 'biws_add_partner_fields', 10, 2);
        $loader->add_action('created_event_partner', $this, 'biws_save_partner_meta', 10, 2);
        $loader->add_action('event_partner_edit_form_fields', $this, 'biws_edit_partner_fields', 10, 2);
        $loader->add_action('edited_event_partner', $this, 'biws_update_partner_meta', 10, 2);
        $loader->add_filter('manage_edit-event_partner_columns', $this, 'biws_add_partner_columns');
        $loader->add_filter('manage_event_partner_custom_column', $this, 'biws_add_partner_column_content', 10, 3);
        $loader->add_action('admin_enqueue_scripts', $this, 'load_media');
        $loader->add_action('admin_footer', $this, 'add_script');
    }

    public function register_event_partner()
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

    public function load_media()
    {
        wp_enqueue_media();
    }

    public function biws_add_partner_fields($taxonomy)
    {
?>
        <div class="form-field term-priority-wrap">
            <label for="priority"><?php _e('Priority', 'biws-textdomain'); ?></label>
            <input name="priority" id="priority" type="number" value="0">
        </div>
        <div class="form-field term-group">
            <label for="partner-image-id"><?php _e('Image', 'biws-textdomain'); ?></label>
            <input type="hidden" id="partner-image-id" name="partner-image-id" class="custom_media_url" value="">
            <div id="partner-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary event_partner_media_button" id="event_partner_media_button" name="event_partner_media_button" value="<?php _e('Add Image', 'hero-theme'); ?>" />
                <input type="button" class="button button-secondary event_partner_media_remove" id="event_partner_media_remove" name="event_partner_media_remove" value="<?php _e('Remove Image', 'hero-theme'); ?>" />
            </p>
        </div>
    <?php
    }

    public function biws_save_partner_meta($term_id, $tt_id)
    {
        if (isset($_POST['partner-image-id']) && '' !== $_POST['partner-image-id']) {
            $image = $_POST['partner-image-id'];
            add_term_meta($term_id, 'partner-image-id', $image, true);
        }
        if (isset($_POST['priority']) && '' !== $_POST['priority']) {
            add_term_meta($term_id, 'priority', filter_var($_POST['priority'], FILTER_SANITIZE_NUMBER_INT));
        } else {
            add_term_meta($term_id, 'priority', 0);
        }
    }

    public function biws_edit_partner_fields($term, $taxonomy)
    { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="partner-image-id"><?php _e('Image', 'hero-theme'); ?></label>
            </th>
            <td>
                <?php $image_id = get_term_meta($term->term_id, 'partner-image-id', true); ?>
                <input type="hidden" id="partner-image-id" name="partner-image-id" value="<?php echo $image_id; ?>">
                <div id="partner-image-wrapper">
                    <?php if ($image_id) { ?>
                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary event_partner_media_button" id="event_partner_media_button" name="event_partner_media_button" value="<?php _e('Add Image', 'biws-textdomain'); ?>" />
                    <input type="button" class="button button-secondary event_partner_media_remove" id="event_partner_media_remove" name="event_partner_media_remove" value="<?php _e('Remove Image', 'biws-textdomain'); ?>" />
                </p>
            </td>
        </tr>
        <?php
        $priority = get_term_meta($term->term_id, 'priority', true);
        ?>
        <tr class="form-field term-priority-wrap">
            <th scope="row">
                <label for="priority"><?php _e('Priority', 'biws-textdomain'); ?></label>
            </th>
            <td>
                <input name="priority" id="priority" type="number" value="<?php echo $priority; ?>">
            </td>
        </tr>
    <?php
    }

    public function biws_update_partner_meta($term_id, $tt_id)
    {
        if (isset($_POST['partner-image-id']) && '' !== $_POST['partner-image-id']) {
            $image = $_POST['partner-image-id'];
            update_term_meta($term_id, 'partner-image-id', $image);
        } else {
            delete_term_meta($term_id, 'partner-image-id');
        }
        if (isset($_POST['priority']) && '' !== $_POST['priority']) {
            update_term_meta($term_id, 'priority', filter_var($_POST['priority'], FILTER_SANITIZE_NUMBER_INT));
        } else {
            update_term_meta($term_id, 'priority', 0);
        }
    }

    public function biws_add_partner_columns($columns)
    {
        $columns['priority'] = __('Priority', 'biws-textdomain');
        return $columns;
    }


    public function biws_add_partner_column_content($content, $column_name, $term_id)
    {
        if ($column_name === 'priority') {
            $priority = get_term_meta($term_id, 'priority', true);
            return $content . $priority;
        }
        return $content;
    }

    public function add_script()
    {
    ?>
        <script>
            jQuery(document).ready(function($) {
                function ct_media_upload(button_class) {
                    var _custom_media = true,
                        _orig_send_attachment = wp.media.editor.send.attachment;
                    $('body').on('click', button_class, function(e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        _custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (_custom_media) {
                                $('#partner-image-id').val(attachment.id);
                                $('#partner-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#partner-image-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return _orig_send_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }
                ct_media_upload('.event_partner_media_button.button');
                $('body').on('click', '.event_partner_media_remove', function() {
                    $('#partner-image-id').val('');
                    $('#partner-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                });
                $(document).ajaxComplete(function(event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            $('#partner-image-wrapper').html('');
                        }
                    }
                });
            });
        </script>
<?php
    }
}
