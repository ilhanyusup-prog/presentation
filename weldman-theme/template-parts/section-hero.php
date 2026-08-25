<?php
/**
 * Hero section — used as the first flexible-content layout on the front
 * page and, optionally, on other flexible-sections pages.
 *
 * Renders the single <h1> for the page, a short subtitle, a side image and
 * (when filled in) a pull-quote with author underneath.
 *
 * @package Weldman
 */

$title         = get_sub_field( 'hero_title' );
$subtitle      = get_sub_field( 'hero_subtitle' );
$quote_text    = get_sub_field( 'hero_quote_text' );
$quote_author  = get_sub_field( 'hero_quote_author' );
$image         = get_sub_field( 'hero_image' );
?>
<section class="section section-hero">
	<div class="container section-hero__inner">
		<div class="section-hero__content">
			<?php if ( $title ) : ?>
				<h1 class="section-hero__title"><?php echo esc_html( $title ); ?></h1>
			<?php endif; ?>

			<?php if ( $subtitle ) : ?>
				<p class="section-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $image ) : ?>
			<div class="section-hero__media">
				<?php
				weldman_image(
					$image,
					'weldman-hero',
					array(
						'class' => 'section-hero__image',
						'alt'   => weldman_image_alt( $image ),
					),
					false
				);
				?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $quote_text ) : ?>
		<div class="container">
			<blockquote class="section-hero__quote">
				<p><?php echo esc_html( $quote_text ); ?></p>
				<?php if ( $quote_author ) : ?>
					<cite class="section-hero__quote-author">
						<?php if ( $image ) : ?>
							<?php
							weldman_image(
								$image,
								'thumbnail',
								array( 'class' => 'section-hero__quote-avatar' )
							);
							?>
						<?php endif; ?>
						<?php echo esc_html( $quote_author ); ?>
					</cite>
				<?php endif; ?>
			</blockquote>
		</div>
	<?php endif; ?>
</section>
