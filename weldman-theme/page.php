<?php
/**
 * The template for displaying a standard WordPress page.
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
					<?php
					the_post_thumbnail(
						'weldman-hero',
						array(
							'class'   => 'page-header__image',
							'alt'     => the_title_attribute( array( 'echo' => false ) ),
							'loading' => 'eager',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="entry-content">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'weldman' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</div>
	</article>

<?php endwhile; ?>

<?php
get_footer();
