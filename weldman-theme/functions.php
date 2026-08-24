<?php
/**
 * Weldman theme functions and definitions.
 *
 * @package Weldman
 */

if ( ! defined( 'WELDMAN_VERSION' ) ) {
	define( 'WELDMAN_VERSION', '1.0.0' );
}

if ( ! defined( 'WELDMAN_DIR' ) ) {
	define( 'WELDMAN_DIR', get_template_directory() );
}

if ( ! defined( 'WELDMAN_URI' ) ) {
	define( 'WELDMAN_URI', get_template_directory_uri() );
}

/**
 * Theme setup.
 */
function weldman_setup() {
	// Translation ready.
	load_theme_textdomain( 'weldman', WELDMAN_DIR . '/languages' );

	// Automatic feed links.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Featured images / thumbnails.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 675, true );
	add_image_size( 'weldman-card', 640, 420, true );
	add_image_size( 'weldman-hero', 1600, 900, true );

	// HTML5 markup for search form, comment form, gallery, caption, widgets.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	// Custom logo (Customizer) support in addition to ACF options logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Wide/full alignment support for block editor content (used inside post content only).
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'weldman' ),
			'footer'  => __( 'Footer Menu', 'weldman' ),
		)
	);
}
add_action( 'after_setup_theme', 'weldman_setup' );

/**
 * Content width for embeds/oEmbed.
 */
function weldman_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'weldman_content_width', 1140 );
}
add_action( 'after_setup_theme', 'weldman_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function weldman_scripts() {
	// Google Fonts — preconnect only, loaded via <link> in header with font-display swap.
	wp_enqueue_style( 'weldman-reset', WELDMAN_URI . '/assets/css/reset.css', array(), WELDMAN_VERSION );
	wp_enqueue_style( 'weldman-style', WELDMAN_URI . '/assets/css/style.css', array( 'weldman-reset' ), WELDMAN_VERSION );
	wp_enqueue_style( 'weldman-components', WELDMAN_URI . '/assets/css/components.css', array( 'weldman-style' ), WELDMAN_VERSION );
	wp_enqueue_style( 'weldman-responsive', WELDMAN_URI . '/assets/css/responsive.css', array( 'weldman-components' ), WELDMAN_VERSION );

	// Theme's stylesheet header (kept for WP compatibility, no real styles inside).
	wp_enqueue_style( 'weldman-theme', get_stylesheet_uri(), array(), WELDMAN_VERSION );

	wp_enqueue_script( 'weldman-main', WELDMAN_URI . '/assets/js/main.js', array(), WELDMAN_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'weldman_scripts' );

/**
 * Register widget areas.
 */
function weldman_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'weldman' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Displayed next to blog posts and archives.', 'weldman' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'weldman_widgets_init' );

/**
 * Performance: strip out unused WordPress front-end bloat.
 */
function weldman_disable_unused_features() {
	// Emoji scripts/styles.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'weldman_disable_emoji_tinymce' );
	add_filter( 'wp_resource_hints', 'weldman_remove_emoji_dns_prefetch', 10, 2 );

	// oEmbed discovery links / JS (we don't rely on auto-embeds for this small site).
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Misc head clutter that adds no value for a small brochure site.
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

	// Disable REST API links in head for logged-out visitors (keep the endpoint itself working).
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_resource_hints', 2 );
}
add_action( 'init', 'weldman_disable_unused_features' );

/**
 * Helper: strip the emoji TinyMCE plugin.
 *
 * @param array $plugins TinyMCE plugins.
 * @return array
 */
function weldman_disable_emoji_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
	return array();
}

/**
 * Helper: remove the emoji DNS prefetch resource hint.
 *
 * @param array  $urls          Resource hint URLs.
 * @param string $relation_type Relation type (dns-prefetch, preconnect...).
 * @return array
 */
function weldman_remove_emoji_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/' );
		foreach ( $urls as $key => $url ) {
			if ( is_string( $url ) && false !== strpos( $url, $emoji_svg_url ) ) {
				unset( $urls[ $key ] );
			}
		}
	}
	return $urls;
}

/**
 * Disable the block-library / global-styles CSS injected by core for classic themes
 * that don't use block templates (front-page/page/single still work fine without it,
 * saves ~30-60kb of unused CSS per request).
 */
function weldman_dequeue_block_assets() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'weldman_dequeue_block_assets', 20 );

/**
 * Preconnect to Google Fonts and load the font stylesheet non-blocking.
 */
function weldman_fonts_preconnect() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
	echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap"></noscript>' . "\n";
}
add_action( 'wp_head', 'weldman_fonts_preconnect', 1 );

/**
 * Add async/defer attributes to our own script tags to avoid blocking render.
 *
 * @param string $tag    The <script> tag.
 * @param string $handle The script handle.
 * @return string
 */
function weldman_script_defer( $tag, $handle ) {
	$defer_handles = array( 'weldman-main' );
	if ( in_array( $handle, $defer_handles, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'weldman_script_defer', 10, 2 );

/**
 * Register ACF Options Page ("Site Options" — contacts, socials, phones, address, footer).
 */
function weldman_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Site Options', 'weldman' ),
			'menu_title' => __( 'Site Options', 'weldman' ),
			'menu_slug'  => 'weldman-options',
			'capability' => 'edit_theme_options',
			'icon_url'   => 'dashicons-admin-generic',
			'redirect'   => false,
		)
	);
}
add_action( 'acf/init', 'weldman_acf_options_page' );

/**
 * Tell ACF where to look for / save local JSON field group exports.
 *
 * @param array $paths Existing save paths.
 * @return array
 */
function weldman_acf_json_save_point( $paths ) {
	unset( $paths[0] );
	$paths[] = WELDMAN_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/save_json', function () {
	return WELDMAN_DIR . '/acf-json';
} );
add_filter( 'acf/settings/load_json', 'weldman_acf_json_save_point' );

/**
 * Custom excerpt length for blog cards.
 *
 * @param int $length Default excerpt length.
 * @return int
 */
function weldman_excerpt_length( $length ) {
	return 26;
}
add_filter( 'excerpt_length', 'weldman_excerpt_length' );

/**
 * Custom excerpt "more" string.
 *
 * @return string
 */
function weldman_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'weldman_excerpt_more' );

/**
 * Show an admin notice if ACF is not installed/active, since the theme relies on it
 * for the homepage sections, contact page fields and site options.
 */
function weldman_acf_missing_notice() {
	if ( ! class_exists( 'ACF' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'The Weldman theme requires the Advanced Custom Fields (ACF) plugin (free or Pro) to manage homepage sections, the contact page and site-wide options. Please install and activate it.', 'weldman' ) .
			'</p></div>';
	}
}
add_action( 'admin_notices', 'weldman_acf_missing_notice' );

/**
 * Required includes.
 */
require WELDMAN_DIR . '/inc/template-functions.php';
require WELDMAN_DIR . '/inc/customizer.php';
require WELDMAN_DIR . '/inc/acf-fields.php';
require WELDMAN_DIR . '/inc/seo.php';
require WELDMAN_DIR . '/inc/contact-form.php';
