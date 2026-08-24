<?php
/**
 * "Text block with image" section — reusable layout used for Missioon,
 * Innovatsioon and Kvaliteet on the front page (and any page built with the
 * flexible "Sections" page template).
 *
 * @package Weldman
 */

$title    = get_sub_field( 'block_title' );
$text     = get_sub_field( 'block_text' );
$image    = get_sub_field( 'block_image' );
$position = get_sub_field( 'image_position' );
$position = $position ? $position : 'right';
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
