<?php
/**
 * Fixed text/image section used for Mission, Innovation and Quality.
 * The front-page template passes the field prefix in $args['prefix'].
 *
 * @package Weldman
 */

$prefix   = ! empty( $args['prefix'] ) ? sanitize_key( $args['prefix'] ) : '';
$title    = $prefix ? weldman_field( $prefix . '_title' ) : '';
$text     = $prefix ? weldman_field( $prefix . '_text' ) : '';
$image    = $prefix ? weldman_field( $prefix . '_image' ) : null;
$position = $prefix ? weldman_field( $prefix . '_image_position' ) : '';
$position = $position ? $position : 'right';

if ( ! $title && ! $text && ! $image ) {
	return;
}
?>
<section class="section section-text-block section-text-block--image-<?php echo esc_attr( $position ); ?>">
	<div class="container section-text-block__inner">
		<?php if ( $image ) : ?>
			<div class="section-text-block__media">
				<?php
				weldman_image(
					$image,
					'weldman-card',
					array(
						'class' => 'section-text-block__image',
						'alt'   => weldman_image_alt( $image ),
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="section-text-block__content">
			<?php if ( $title ) : ?>
				<h2 class="section-text-block__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( $text ) : ?>
				<div class="section-text-block__text">
					<?php echo wp_kses_post( $text ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
