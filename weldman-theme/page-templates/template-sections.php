<?php
/**
 * Template Name: Flexible sections
 * Template Post Type: page
 *
 * Generic page built from the same reusable ACF Flexible Content layouts
 * used on the front page (Hero, Text block with image, Partners, Contact
 * form). Used for pages such as "Innovatsioon" (/ecosystem/) that need the
 * same visual building blocks as the homepage but as a standalone page.
 *
 * @package Weldman
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) : ?>

		<?php weldman_render_page_sections(); ?>

	<?php else : ?>

		<article <?php post_class( 'page-content-wrap' ); ?>>
			<div class="container">
				<?php weldman_page_eyebrow(); ?>
				<header class="page-header">
					<h1 class="page-header__title"><?php the_title(); ?></h1>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>

	<?php endif; ?>

<?php endwhile; ?>

<?php
get_footer();
