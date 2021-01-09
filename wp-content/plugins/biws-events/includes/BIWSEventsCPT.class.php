<?php

// TODO add documentation
defined('ABSPATH') or die('Nope!');

class BIWS_EventsCPT
{
    private $slug;

    private $labels;

    private $args;

    public function __construct($slug)
    {
        $this->slug = $slug;

        $this->labels = array(
            'name' => __('Events', 'biws-textdomain'),
            'singular_name' => __('Event', 'biws-textdomain'),
        );
        $this->args = array(
            'label' => __('Events', 'biws-textdomain'),
            'description' => __('Turniere und Events', 'biws-textdomain'),
            'labels' => $this->labels,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
            'hierarchical' => true,
            'has_archive' => true,
            'capability_type' => 'page',
            'menu_icon' => 'dashicons-calendar-alt',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 5,
            'exclude_from_search' => true,
            'rewrite' => array('slug' => $this->slug),
        );
    }

    public function init($loader)
    {
        $loader->add_action('init', $this, 'register_biws_events');

        $loader->add_action('save_post', $this, 'biws_save_event_info');
        $loader->add_filter('manage_edit-' . $this->slug . '_columns', $this, 'biws_custom_columns_head');
        $loader->add_action('manage_' . $this->slug . '_posts_custom_column', $this, 'biws_custom_columns_content', 10, 2);
        $loader->add_action('add_meta_boxes', $this, 'biws_add_event_info_metabox');

        add_shortcode('biws_event_search', array($this, 'biws_event_search'));

        $loader->add_action('wp_ajax_biws_event_search', $this, 'biws_event_search_callback');
        $loader->add_action('wp_ajax_nopriv_biws_event_search', $this, 'biws_event_search_callback');

        $loader->add_filter('single_template', $this, 'biws_load_single_template', 20);
        $loader->add_action('wp_footer', $this, 'biws_event_schema_org_single');

        $loader->add_action('admin_action_biws_duplicate_post_as_draft', $this, 'biws_duplicate_post_as_draft');
        $loader->add_filter('page_row_actions', $this, 'biws_duplicate_post_link', 10, 2);
    }

    public function register_biws_events()
    {
        register_post_type($this->slug, $this->args);
    }

    public function biws_add_event_info_metabox()
    {
        add_meta_box(
            'biws-event-info-metabox',
            __('Event Info', 'biws-textdomain'),
            array($this, 'biws_render_event_info_metabox'),
            $this->slug,
            'side',
            'core'
        );
    }

    public function biws_render_event_info_metabox($post)
    {
        wp_nonce_field(basename(__FILE__), 'biws-event-info-nonce');

        $event_highlight = get_post_meta($post->ID, 'event-highlight', true);

        $event_organizer = get_post_meta($post->ID, 'event-organizer', true);
        $event_organizer_url = get_post_meta($post->ID, 'event-organizer-url', true);

        $event_start_date = get_post_meta($post->ID, 'event-start-date', true);
        $event_end_date = get_post_meta($post->ID, 'event-end-date', true);

        $event_entry_fee = get_post_meta($post->ID, 'event-entry-fee', true);
        $event_entry_fee_label = get_post_meta($post->ID, 'event-entry-fee-label', true);
        $event_entry_fee_description = get_post_meta($post->ID, 'event-entry-fee-description', true);

        $event_start_date = !empty($event_start_date) ? $event_start_date : time();
        $event_end_date = !empty($event_end_date) ? $event_end_date : $event_start_date;
?>
        <div style="margin-bottom:1rem;">
            <input id="biws-event-highlight" type="checkbox" name="biws-event-highlight" value="1" <?php echo !empty($event_highlight) ? ' checked' : ''; ?> />
            <label for="biws-event-highlight"><?php _e('Highlight', 'biws-textdomain'); ?></label>
        </div>

        <div style="margin-bottom:1rem;">
            <label for="biws-event-organizer"><?php _e('Organizer', 'biws-textdomain'); ?></label>
            <input class="widefat" id="biws-event-organizer" type="text" name="biws-event-organizer" value="<?php echo $event_organizer; ?>" placeholder="<?php _e('Label', 'biws-textdomain'); ?>" />
            <input class="widefat" id="biws-event-organizer-url" type="url" name="biws-event-organizer-url" value="<?php echo $event_organizer_url; ?>" placeholder="https://www.*" />
        </div>

        <div style="margin-bottom:1rem;">
            <label for="biws-event-start-date"><?php _e('Event Start Date', 'biws-textdomain'); ?></label>
            <input class="widefat" id="biws-event-start-date" type="date" name="biws-event-start-date" value="<?php echo date('Y-m-d', $event_start_date); ?>" />

            <label for="biws-event-end-date"><?php _e('Event End Date', 'biws-textdomain'); ?></label>
            <input class="widefat" id="biws-event-end-date" type="date" name="biws-event-end-date" value="<?php echo date('Y-m-d', $event_end_date); ?>" />
        </div>

        <!-- handle visibility of n+1 with js if time n time and label are not empty -->
        <div style="margin-bottom:1rem;">
            <?php
            for ($i = 1; $i <= 4; $i++) {
                $field = 'biws-event-time-' . $i;
                $event_time_time_i = get_post_meta($post->ID, 'event-time-' . $i . '-time', true);
                $event_time_label_i = get_post_meta($post->ID, 'event-time-' . $i . '-label', true);
            ?>
                <label for="<?php echo $field; ?>"><?php echo __('Event Time', 'biws-textdomain') . ' ' . $i; ?></label>
                <div id="<?php echo $field; ?>" style="display: flex;">
                    <input class="widefat" id="<?php echo $field; ?>-time" type="time" name="<?php echo $field; ?>-time" step="1800" value="<?php echo $event_time_time_i; ?>" />
                    <input class="widefat" id="<?php echo $field; ?>-label" type="text" name="<?php echo $field; ?>-label" value="<?php echo $event_time_label_i; ?>" placeholder="<?php _e('Label', 'biws-textdomain'); ?>" />
                </div>
            <?php
            }
            ?>
        </div>
        <div style="margin-bottom:1rem;">
            <label for="biws-event-entry-fee"><?php _e('Entry Fee', 'biws-textdomain'); ?></label>
            <input class="widefat" id="biws-event-entry-fee" type="number" name="biws-event-entry-fee" value="<?php echo $event_entry_fee; ?>" />
            <input class="widefat" id="biws-event-entry-fee-label" type="text" name="biws-event-entry-fee-label" value="<?php echo $event_entry_fee_label; ?>" placeholder="<?php _e('Label', 'biws-textdomain'); ?>" />
            <input class="widefat" id="biws-event-entry-fee-description" type="text" name="biws-event-entry-fee-description" value="<?php echo $event_entry_fee_description; ?>" placeholder="e.g. free for kids" />
        </div>
        <div style="margin-bottom:1rem;">
            <?php
            for ($i = 1; $i <= 4; $i++) {
                $field = 'biws-event-link-' . $i;
                $event_link_url_i = get_post_meta($post->ID, 'event-link-' . $i . '-url', true);
                $event_link_label_i = get_post_meta($post->ID, 'event-link-' . $i . '-label', true);
            ?>
                <label for="<?php echo $field; ?>"><?php _e('Event Link', 'biws-textdomain');
                                                    echo ' ' . $i; ?></label>
                <div id="<?php echo $field; ?>">
                    <input class="widefat" id="<?php echo $field; ?>-url" type="text" name="<?php echo $field; ?>-url" value="<?php echo $event_link_url_i; ?>" placeholder="https://www.*" />
                    <input class="widefat" id="<?php echo $field; ?>-label" type="text" name="<?php echo $field; ?>-label" value="<?php echo $event_link_label_i; ?>" placeholder="<?php _e('Label', 'biws-textdomain'); ?>" />
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    }

    public function biws_save_event_info($post_id)
    {
        if (!isset($_POST['post_type']) || $this->slug != $_POST['post_type']) {
            return;
        }

        $is_autosave = wp_is_post_autosave($post_id);
        $is_revision = wp_is_post_revision($post_id);
        $is_valid_nonce = (isset($_POST['biws-event-info-nonce']) && (wp_verify_nonce($_POST['biws-event-info-nonce'], basename(__FILE__)))) ? true : false;

        if ($is_autosave || $is_revision || !$is_valid_nonce) {
            return;
        }

        if (isset($_POST['biws-event-highlight'])) {
            update_post_meta($post_id, 'event-highlight', filter_var($_POST['biws-event-highlight'], FILTER_SANITIZE_NUMBER_INT));
        } else {
            delete_post_meta($post_id, 'event-highlight');
        }

        if (isset($_POST['biws-event-organizer'])) {
            update_post_meta($post_id, 'event-organizer', filter_var($_POST['biws-event-organizer'], FILTER_SANITIZE_STRING));
            if (isset($_POST['biws-event-organizer-url'])) {
                update_post_meta($post_id, 'event-organizer-url', filter_var($_POST['biws-event-organizer-url'], FILTER_SANITIZE_URL));
            }
        } else {
            delete_post_meta($post_id, 'event-organizer');
            delete_post_meta($post_id, 'event-organizer-url');
        }

        if (isset($_POST['biws-event-start-date'])) {
            $start_time = strtotime($_POST['biws-event-start-date']);
            update_post_meta($post_id, 'event-start-date', $start_time);
            if (isset($_POST['biws-event-end-date'])) {
                $end_time = strtotime($_POST['biws-event-end-date']);
                update_post_meta($post_id, 'event-end-date', $end_time > $start_time ? $end_time : $start_time);
            } else {
                update_post_meta($post_id, 'event-end-date', $start_time);
            }
        } else {
            update_post_meta($post_id, 'event-start-date', time());
            update_post_meta($post_id, 'event-end-date', time());
        }

        for ($i = 1; $i <= 4; $i++) {
            if (isset($_POST['biws-event-time-' . $i . '-time']) && isset($_POST['biws-event-time-' . $i . '-label']) && !empty($_POST['biws-event-time-' . $i . '-label'])) {
                update_post_meta($post_id, 'event-time-' . $i . '-time', filter_var($_POST['biws-event-time-' . $i . '-time'], FILTER_SANITIZE_STRING));
                update_post_meta($post_id, 'event-time-' . $i . '-label', filter_var($_POST['biws-event-time-' . $i . '-label'], FILTER_SANITIZE_STRING));
            } else {
                delete_post_meta($post_id, 'event-time-' . $i . '-time');
                delete_post_meta($post_id, 'event-time-' . $i . '-label');
            }
        }

        if (isset($_POST['biws-event-entry-fee']) && isset($_POST['biws-event-entry-fee-label'])) {
            update_post_meta($post_id, 'event-entry-fee', filter_var($_POST['biws-event-entry-fee'], FILTER_SANITIZE_NUMBER_INT));
            update_post_meta($post_id, 'event-entry-fee-label', filter_var($_POST['biws-event-entry-fee-label'], FILTER_SANITIZE_STRING));
            if (isset($_POST['biws-event-entry-fee-description'])) {
                update_post_meta($post_id, 'event-entry-fee-description', filter_var($_POST['biws-event-entry-fee-description'], FILTER_SANITIZE_STRING));
            }
        } else {
            delete_post_meta($post_id, 'event-entry-fee');
            delete_post_meta($post_id, 'event-entry-fee-label');
            delete_post_meta($post_id, 'event-entry-fee-description');
        }

        for ($i = 1; $i <= 4; $i++) {
            if (isset($_POST['biws-event-link-' . $i . '-label']) && isset($_POST['biws-event-link-' . $i . '-url']) && !empty($_POST['biws-event-link-' . $i . '-url'])) {
                update_post_meta($post_id, 'event-link-' . $i . '-url', filter_var($_POST['biws-event-link-' . $i . '-url'], FILTER_SANITIZE_URL));
                update_post_meta($post_id, 'event-link-' . $i . '-label', filter_var($_POST['biws-event-link-' . $i . '-label'], FILTER_SANITIZE_STRING));
            } else {
                delete_post_meta($post_id, 'event-link-' . $i . '-url');
                delete_post_meta($post_id, 'event-link-' . $i . '-label');
            }
        }
    }

    public function biws_custom_columns_head($defaults)
    {
        unset($defaults['date']);

        $defaults['event_start_date'] = __('Start Date', 'biws-textdomain');
        $defaults['event_end_date'] = __('End Date', 'biws-textdomain');
        $defaults['event_time'] = __('Time Table', 'biws-textdomain');

        return $defaults;
    }

    public function biws_custom_columns_content($column_name, $post_id)
    {
        if ('event_start_date' == $column_name) {
            $start_date = get_post_meta($post_id, 'event-start-date', true);
            echo date_i18n('d. F Y', $start_date);
        }

        if ('event_end_date' == $column_name) {
            $end_date = get_post_meta($post_id, 'event-end-date', true);
            echo date_i18n('d. F Y', $end_date);
        }

        if ('event_time' == $column_name) {
            for ($i = 1; $i <= 4; $i++) {
                $label = get_post_meta($post_id, 'event-time-' . $i . '-label', true);
                if (!empty($label)) {
                    $time = get_post_meta($post_id, 'event-time-' . $i . '-time', true);
                    echo $time . '&nbsp;&nbsp;' . $label . '</br>';
                }
            }
        }
    }

    /**
     * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
     * credits: https://www.hostinger.com/tutorials/how-to-duplicate-wordpress-page-or-post
     */
    function biws_duplicate_post_as_draft()
    {
        global $wpdb;
        if (!(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'biws_duplicate_post_as_draft' == $_REQUEST['action']))) {
            wp_die('No post to duplicate has been supplied!');
        }

        /**
         * Nonce verification
         */
        if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__)))
            return;

        /**
         * get the original post id
         */
        $post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
        /**
         * and all the original post data then
         */
        $post = get_post($post_id);

        /**
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        /**
         * if post data exists, create the post duplicate
         */
        if (isset($post) && $post != null) {

            /**
             * new post data array
             */
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'draft',
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );

            /**
             * insert the post by wp_insert_post() function
             */
            $new_post_id = wp_insert_post($args);

            /**
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            /**
             * duplicate all post meta just in two SQL queries
             */
            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug') continue;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }


            /**
             * finally, redirect to the edit post screen for the new draft
             */
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    /**
     * Add the duplicate link to action list for post_row_actions
     */
    function biws_duplicate_post_link($actions, $post)
    {
        if (current_user_can('edit_posts') && $post->post_type === $this->slug) {
            $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=biws_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }

    private function biws_event_search_enqueues()
    {
        $plugin_dir = plugin_dir_url(__DIR__);
        wp_enqueue_script('biws-event-search', $plugin_dir . 'public/js/biws-events-public.min.js', array(), '1.0.0', true);
        wp_localize_script('biws-event-search', 'ajax_url', admin_url('admin-ajax.php'));
        $this->biws_event_styles();
    }

    private function biws_event_styles()
    {
        $plugin_dir = plugin_dir_url(__DIR__);
        wp_enqueue_style('biws-event-list-style', $plugin_dir . 'public/css/biws-events-public.min.css');
    }

    public function biws_event_search()
    {
        add_action('wp_footer', array($this, 'biws_event_schema_org_list'));
        $this->biws_event_search_enqueues();

        ob_start();
    ?>
        <div id="biws-events" class="alignwide"></div>
<?php
        return ob_get_clean();
    }

    public function biws_event_search_callback()
    {
        header('Content-Type: application/json');
        $result = array();

        $args = $this->biws_event_query_args();

        // filters
        if (isset($_POST['highlight'])) {
            $args['meta_query'][] = array(
                'key' => 'event-highlight',
                'value' => 1,
                'compare' => '=',
            );
        }

        $biws_event_query = new WP_Query($args);

        while ($biws_event_query->have_posts()) {
            $biws_event_query->the_post();

            $id = get_the_id();

            $start_date = get_post_meta($id, 'event-start-date', true);
            $end_date = get_post_meta($id, 'event-end-date', true);

            $event_types = wp_get_object_terms($id, 'event_type');
            $types = array();
            foreach ($event_types as $term) {
                $types[] = $term->name;
            }

            $event_venues = wp_get_object_terms($id, 'event_venue');
            $venues = array();
            foreach ($event_venues as $term) {
                $venue_array = array(
                    'name' => $term->name,
                );

                $street = get_term_meta($term->term_id, 'venue-street');
                if ($street) {
                    $venue_array['street'] = $street;
                }
                $zip = get_term_meta($term->term_id, 'venue-zipcode');
                if ($zip) {
                    $venue_array['zip'] = $zip;
                }
                $loc = get_term_meta($term->term_id, 'venue-location');
                if ($loc) {
                    $venue_array['location'] = $loc;
                }

                $venues[] = $venue_array;

                unset($venue_array);
                unset($street);
                unset($zip);
                unset($loc);
            }

            $event_disciplines = wp_get_object_terms($id, 'event_discipline');
            $disciplines = array();
            foreach ($event_disciplines as $term) {
                $disciplines[] = $term->slug;
            }

            $event_tags = wp_get_object_terms($id, 'event_tag');
            $tags = array();
            foreach ($event_tags as $term) {
                $tags[] = array(
                    'name' => $term->name,
                    'color' => get_term_meta($term->term_id, 'tag-color', true),
                );
            }

            $result_array = array(
                'id' => $id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($id),
                'highlight' => get_post_meta($id, 'event-highlight', true),
                'start' => array(
                    'day' => date_i18n("d", $start_date),
                    'month' => date_i18n("M", $start_date),
                ),
                'end' => array(
                    'day' => date_i18n("d", $end_date),
                    'month' => date_i18n("M", $end_date),
                ),
            );
            if ($types) {
                $result_array['category'] = $types[0];
            }
            if ($venues) {
                $result_array['venue'] = $venues[0];
            }
            if ($disciplines) {
                $result_array['disciplines'] = $disciplines;
            }
            if ($tags) {
                $result_array['tags'] = $tags;
            }

            unset($event_types);
            unset($types);
            unset($event_venues);
            unset($venues);
            unset($event_disciplines);
            unset($disciplines);
            unset($event_tags);
            unset($tags);
            unset($id);
            $result[] = $result_array;
        }

        echo json_encode($result);

        unset($today_date);
        unset($today_tc);
        unset($biws_event_query);
        unset($result);
        wp_die();
    }

    function biws_load_single_template($template)
    {
        global $post;

        $plugin_dir = plugin_dir_path(__DIR__);
        if ($post->post_type === $this->slug) {
            if (file_exists($plugin_dir . 'templates/single-termine.php')) {
                $this->biws_event_styles();
                add_filter('body_class', function ($classes) {
                    return array_merge($classes, array('biws-event-single'));
                });
                return $plugin_dir . 'templates/single-termine.php';
            }
        }
        return $template;
    }

    function biws_event_query_args()
    {
        $today_date = new DateTime('today');
        $today_tc = strtotime($today_date->format('Y-m-d'));
        return array(
            'post_type' => $this->slug,
            'posts_per_page' => -1,
            'meta_key'   => 'event-start-date',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'event-end-date',
                    'value' => $today_tc,
                    'compare' => '>=',
                ),
            )
        );
    }

    function biws_event_schema_org_list()
    {
        if (!class_exists('BIWS_EventsSchemaOrg')) {
            return;
        }
        $schema = new BIWS_EventsSchemaOrg();
        $biws_event_query = new WP_Query($this->biws_event_query_args());

        while ($biws_event_query->have_posts()) {
            $biws_event_query->the_post();
            $schema->addGraphElement($this->build_schema_org_script_part(get_the_id()));
        }

        echo $schema->serialize();

        unset($biws_event_query);
        unset($schema);
    }

    function biws_event_schema_org_single()
    {
        if (!class_exists('BIWS_EventsSchemaOrg')) {
            return;
        }
        if (is_single() && $this->slug === get_post_type()) {
            $schema = new BIWS_EventsSchemaOrg();
            $schema->setSchema($this->build_schema_org_script_part(get_the_id()));
            echo $schema->serialize();
            unset($schema);
        }
    }

    private function build_schema_org_script_part($id)
    {
        $event = array();
        $event['@type'] = "SportsEvent";
        $event['name'] = get_the_title($id);
        $startDate = get_post_meta($id, 'event-start-date', true);
        if ($startDate) {
            $event['startDate'] = date("Y-m-d", $startDate);
        }
        $endDate = get_post_meta($id, 'event-end-date', true);
        if ($endDate) {
            $event['endDate'] = date("Y-m-d", $endDate);
        }
        $event['url'] = get_permalink($id);
        $event['sport'] = 'https://de.wikipedia.org/wiki/Tischfu%C3%9Fball';

        $excerpt = get_the_excerpt($id);
        $description = '';
        if ($excerpt) {
            $description = $excerpt;
        } else {
            $description = "Tischfußball ";

            $event_types = wp_get_object_terms($id, 'event_type');
            $description .= $event_types ? $event_types[0]->name : "Turnier";

            $event_disciplines = wp_get_object_terms($id, 'event_discipline');
            $event_discipline_names = array();
            if ($event_disciplines) {
                $description .= " ";
                foreach ($event_disciplines as $discipline) {
                    $event_discipline_names[] = $discipline->name;
                }
                $description .= " - ";
                $description .= join(", ", $event_discipline_names);
            }
        }
        if ($description) {
            $event['description'] = $description;
        }

        $image = false;
        if (class_exists('MultiPostThumbnails')) {
            if (MultiPostThumbnails::has_post_thumbnail(get_post_type($id), 'seo-image')) {
                $img_id = MultiPostThumbnails::get_post_thumbnail_id(get_post_type($id), 'seo-image', $id);
                if ($img_id) {
                    $image = wp_get_attachment_image_src($img_id, 'large');
                }
            }
        }
        // if seo image not present or extension not supported: get post thumbnail
        if (!$image || !in_array(strtolower(pathinfo($image[0], PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif'))) {
            $img_id = get_post_thumbnail_id($id);
            if ($img_id) {
                $image = wp_get_attachment_image_src($img_id, 'large');
            }
        }
        // if thumbnail not present or extension not supported: get default seo image
        if (!$image || !in_array(strtolower(pathinfo($image[0], PATHINFO_EXTENSION)), array('jpeg', 'jpg', 'png', 'gif'))) {
            $img_id = get_option('biws_seo_default_thumbnail_id');
            if ($img_id) {
                $image = wp_get_attachment_image_src($img_id, 'large');
            }
        }
        if ($image) {
            $event['image'] = $image[0];
        }

        $location = array();
        $location['@type'] = 'Place';

        $address = array();
        $address['@type'] = 'PostalAddress';

        $event_venues = wp_get_object_terms($id, 'event_venue');
        if ($event_venues) {
            $term_id = $event_venues[0]->term_id;
            $location['name'] = $event_venues[0]->name;

            $address['streetAddress'] = get_term_meta($term_id, 'venue-street', true);
            $address['addressLocality'] = get_term_meta($term_id, 'venue-location', true);
            $address['postalCode'] = get_term_meta($term_id, 'venue-zipcode', true);

            $location['address'] = $address;
            unset($term_id);
        }

        $event['location'] = $location;


        return $event;
    }
}
