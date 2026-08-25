<?php
/**
 * The template for displaying 404 (Not Found) pages.
 *
 * @package Weldman
 */

get_header();
?>

<div class="container error-404">
	<header class="page-header">
		<h1 class="page-header__title"><?php esc_html_e( 'Page not found', 'weldman' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for could not be found. It might have been moved or no longer exists.', 'weldman' ); ?></p>
	</header>

	<?php get_search_form(); ?>

	<p class="error-404__back">
		<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to homepage', 'weldman' ); ?>
		</a>
	</p>
</div>

<?php
get_footer();
