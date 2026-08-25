<?php
/**
 * The footer for the theme.
 *
 * @package Weldman
 */

$address = weldman_option( 'company_address' );
$phones  = weldman_option( 'phone_numbers' );
$email   = weldman_option( 'email' );
$socials = weldman_option( 'social_links' );
?>

	</main><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container site-footer__inner">
			<div class="site-footer__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="site-logo-text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $address ) : ?>
				<address class="site-footer__address">
					<?php echo esc_html( $address ); ?>
				</address>
			<?php endif; ?>

			<?php if ( ! empty( $phones ) && is_array( $phones ) ) : ?>
				<ul class="site-footer__phones">
					<?php foreach ( $phones as $row ) : ?>
						<?php if ( empty( $row['phone'] ) ) { continue; } ?>
						<li>
							<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $row['phone'] ) ); ?>">
								<?php echo esc_html( $row['phone'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $email ) : ?>
				<p class="site-footer__email">
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $socials ) && is_array( $socials ) ) : ?>
				<ul class="social-links">
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

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer', 'weldman' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'footer-menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<p class="site-footer__copyright">
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'weldman' ); ?>
			</p>
		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
