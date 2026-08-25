<?php
/**
 * Template Name: Content page
 * Template Post Type: page
 *
 * Simple inner-page template for pages such as Innovatsioon (/ecosystem/).
 * It uses standard WordPress title, featured image and editor content, with
 * no ACF Pro field types.
 *
 * @package Weldman
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article <?php post_class( 'page-content-wrap' ); ?>>
		<div class="container">
			<?php weldman_page_eyebrow(); ?>
			<header class="page-header">
				<h1 class="page-header__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="page-header__media">
					<?php the_post_thumbnail( 'weldman-hero', array( 'class' => 'page-header__image', 'loading' => 'eager' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
