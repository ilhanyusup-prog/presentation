<?php
/**
 * The front page template.
 *
 * Renders the "page_sections" ACF Flexible Content field (Hero, Text block
 * with image x3 for Missioon/Innovatsioon/Kvaliteet, Partners, Contact form).
 *
 * @package Weldman
 */

get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) : ?>
			<?php weldman_render_page_sections(); ?>
		<?php else : ?>
			<section class="section container">
				<header class="page-header">
					<h1><?php the_title(); ?></h1>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</section>
		<?php endif; ?>

	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
