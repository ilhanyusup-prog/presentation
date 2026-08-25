<?php
/**
 * Post card used in archive.php / index.php listings.
 *
 * @package Weldman
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			the_post_thumbnail(
				'weldman-card',
				array(
					'class' => 'post-card__image',
					'alt'   => the_title_attribute( array( 'echo' => false ) ),
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="post-card__body">
		<p class="post-card__meta">
			<time class="post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		</p>

		<?php the_title( sprintf( '<h2 class="post-card__title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		<div class="post-card__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<a class="post-card__link" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Read more', 'weldman' ); ?> &rarr;
		</a>
	</div>
</article>
