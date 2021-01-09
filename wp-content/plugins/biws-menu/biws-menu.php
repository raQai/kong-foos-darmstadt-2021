<?php

/**
 * Plugin Name: BIWS Menu
 * Description: Improve navigation menu items with images and description
 * Author: Patrick Bogdan
 * Version: 1.0.0
 */

defined('ABSPATH') or die('Nope!');

class BIWS_Menu_Plugin
{

	private $used_attachments = array();

	private $additionalDisplayableImageExtensions = array('ico');

	private $processed = array();

	public function __construct()
	{
		// Actions.
		add_action('init', array($this, 'biws_menu_init'));
		add_action('save_post_nav_menu_item', array($this, 'biws_menu_save_post_action'), 10, 3);
		add_action('admin_head-nav-menus.php', array($this, 'biws_menu_admin_head_nav_menus_action'));
		add_action('admin_action_delete-menu-item-image', array($this, 'biws_menu_delete_menu_item_image_action'));
		add_action('wp_ajax_set-menu-item-thumbnail', array($this, 'wp_ajax_set_menu_item_thumbnail'));

		add_action('admin_init', array($this, 'admin_init'), 99);

		// Filters.
		add_filter('wp_setup_nav_menu_item', array($this, 'biws_menu_wp_setup_nav_menu_item'));
		add_filter('nav_menu_link_attributes', array($this, 'biws_menu_nav_menu_link_attributes_filter'), 10, 4);
		add_filter('manage_nav-menus_columns', array($this, 'biws_menu_nav_menu_manage_columns'), 11);
		add_filter('nav_menu_item_title', array($this, 'biws_menu_nav_menu_item_title_filter'), 10, 4);
		add_filter('the_title', array($this, 'biws_menu_nav_menu_item_title_filter'), 10, 4);

		// Add support for additional image types.
		add_filter('file_is_displayable_image', array($this, 'file_is_displayable_image'), 10, 2);
		add_filter('wp_get_attachment_image_attributes', array($this, 'wp_get_attachment_image_attributes'), 99, 3);
	}

	public function admin_init()
	{
		add_action('wp_nav_menu_item_custom_fields', array($this, 'biws_menu_custom_fields'), 10, 4);
	}

	public function file_is_displayable_image($result, $path)
	{
		if ($result) {
			return true;
		}
		$fileExtension = pathinfo($path, PATHINFO_EXTENSION);

		return in_array($fileExtension, $this->additionalDisplayableImageExtensions);
	}

	public function biws_menu_init()
	{
		add_post_type_support('nav_menu_item', array('thumbnail'));
	}

	/**
	 * Adding images as screen options.
	 *
	 * If not checked screen option 'image', uploading form not showed.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function biws_menu_nav_menu_manage_columns($columns)
	{
		return $columns + array('image' => __('Image', 'menu-image'));
	}

	/**
	 * Saving post action.
	 *
	 * Saving uploaded images and attach/detach to image post type.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function biws_menu_save_post_action($post_id, $post)
	{
		$setting_name = 'menu_item_image_title_position';
		if (isset($_POST[$setting_name][$post_id]) && !empty($_POST[$setting_name][$post_id])) {
			if ($post->{"_$setting_name"} != $_POST[$setting_name][$post_id]) {
				update_post_meta($post_id, "_$setting_name", esc_sql($_POST[$setting_name][$post_id]));
			}
		}
	}

	/*
	public function biws_menu_edit_nav_menu_walker_filter() {
		return 'BIWS_Menu_Walker_Nav_Menu_Edit';
	}
	*/

	public function biws_menu_wp_setup_nav_menu_item($item)
	{
		if (!isset($item->thumbnail_id)) {
			$item->thumbnail_id = get_post_thumbnail_id($item->ID);
		}
		if (!isset($item->title_position)) {
			$item->title_position = get_post_meta($item->ID, '_menu_item_image_title_position', true);
		}

		return $item;
	}

	public function biws_menu_nav_menu_link_attributes_filter($atts, $item, $args, $depth = null)
	{

		if ('' !== $item->thumbnail_id) {
			$this->setProcessed($item->ID);
			$position = $item->title_position ? $item->title_position : apply_filters('biws_menu_default_title_position', 'after');
			$class    = !empty($atts['class']) ? $atts['class'] : '';
			$class    .= " biws-menu-title-{$position}";
			if ($item->thumbnail_id) {
				$class .= ' biws-menu-image';
			}
			$atts['class'] = trim($class);
		}

		return $atts;
	}

	/**
	 * Replacement default menu item output.
	 *
	 * @param string $title Default item output
	 * @param object $item  Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param object $args
	 *
	 * @return string
	 */
	public function biws_menu_nav_menu_item_title_filter($title, $item = null, $depth = null, $args = null)
	{

		if (strpos($title, 'menu-image') > 0 || !is_nav_menu_item($item) || !isset($item)) {
			return $title;
		}

		if (is_numeric($item) && $item < 0) {
			return $title;
		}

		if (is_numeric($item) && $item > 0) {
			$item = wp_setup_nav_menu_item(get_post($item));
		}

		// Process only if there is an menu image associated with the menu item.
		if ('' !== $item->thumbnail_id) {

			$position   = $item->title_position ? $item->title_position : apply_filters('biws_menu_default_title_position', 'after');
			$class      = "biws-menu-title-{$position}";
			$this->setUsedAttachments("thumbnail", $item->thumbnail_id);
			$image = '';
			if ($item->thumbnail_id) {
				$image = wp_get_attachment_image($item->thumbnail_id, "thumbnail", false, "class=biws-menu-image {$class}");
			}
			$image = apply_filters('menu_image_img_html', $image);
			$class .= ' biws-menu-title';

			$item_args = array($image, $class, $title);

		    $title = vsprintf('%s<span class="%s">%s</span>', $item_args);
		}

		return $title;
	}

	/**
	 * Loading media-editor script ot nav-menus page.
	 *
	 * @since 2.0
	 */
	public function biws_menu_admin_head_nav_menus_action()
	{
		wp_enqueue_script('biws-menu-admin', plugins_url('/includes/js/biws-menu-admin.js', __FILE__), array('jquery'), '2.9.5');
		wp_localize_script(
			'biws-menu-admin',
			'biwsMenu',
			array(
				'l10n'     => array(
					'uploaderTitle'      => __('Chose menu image', 'menu-image'),
					'uploaderButtonText' => __('Select', 'menu-image'),
				),
				'settings' => array(
					'nonce' => wp_create_nonce('update-menu-item'),
				),
			)
		);
		wp_enqueue_media();
		wp_enqueue_style('editor-buttons');
	}

	/**
	 * When menu item removed remove menu image metadata.
	 */
	public function biws_menu_delete_menu_item_image_action()
	{

		$menu_item_id = (int) $_REQUEST['menu-item'];

		check_admin_referer('delete-menu_item_image_' . $menu_item_id);

		if (is_nav_menu_item($menu_item_id) && has_post_thumbnail($menu_item_id)) {
			delete_post_thumbnail($menu_item_id);
			delete_post_meta($menu_item_id, '_menu_item_image_title_position');
		}
	}

	/**
	 * Output HTML for the menu item images.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id The post ID or object associated with the thumbnail, defaults to global $post.
	 *
	 * @return string html
	 */
	public function wp_post_thumbnail_only_html($item_id)
	{
		$markup       = '<p class="description description-thin" ><label>%s<br /><a title="%s" href="#" class="set-post-thumbnail button%s" data-item-id="%s" style="height: auto;">%s</a>%s</label></p>';

		$thumbnail_id = get_post_thumbnail_id($item_id);
		$content      = sprintf(
			$markup,
			esc_html__('Menu image', 'menu-image'),
			$thumbnail_id ? esc_attr__('Change menu item image', 'menu-image') : esc_attr__('Set menu item image', 'menu-image'),
			'',
			$item_id,
			$thumbnail_id ? wp_get_attachment_image($thumbnail_id, "thumbnail") : esc_html__('Set image', 'menu-image'),
			$thumbnail_id ? '<a href="#" class="remove-post-thumbnail">' . __('Remove', 'menu-image') . '</a>' : ''
		);

		return $content;
	}

	/**
	 * Output HTML for the menu item images section.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id The post ID or object associated with the thumbnail, defaults to global $post.
	 *
	 * @return string html
	 */
	public function wp_post_thumbnail_html($item_id)
	{
		$content      = $this->wp_post_thumbnail_only_html($item_id);

		$title_position = get_post_meta($item_id, '_menu_item_image_title_position', true);
		if (!$title_position) {
			$title_position = apply_filters('biws_menu_default_title_position', 'after');
		}

		ob_start();
?>

		<div class="menu-item-image-options">
			<p class="description description-wide">
				<label><?php _e('Title position', 'menu-image'); ?></label><br />
				<?php
				$positions = array(
					'hide'   => __( 'Hide', 'menu-image' ),
					'below'  => __('Below', 'menu-image'),
					'after'  => __('After', 'menu-image'),
				);
				foreach ($positions as $position => $label) :
					printf(
						"<label><input type='radio' name='menu_item_image_title_position[%s]' value='%s'%s/> %s</label>",
						$item_id,
						esc_attr($position),
						$title_position == $position ? ' checked="checked"' : '',
						$label
					);
				endforeach;
				?>

			</p>
		</div>

	<?php
		$content = "<div class='menu-item-images' style='min-height:70px'>$content</div>" . ob_get_clean();

		return apply_filters('admin_menu_item_thumbnail_html', $content, $item_id);
	}

	/**
	 * Update item thumbnail via ajax action.
	 *
	 * @since 2.0
	 */
	public function wp_ajax_set_menu_item_thumbnail()
	{
		$json = !empty($_REQUEST['json']);

		$post_ID = intval($_POST['post_id']);
		if (!current_user_can('edit_post', $post_ID)) {
			wp_die(-1);
		}

		$thumbnail_id = intval($_POST['thumbnail_id']);

		check_ajax_referer('update-menu-item');

		if ($thumbnail_id == '-1') {
			$success = delete_post_thumbnail($post_ID);
		} else {
			$success = set_post_thumbnail($post_ID, $thumbnail_id);
		}

		if ($success) {
			$return = $this->wp_post_thumbnail_only_html($post_ID);
			$json ? wp_send_json_success($return) : wp_die($return);
		}

		wp_die(0);
	}

	public function biws_menu_custom_fields($item_id, $item, $depth, $args)
	{
		if (!$item_id && isset($item->ID)) {
			$item_id = $item->ID;
		}
	?>
		<div class="field-image hide-if-no-js wp-media-buttons">
			<?php echo $this->wp_post_thumbnail_html($item_id); ?>
		</div>
<?php
	}

	/**
	 * Set used attachment ids.
	 *
	 * @param string $size
	 * @param int    $id
	 */
	public function setUsedAttachments($size, $id)
	{
		$this->used_attachments[$size][] = $id;
	}

	/**
	 * Check if attachment is used in menu items.
	 *
	 * @param int    $id
	 * @param string $size
	 *
	 * @return bool
	 */
	public function isAttachmentUsed($id, $size = null)
	{
		if (!is_null($size)) {
			return is_string($size) && isset($this->used_attachments[$size]) && in_array($id, $this->used_attachments[$size]);
		} else {
			foreach ($this->used_attachments as $used_attachment) {
				if (in_array($id, $used_attachment)) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Filters the list of attachment image attributes.
	 *
	 * @since 2.8.0
	 *
	 * @param array        $attr       Attributes for the image markup.
	 * @param WP_Post      $attachment Image attachment post.
	 * @param string|array $size       Requested size. Image size or array of width and height values
	 *                                 (in that order). Default 'thumbnail'.
	 *
	 * @return array Valid array of image attributes.
	 */
	public function wp_get_attachment_image_attributes($attr, $attachment, $size)
	{
		if ($this->isAttachmentUsed($attachment->ID, $size)) {
			unset($attr['sizes'], $attr['srcset']);
		}

		return $attr;
	}

	/**
	 * Mark item as processed to prevent re-processing it again.
	 *
	 * @param int $id
	 */
	protected function setProcessed($id)
	{
		$this->processed[] = $id;
	}

	/**
	 * Check if was already processed.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	protected function isProcessed($id)
	{
		return in_array($id, $this->processed);
	}
}

$biws_menu = new BIWS_Menu_Plugin();

require_once( ABSPATH . 'wp-admin/includes/nav-menu.php' );

function add_description_to_menu($item_output, $item, $depth, $args) {
    if (strlen($item->description) > 0 ) {
		$suffix = "</a>{$args->after}";
		$html = sprintf('<small>%s</small>', esc_html($item->description));
		if (strpos($item_output, "</span>") !== false) {
			$suffix = "</span>" . $suffix;
		}
        $item_output = substr($item_output, 0, -strlen($suffix)) . $html . $suffix;
    }

    return $item_output;
}
add_filter('walker_nav_menu_start_el', 'add_description_to_menu', 10, 4);

function anchor_nav_menu_items($menu)
{
   // contains # and does not start with it
   if (strpos($menu->url, '#')) {
      // check if this is a relative url
      // FIXME this condition needs some additional work because it relys on using the host properly
      //       www.domain.com/foo/bar would probably not match https://domain.com/foo/bar
      if (strpos(get_site_url(), $menu->url) === false) {
         $menu_site_url = get_site_url(null, $menu->url);
         $menu_site_id = url_to_postid($menu_site_url);
         // check if the current site is referenced in the menu item
         if ($menu_site_id === get_the_id()) {
            $menu_site_url_explode = explode('#', $menu->url);
            $menu->url = '#' . end($menu_site_url_explode);
         }
      }
   }

   return $menu;
}

add_filter('wp_setup_nav_menu_item', 'anchor_nav_menu_items');