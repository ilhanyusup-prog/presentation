<?php
/**
 * The template for displaying search results pages.
 *
 * @package Weldman
 */

get_header();
?>

<div class="container blog-archive">
	<header class="page-header">
		<h1 class="page-header__title">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Search results for: %s', 'weldman' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content-post' );
			endwhile;
			?>
		</div>

		<?php weldman_pagination(); ?>

	<?php else : ?>

		<p><?php esc_html_e( 'No results found. Please try a different search.', 'weldman' ); ?></p>
		<?php get_search_form(); ?>

	<?php endif; ?>
</div>

<?php
get_footer();
