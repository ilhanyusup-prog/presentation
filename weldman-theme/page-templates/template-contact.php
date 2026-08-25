<?php
/**
 * Template Name: Contact page
 * Template Post Type: page
 *
 * Used for the "Kontakt" page (/contact/): intro text, contact details
 * (pulled from the Site Options page so they stay in sync with the
 * footer), an optional map embed, and the contact form.
 *
 * @package Weldman
 */

get_header();

$address = weldman_option( 'company_address' );
$phones  = weldman_option( 'phone_numbers' );
$email   = weldman_option( 'email' );
?>

<?php while ( have_posts() ) : the_post(); ?>

	<article <?php post_class( 'page-content-wrap contact-page' ); ?>>
		<div class="container">
			<?php weldman_page_eyebrow(); ?>

			<header class="page-header">
				<h1 class="page-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="contact-page__grid">
				<div class="contact-page__info">
					<?php
					$intro = weldman_field( 'contact_intro' );
					if ( $intro ) {
						echo wp_kses_post( $intro );
					} else {
						the_content();
					}
					?>

					<ul class="contact-page__details">
						<?php if ( $address ) : ?>
							<li>
								<span class="contact-page__label"><?php esc_html_e( 'Address', 'weldman' ); ?></span>
								<address><?php echo esc_html( $address ); ?></address>
							</li>
						<?php endif; ?>

						<?php if ( ! empty( $phones ) && is_array( $phones ) ) : ?>
							<li>
								<span class="contact-page__label"><?php esc_html_e( 'Phone', 'weldman' ); ?></span>
								<?php foreach ( $phones as $row ) : ?>
									<?php if ( empty( $row['phone'] ) ) { continue; } ?>
									<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $row['phone'] ) ); ?>">
										<?php echo esc_html( $row['phone'] ); ?>
									</a><br />
								<?php endforeach; ?>
							</li>
						<?php endif; ?>

						<?php if ( $email ) : ?>
							<li>
								<span class="contact-page__label"><?php esc_html_e( 'Email', 'weldman' ); ?></span>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="contact-page__form">
					<?php
					$form_title = weldman_field( 'contact_form_title' );
					?>
					<h2><?php echo esc_html( $form_title ? $form_title : __( 'Kirjuta meile!', 'weldman' ) ); ?></h2>
					<?php weldman_render_contact_form(); ?>
				</div>
			</div>

			<?php
			$map_embed = weldman_field( 'contact_map_embed' );
			if ( $map_embed ) :
				?>
				<div class="contact-page__map">
					<iframe
						src="<?php echo esc_url( $map_embed ); ?>"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						title="<?php esc_attr_e( 'Map', 'weldman' ); ?>"
					></iframe>
				</div>
			<?php endif; ?>
		</div>
	</article>

<?php endwhile; ?>

<?php
get_footer();
