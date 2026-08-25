<?php
/**
 * The template for displaying a single blog post.
 *
 * @package Weldman
 */

get_header();
?>

<div class="container">
	<?php while ( have_posts() ) : the_post(); ?>

		<article <?php post_class( 'single-post' ); ?>>
			<header class="page-header single-post__header">
				<?php weldman_page_eyebrow(); ?>
				<h1 class="page-header__title"><?php the_title(); ?></h1>
				<p class="single-post__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					&middot;
					<?php esc_html_e( 'by', 'weldman' ); ?> <?php the_author(); ?>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="single-post__media">
					<?php
					the_post_thumbnail(
						'weldman-hero',
						array(
							'class'   => 'single-post__image',
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

			<footer class="single-post__footer">
				<?php
				$categories = get_the_category_list( ', ' );
				if ( $categories ) {
					echo '<p class="single-post__categories">' . esc_html__( 'Categories:', 'weldman' ) . ' ' . wp_kses_post( $categories ) . '</p>';
				}
				?>
			</footer>
		</article>

		<nav class="single-post__nav">
			<div class="single-post__nav-prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
			<div class="single-post__nav-next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
		</nav>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>

	<?php endwhile; ?>
</div>

<?php
get_footer();
