<?php
/**
 * The template for displaying archive pages (blog listing, categories, tags, dates).
 *
 * @package Weldman
 */

get_header();
?>

<div class="container blog-archive">
	<header class="page-header">
		<h1 class="page-header__title">
			<?php
			if ( is_category() ) {
				single_cat_title();
			} elseif ( is_tag() ) {
				single_tag_title();
			} elseif ( is_author() ) {
				the_archive_title( '', '' );
				echo esc_html( get_the_author() );
			} elseif ( is_date() ) {
				the_archive_title( '', '' );
			} else {
				esc_html_e( 'Blog', 'weldman' );
			}
			?>
		</h1>
		<?php
		$description = get_the_archive_description();
		if ( $description ) {
			echo '<div class="archive-description">' . wp_kses_post( $description ) . '</div>';
		}
		?>
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

		<p><?php esc_html_e( 'No posts found.', 'weldman' ); ?></p>

	<?php endif; ?>
</div>

<?php
get_footer();
