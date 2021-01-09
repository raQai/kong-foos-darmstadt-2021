<?php

$plugin_dir = plugin_dir_path(__FILE__);

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
	<?php
	// On the cover page template, output the cover header.
	$cover_header_style   = '';
	$cover_header_classes = '';

	$color_overlay_style   = '';
	$color_overlay_classes = '';

	$image_url = !post_password_required() ? get_the_post_thumbnail_url(get_the_ID(), 'twentytwenty-fullscreen') : '';

	if ($image_url) {
		$cover_header_style   = ' style="background-image: url( ' . esc_url($image_url) . ' );"';
		$cover_header_classes = ' bg-image';
	}

	// Get the color used for the color overlay.
	$color_overlay_color = get_theme_mod('cover_template_overlay_background_color');
	if ($color_overlay_color) {
		$color_overlay_style = ' style="color: ' . esc_attr($color_overlay_color) . ';"';
	} else {
		$color_overlay_style = '';
	}

	// Get the fixed background attachment option.
	if (get_theme_mod('cover_template_fixed_background', true)) {
		$cover_header_classes .= ' bg-attachment-fixed';
	}

	// Get the opacity of the color overlay.
	$color_overlay_opacity  = get_theme_mod('cover_template_overlay_opacity');
	$color_overlay_opacity  = (false === $color_overlay_opacity) ? 80 : $color_overlay_opacity;
	$color_overlay_classes .= ' opacity-' . $color_overlay_opacity;
	?>

	<div class="cover-header <?php echo $cover_header_classes; ?>" <?php echo $cover_header_style; ?>>
		<div class="cover-header-inner-wrapper screen-height">
			<div class="cover-header-inner">
				<div class="cover-color-overlay color-accent<?php echo esc_attr($color_overlay_classes); ?>" <?php echo $color_overlay_style; ?>></div>

				<header class="entry-header has-text-align-center">
					<div class="entry-header-inner section-inner medium">

						<?php

						the_title('<h1 class="entry-title">', '</h1>');

						/*
						if (has_excerpt()) {
						 ?>
							<div class="intro-text section-inner max-percentage small">
								<?php the_excerpt(); ?>
							</div>
						<?php
						}
						*/
						?>

					</div><!-- .entry-header-inner -->
				</header><!-- .entry-header -->

			</div><!-- .cover-header-inner -->
		</div><!-- .cover-header-inner-wrapper -->
	</div><!-- .cover-header -->

	<div class="post-inner" id="post-inner">
		<?php include $plugin_dir . 'content-sponsors.php'; ?>
		<div class="entry-content">
			<div class="alignwide biws-event-wrapper">
				<div class="biws-event-info has-primary-background-color">
					<?php include $plugin_dir . 'content-info.php'; ?>
				</div>
				<div class="biws-event-content has-small-font-size wp-block-group has-subtle-background-background-color has-background">
					<div class="wp-block-group__inner-container">
						<?php the_content(); ?>
					</div>
				</div>
			</div>

		</div><!-- .entry-content -->
		<?php edit_post_link(); ?>

	</div><!-- .post-inner -->

</article><!-- .post -->