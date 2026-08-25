<?php
/**
 * The main template file — fallback used when no more specific template
 * matches (also serves as the base blog listing template).
 *
 * @package Weldman
 */

get_header();
?>

<div class="container blog-archive">
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

		<p><?php esc_html_e( 'Nothing found.', 'weldman' ); ?></p>

	<?php endif; ?>
</div>

<?php
get_footer();
