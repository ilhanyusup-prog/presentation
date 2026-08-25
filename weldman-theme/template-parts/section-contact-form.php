<?php
/**
 * Fixed homepage contact form section ("Kirjuta meile!").
 *
 * @package Weldman
 */

$title = weldman_field( 'form_title' );
$title = $title ? $title : __( 'Kirjuta meile!', 'weldman' );
?>
<section class="section section-contact-form" id="contact">
	<div class="container section-contact-form__inner">
		<h2 class="section-contact-form__title"><?php echo esc_html( $title ); ?></h2>

		<div class="section-contact-form__box">
			<?php weldman_render_contact_form(); ?>
		</div>

		<?php
		$socials = weldman_social_links();
		if ( ! empty( $socials ) && is_array( $socials ) ) :
			?>
			<ul class="social-links social-links--centered">
				<?php foreach ( $socials as $social ) : ?>
					<?php if ( empty( $social['url'] ) ) { continue; } ?>
					<li>
						<a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $social['platform'] ) ); ?>">
							<?php echo weldman_social_icon( $social['platform'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
