<?php
/**
 * Baseline, manual SEO helpers.
 *
 * These are intentionally simple — the theme is designed to be used without
 * an SEO plugin at first (see project brief, step 12/13). Once Yoast SEO or
 * Rank Math is installed later, most of this can be left in place: plugins
 * take over the <title>/meta description/schema output and these fallbacks
 * simply stop being needed for pages the plugin manages, or you can disable
 * this whole file by removing the require line in functions.php.
 *
 * @package Weldman
 */

/**
 * Output a <meta name="description"> tag using, in order of priority:
 * 1. An ACF "meta_description" field on the current page/post,
 * 2. The post excerpt,
 * 3. The site tagline (front page only).
 */
function weldman_meta_description() {
	if ( is_admin() ) {
		return;
	}

	$description = '';

	if ( is_singular() ) {
		$description = weldman_field( 'meta_description' );

		if ( ! $description && has_excerpt() ) {
			$description = get_the_excerpt();
		}

		if ( ! $description ) {
			$description = wp_trim_words( wp_strip_all_tags( get_the_content() ), 35 );
		}
	} elseif ( is_front_page() ) {
		$description = get_bloginfo( 'description' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$description = term_description();
	}

	$description = wp_strip_all_tags( (string) $description );
	$description = trim( preg_replace( '/\s+/', ' ', $description ) );

	if ( '' === $description ) {
		return;
	}

	printf( '<meta name="description" content="%s" />' . "\n", esc_attr( wp_trim_words( $description, 45 ) ) );
}
add_action( 'wp_head', 'weldman_meta_description', 1 );

// Note: WordPress core already prints a <link rel="canonical"> tag via
// rel_canonical() on wp_head, so the theme does not duplicate it here.

/**
 * Organization / LocalBusiness JSON-LD structured data, output in the footer.
 * Pulls address / phones / email / social links from WordPress Customizer so
 * editors only maintain this data in one place.
 */
function weldman_schema_jsonld() {
	$address = weldman_site_setting( 'company_address', 'Lennujaama tee 7, 11101 Tallinn' );
	$email   = weldman_site_setting( 'email', 'info@weldman.ee' );
	$phones  = weldman_phone_numbers();
	$socials = weldman_social_links();

	$same_as = array();
	if ( is_array( $socials ) ) {
		foreach ( $socials as $social ) {
			if ( ! empty( $social['url'] ) ) {
				$same_as[] = $social['url'];
			}
		}
	}

	$telephone = '';
	if ( ! empty( $phones ) ) {
		$telephone = $phones[0];
	}

	$schema = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'LocalBusiness',
		'name'      => get_bloginfo( 'name' ),
		'url'       => home_url( '/' ),
		'description' => get_bloginfo( 'description' ),
	);

	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( $logo_src ) {
			$schema['logo'] = $logo_src;
			$schema['image'] = $logo_src;
		}
	}

	if ( $address ) {
		$schema['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $address,
			'addressLocality' => 'Tallinn',
			'addressCountry'  => 'EE',
		);
	}

	if ( $telephone ) {
		$schema['telephone'] = $telephone;
	}

	if ( $email ) {
		$schema['email'] = $email;
	}

	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = $same_as;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'weldman_schema_jsonld' );
