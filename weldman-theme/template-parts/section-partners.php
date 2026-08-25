<?php
/**
 * Partners / certifications logos section.
 *
 * @package Weldman
 */

$title    = get_sub_field( 'partners_title' );
$partners = get_sub_field( 'partners' );

if ( empty( $partners ) ) {
	return;
}
?>
<section class="section section-partners">
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="section-partners__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<ul class="section-partners__list">
			<?php foreach ( $partners as $partner ) : ?>
				<?php
				$logo = isset( $partner['logo'] ) ? $partner['logo'] : null;
				$link = isset( $partner['link'] ) ? $partner['link'] : '';

				if ( ! $logo ) {
					continue;
				}
				?>
				<li class="section-partners__item">
					<?php if ( $link ) : ?>
						<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer">
					<?php endif; ?>

					<?php
					weldman_image(
						$logo,
						'medium',
						array(
							'class' => 'section-partners__logo',
							'alt'   => weldman_image_alt( $logo ),
						)
					);
					?>

					<?php if ( $link ) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
