<?php

/**
 * Plugin Name: BIWS Seo
 * Description: Add simple SEO related settings to allow setting up meta tags
 * Author: Patrick Bogdan
 * Version: 1.0.0
 */

defined('ABSPATH') or die('Nope!');

function biws_seo_plugin_init()
{
    if (class_exists('MultiPostThumbnails')) {
        foreach (array('post', 'page', 'termine') as $post_type) {
            new MultiPostThumbnails(array(
                'label' => 'SEO Image',
                'id' => 'seo-image',
                'post_type' => $post_type,
            ));
        }
    }
}
add_action('plugins_loaded', 'biws_seo_plugin_init');

/**
 * Register settings page for SEO
 */
function biws_register_seo_settings()
{
    add_option('biws_seo_facebook');
    register_setting('biws_seo_settings', 'biws_seo_facebook');

    add_option('biws_seo_facebook_app_id');
    register_setting('biws_seo_settings', 'biws_seo_facebook_app_id');

    add_option('biws_seo_twitter');
    register_setting('biws_seo_settings', 'biws_seo_twitter');

    add_option('biws_seo_youtube');
    register_setting('biws_seo_settings', 'biws_seo_youtube');

    add_option('biws_seo_telegram');
    register_setting('biws_seo_settings', 'biws_seo_telegram');

    add_option('biws_seo_default_thumbnail_id');
    register_setting('biws_seo_settings', 'biws_seo_default_thumbnail_id');

    add_option('biws_seo_schema_org');
    register_setting('biws_seo_settings', 'biws_seo_schema_org');
}
add_action('admin_init', 'biws_register_seo_settings');

/**
 * Add SEO Settings menu and page
 */
function biws_seo_options_page()
{
    add_options_page('BIWS SEO', 'BIWS SEO', 'manage_options', 'biws-seo', 'biws_seo_options_page_html');
}
add_action('admin_menu', 'biws_seo_options_page');

/**
 * enqueue necessary scripts for image upload
 */
function biws_seo_enqueue_media_upload_script()
{
    if (empty($_GET['page']) || 'biws-seo' !== $_GET['page']) {
        return;
    }
?>
    <script>
        jQuery(document).ready(function($) {
            function ct_media_upload(selector) {
                let _orig_send_attachment = wp.media.editor.send.attachment;
                $('body').on('click', selector, function(e) {
                    let button_id = '#' + $(this).attr('id');
                    let send_attachment_bkp = wp.media.editor.send.attachment;
                    let button = $(button_id);
                    wp.media.editor.send.attachment = function(props, attachment) {
                        if (['jpeg', 'jpg', 'png', 'gif'].includes(attachment.subtype)) {
                            $('#biws_seo_default_thumbnail_id').val(attachment.id);
                            $('#biws_seo_default_thumbnail-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                            $('#biws_seo_default_thumbnail-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                        } else {
                            return _orig_send_attachment.apply(button_id, [props, attachment]);
                        }
                    }
                    wp.media.editor.open(button);
                    return false;
                });
            }
            ct_media_upload('#biws_seo_default_thumbnail_id_button');
            $('body').on('click', '#biws_seo_default_thumbnail_id_remove', function(event) {
                event.preventDefault();
                $('#biws_seo_default_thumbnail_id').val('');
                $('#biws_seo_default_thumbnail-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
            });
        });
    </script>
<?php
}
add_action('admin_footer', 'biws_seo_enqueue_media_upload_script');

function biws_seo_wp_enqueue_media()
{
    if (empty($_GET['page']) || 'biws-seo' !== $_GET['page']) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'biws_seo_wp_enqueue_media');

/**
 * Render settigs page
 */
function biws_seo_options_page_html()
{
?>
    <div class="wrap">
        <h1>Einstellungen &rsaquo; BIWS SEO Options</h1>

        <form method="post" action="options.php">
            <?php settings_fields('biws_seo_settings'); ?>
            <table class="form-table" role="presentation">
                <tbody>

                    <tr>
                        <th scope="row"><label for="biws_seo_facebook">Facebook</label></th>
                        <td><input name="biws_seo_facebook" type="text" id="biws_seo_facebook" class="regular-text ltr" value="<?php echo get_option('biws_seo_facebook'); ?>">
                            <p class="description" id="biws_seo_facebook-description">Username without @ used to link to your Facebook page (https://www.facebook.com/{username})</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_facebook_app_id">Facebook App ID</label></th>
                        <td><input name="biws_seo_facebook_app_id" type="text" id="biws_seo_facebook_app_id" class="regular-text ltr" value="<?php echo get_option('biws_seo_facebook_app_id'); ?>">
                            <p class="description" id="biws_seo_facebook_app_id-description">Facebook App ID (https://developers.facebook.com/docs/apps/register)</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_twitter">Twitter</label></th>
                        <td><input name="biws_seo_twitter" type="text" id="biws_seo_twitter" class="regular-text ltr" value="<?php echo get_option('biws_seo_twitter'); ?>">
                            <p class="description" id="biws_seo_twitter-description">Username without @ used to link to your Twitter page (https://www.twitter.com/{username})</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_youtube">YouTube</label></th>
                        <td><input name="biws_seo_youtube" type="text" id="biws_seo_youtube" class="regular-text ltr" value="<?php echo get_option('biws_seo_youtube'); ?>">
                            <p class="description" id="biws_seo_youtube-description">Channel ID or Username used to link to your YouTube page (https://www.youtube.com/channel/{id/username})</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_telegram">Telegram</label></th>
                        <td><input name="biws_seo_telegram" type="text" id="biws_seo_telegram" class="regular-text ltr" value="<?php echo get_option('biws_seo_telegram'); ?>">
                            <p class="description" id="biws_seo_telegram-description">Username without @ used to link to your Telegram channel (https://www.t.me/{username})</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_default_thumbnail_id">SEO Default Thumbnail</label></th>
                        <td>
                            <?php $image_id = get_option('biws_seo_default_thumbnail_id'); ?>
                            <input type="hidden" id="biws_seo_default_thumbnail_id" name="biws_seo_default_thumbnail_id" value="<?php echo $image_id; ?>">
                            <div id="biws_seo_default_thumbnail-wrapper">
                                <?php if ($image_id) { ?>
                                    <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                                <?php } ?>
                            </div>
                            <p>
                                <button type="button" class="button button-secondary biws_seo_default_thumbnail_id_button" id="biws_seo_default_thumbnail_id_button"><?php _e('Add Image', 'biws-textdomain'); ?></button>
                                <button type="button" class="button button-secondary" style="border:none;color:#d94f4f" id="biws_seo_default_thumbnail_id_remove"><?php _e('Delete Image', 'biws-textdomain'); ?></button>
                            </p>
                            <p class="description" id="biws_seo_default_thumbnail_id-description">Thumbnail to be used if no supported featured image is configured. Should be of at least 1080px width. Supports only jpeg, gif, png</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="biws_seo_schema_org">schema.org</label></th>
                        <td>
                            <textarea rows="16" name="biws_seo_schema_org" id="biws_seo_schema_org" class="regular-text ltr"><?php echo get_option('biws_seo_schema_org'); ?></textarea>
                            <p class="description" id="biws_seo_schema_org-description">Jason-LD structured data w/o script tag (also see https://schema.org/)</p>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function biws_add_seo_meta_tags()
{
    if (is_single() || is_page()) {

        $locale = get_locale();

        $post_id = get_queried_object_id();

        $url = get_permalink($post_id);
        $title = get_the_title($post_id);
        $site_name = get_bloginfo('name');

        $description = get_the_excerpt($post_id);
        $description = wp_trim_words(($description ? $description : get_post_field('post_content', $post_id)), 40);

        $image = false;
        $image_alt = "";
        if (class_exists('MultiPostThumbnails')) {
            if (MultiPostThumbnails::has_post_thumbnail(get_post_type(), 'seo-image')) {
                $img_id = MultiPostThumbnails::get_post_thumbnail_id(get_post_type(), 'seo-image', get_the_ID());
                if ($img_id) {
                    $image = wp_get_attachment_image_src($img_id, 'large');
                    $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                }
            }
        }
        // if seo image not present or extension not supported: get post thumbnail
        if (!$image || !in_array(strtolower(pathinfo($image[0], PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif'))) {
            $img_id = get_post_thumbnail_id(get_the_ID());
            if ($img_id) {
                $image = wp_get_attachment_image_src($img_id, 'large');
                $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            }
        }
        // if thumbnail not present or extension not supported: get default seo image
        if (!$image || !in_array(strtolower(pathinfo($image[0], PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif'))) {
            $img_id = get_option('biws_seo_default_thumbnail_id');
            if ($img_id) {
                $image = wp_get_attachment_image_src($img_id, 'large');
                $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            }
        }

        $facebook = get_option('biws_seo_facebook');
        $facebook_app_id = get_option('biws_seo_facebook_app_id');
        $twitter = get_option('biws_seo_twitter');

        echo '<meta name="description" content="' . esc_attr($description) . '" />';

        echo '<meta property="og:locale" content="' . esc_attr($locale) . '" />';
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />';
        echo '<meta property="og:type" content="article" />';
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />';
        echo '<meta property="og:url" content="' . esc_url($url) . '" />';
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />';
        if ($facebook_app_id) {
            echo '<meta property="fb:app_id" content="' . esc_attr($facebook_app_id) . '" />';
        }
        if ($facebook) {
            echo '<meta property="article:publisher" content="https://www.facebook.com/' . $facebook . '/" />';
        }
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image[0]) . '" />';
            echo '<meta property="og:image:width" content="' . $image[1] . '" />';
            echo '<meta property="og:image:height" content="' . $image[2] . '" />';
            if ($image_alt) {
                echo '<meta property="og:image:alt" content="' . $image_alt . '" />';
            }
        }

        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image" />';
        if ($twitter) {
            echo '<meta name="twitter:site" content="@' . $twitter . '" />';
            echo '<meta name="twitter:creator" content="@' . $twitter . '" />';
        }
    }
}
add_action('wp_head', 'biws_add_seo_meta_tags');

function biws_add_seo_schema_org()
{
    $schema = get_option('biws_seo_schema_org');
    if ($schema) {
        // $schema = esc_attr($schema);
        echo '<script type="application/ld+json">';
        if (substr($schema, 0, 1) !== '{') {
            echo '{';
        }
        echo $schema;
        if (substr($schema, -1) !== '}') {
            echo '}';
        }
        echo '</script>';
    }
}
add_action('wp_footer', 'biws_add_seo_schema_org');

/**
 * TODO
 * add seo related schema.org script
 * convert to class
 * add static getter for seo image of post_id
 */
