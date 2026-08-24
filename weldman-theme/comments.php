<?php
/**
 * The template for displaying comments.
 *
 * @package Weldman
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$comment_count = get_comments_number();
			if ( 1 === (int) $comment_count ) {
				esc_html_e( '1 comment', 'weldman' );
			} else {
				printf(
					/* translators: %s: number of comments. */
					esc_html( _n( '%s comment', '%s comments', $comment_count, 'weldman' ) ),
					esc_html( number_format_i18n( $comment_count ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'weldman' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit' => 'btn btn-primary',
		)
	);
	?>

</div>
