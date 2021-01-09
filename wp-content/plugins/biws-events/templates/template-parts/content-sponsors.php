<?php
$event_partners = wp_get_object_terms(get_the_id(), 'event_partner', array(
	'meta_key'   => 'priority',
	'orderby' => 'meta_value_num',
	'order' => 'DESC',
));
$event_partner_images = array();
foreach ($event_partners as $event_partner) {
	$event_partner_image_id = get_term_meta($event_partner->term_id, 'partner-image-id', true);
	if ($event_partner_image_id) {
		// $event_partner_image = wp_get_attachment_image($event_partner_image_id);
		$event_partner_image = wp_get_attachment_image($event_partner_image_id, 'thumbnail', false, array('class' => 'biws-event-sponsor-logo'));
		if ($event_partner_image) {
			$event_partner_images[] = $event_partner_image;
		}
		unset($event_partner_image);
	}
	unset($event_partner_image_id);
}
unset($event_partners);
if ($event_partner_images) {
?>
	<!-- start sponsors and partners -->
	<div class="wp-block-cover alignfull has-subtle-background-background-color has-background-dim biws_min_cover p0 m0">
		<div class="wp-block-cover__inner-container">
			<p class="has-text-color has-text-align-center has-small-font-size has-secondary-color alignfull mb0">SPONSOREN UND PARTNER</p>
			<ul class="biws-event-sponsors">
				<?php foreach ($event_partner_images as $event_partner_image) { ?>
					<li class="biws-event-sponsor-item"><?php echo $event_partner_image; ?></li>
				<?php
					unset($event_partner_image);
				}
				unset($event_partner_images);
				?>
			</ul>
		</div>
	</div>
	<!-- end sponsors and partners -->
<?php }
?>