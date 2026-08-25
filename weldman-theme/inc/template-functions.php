<?php
/**
 * Helper / template functions used across the theme.
 *
 * @package Weldman
 */

/**
 * Safe wrapper around ACF's get_field() so the theme never fatals when the
 * plugin is missing (e.g. right after a fresh checkout before setup).
 *
 * @param string $selector Field name/key.
 * @param mixed  $post_id  Post ID, or false for current post.
 * @return mixed
 */
function weldman_field( $selector, $post_id = false ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return get_field( $selector, $post_id );
}

/**
 * Read a site-wide value stored by WordPress Customizer.
 *
 * @param string $selector Setting name without the weldman_ prefix.
 * @param mixed  $default  Default value.
 * @return mixed
 */
function weldman_site_setting( $selector, $default = '' ) {
	return get_theme_mod( 'weldman_' . $selector, $default );
}

/**
 * Return the configured phone numbers as a compact array.
 *
 * @return array
 */
function weldman_phone_numbers() {
	return array_values(
		array_filter(
			array(
				weldman_site_setting( 'phone_1' ),
				weldman_site_setting( 'phone_2' ),
			)
		)
	);
}

/**
 * Return configured social links in the format expected by templates/schema.
 *
 * @return array
 */
function weldman_social_links() {
	$links = array();

	foreach ( array( 'facebook', 'instagram', 'tiktok', 'youtube', 'linkedin', 'whatsapp' ) as $platform ) {
		$url = weldman_site_setting( 'social_' . $platform );
		if ( $url ) {
			$links[] = array(
				'platform' => $platform,
				'url'      => $url,
			);
		}
	}

	return $links;
}

/**
 * Return up to six fixed partner logo/link slots from the current page.
 *
 * @return array
 */
function weldman_partner_slots() {
	$partners = array();

	for ( $i = 1; $i <= 6; $i++ ) {
		$logo = weldman_field( 'partner_' . $i . '_logo' );
		if ( $logo ) {
			$partners[] = array(
				'logo' => $logo,
				'link' => weldman_field( 'partner_' . $i . '_link' ),
			);
		}
	}

	return $partners;
}

/**
 * Output a responsive <img> for an ACF image array, falling back gracefully
 * when the field is empty. Uses core srcset/sizes handling.
 *
 * @param array|int $image      ACF image array (or attachment ID).
 * @param string    $size       Registered image size.
 * @param array     $attr       Extra attributes (class, sizes...).
 * @param bool      $lazy       Whether to lazy-load (set false for LCP/hero images).
 * @return void
 */
function weldman_image( $image, $size = 'large', $attr = array(), $lazy = true ) {
	$attachment_id = 0;

	if ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		$attachment_id = (int) $image['ID'];
	} elseif ( is_numeric( $image ) ) {
		$attachment_id = (int) $image;
	}

	if ( ! $attachment_id ) {
		return;
	}

	$defaults = array(
		'class'   => '',
		'loading' => $lazy ? 'lazy' : false,
	);

	if ( ! $lazy ) {
		$defaults['fetchpriority'] = 'high';
	}

	$attr = wp_parse_args( $attr, $defaults );

	if ( false === $attr['loading'] ) {
		unset( $attr['loading'] );
	}

	echo wp_get_attachment_image( $attachment_id, $size, false, $attr );
}

/**
 * Return the alt text for an ACF image array, falling back to the attachment's
 * own alt text / title so every <img> ships a meaningful alt attribute.
 *
 * @param array $image ACF image array.
 * @return string
 */
function weldman_image_alt( $image ) {
	if ( ! is_array( $image ) ) {
		return '';
	}
	if ( ! empty( $image['alt'] ) ) {
		return $image['alt'];
	}
	if ( ! empty( $image['title'] ) ) {
		return $image['title'];
	}
	return '';
}

/**
 * Pagination for archive pages using core pagination.
 */
function weldman_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( '&larr; Previous', 'weldman' ),
			'next_text'          => __( 'Next &rarr;', 'weldman' ),
			'screen_reader_text' => __( 'Posts navigation', 'weldman' ),
			'class'              => 'pagination',
		)
	);
}

/**
 * Social platform → inline SVG icon map used in the footer / contact section.
 *
 * @param string $platform Platform slug (facebook, instagram, tiktok, youtube, linkedin...).
 * @return string
 */
function weldman_social_icon( $platform ) {
	$platform = strtolower( trim( (string) $platform ) );

	$icons = array(
		'facebook'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.9h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.22.6 1.77 1.15.55.55.89 1.11 1.15 1.77.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.15 1.77 4.9 4.9 0 0 1-1.77 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.77-1.15 4.9 4.9 0 0 1-1.15-1.77c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.22 1.15-1.77A4.9 4.9 0 0 1 5.45.53C6.09.28 6.82.11 7.88.06 8.94.01 9.28 0 12 0Zm0 5.35A6.65 6.65 0 1 0 12 18.65 6.65 6.65 0 0 0 12 5.35Zm0 2.16A4.49 4.49 0 1 1 12 16.14 4.49 4.49 0 0 1 12 7.51Zm6.9-3.38a1.56 1.56 0 1 0 0 3.12 1.56 1.56 0 0 0 0-3.12Z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.44 3.5 12 3.5 12 3.5s-7.44 0-9.38.55A3.02 3.02 0 0 0 .5 6.19 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14C4.56 20.5 12 20.5 12 20.5s7.44 0 9.38-.55a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.81ZM9.6 15.6V8.4L15.8 12Z"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM3.56 20.45h3.56V9H3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"/></svg>',
		'tiktok'    => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M16.6 5.82c-.9-.6-1.55-1.5-1.77-2.55V3h-3.29v14.44a2.9 2.9 0 1 1-2.05-2.77v-3.32a6.16 6.16 0 1 0 5.34 6.1V9.4a8.16 8.16 0 0 0 4.77 1.53V7.61c-.98 0-1.94-.28-2.77-.79-.08-.05-.15-.1-.23-.15Z"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.5 14.4c-.3-.15-1.7-.85-2-.95-.3-.1-.5-.15-.7.15-.2.3-.8.95-1 1.15-.2.2-.4.2-.7.1-.3-.15-1.3-.5-2.5-1.55-1-.85-1.65-1.9-1.85-2.2-.2-.3 0-.45.15-.6.15-.15.35-.4.5-.6.15-.2.2-.35.3-.55.1-.2.05-.4-.05-.6-.1-.2-.5-1.2-.7-1.65-.2-.45-.4-.4-.55-.4h-.5c-.15 0-.4.05-.6.3-.2.25-.8.8-.8 1.9 0 1.1.8 2.2 1 2.4.2.2 1.5 2.3 3.7 3.15 2.2.85 2.2.6 2.6.55.4-.05 1.3-.55 1.5-1.05.2-.5.2-.95.15-1.05-.05-.1-.2-.15-.45-.3ZM12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Z"/></svg>',
	);

	if ( isset( $icons[ $platform ] ) ) {
		return $icons[ $platform ];
	}

	// Generic fallback "link" icon.
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.9 12a5 5 0 0 1 5-5h3v2h-3a3 3 0 1 0 0 6h3v2h-3a5 5 0 0 1-5-5Zm7-1h6v2h-6v-2Zm5-4h3a5 5 0 1 1 0 10h-3v-2h3a3 3 0 1 0 0-6h-3V7Z"/></svg>';
}

/**
 * Renders the mobile-friendly primary navigation, falling back to a simple
 * page list if no menu has been assigned yet (fresh install).
 */
function weldman_primary_nav() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'primary-menu',
				'menu_id'        => 'primary-menu',
				'fallback_cb'    => false,
				'depth'          => 2,
			)
		);
	} else {
		echo '<ul class="primary-menu">';
		wp_list_pages(
			array(
				'title_li' => '',
			)
		);
		echo '</ul>';
	}
}

/**
 * Breadcrumb-style eyebrow used on inner pages (Kontakt, Innovatsioon...).
 *
 * @return void
 */
function weldman_page_eyebrow() {
	if ( is_front_page() ) {
		return;
	}
	echo '<p class="page-eyebrow"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'weldman' ) . '</a> / ' . esc_html( get_the_title() ) . '</p>';
}
