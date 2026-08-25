<?php
/**
 * The front page template.
 *
 * Renders fixed ACF Free fields in a fixed order:
 * Hero, Mission, Innovation, Quality, Partners, Contact form.
 *
 * @package Weldman
 */

get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php if ( weldman_field( 'hero_title' ) ) : ?>
			<?php get_template_part( 'template-parts/section-hero' ); ?>
			<?php get_template_part( 'template-parts/section-text-block', null, array( 'prefix' => 'mission' ) ); ?>
			<?php get_template_part( 'template-parts/section-text-block', null, array( 'prefix' => 'innovation' ) ); ?>
			<?php get_template_part( 'template-parts/section-text-block', null, array( 'prefix' => 'quality' ) ); ?>
			<?php get_template_part( 'template-parts/section-partners' ); ?>
			<?php get_template_part( 'template-parts/section-contact-form' ); ?>
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
