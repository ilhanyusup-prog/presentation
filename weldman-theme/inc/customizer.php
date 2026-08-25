<?php
/**
 * Minimal Customizer additions.
 *
 * The theme intentionally keeps most editable content in ACF (Options Page
 * + Flexible Content) since that's where the client already edits copy.
 * The Customizer here only exposes a couple of brand-level toggles that
 * don't belong in page content.
 *
 * @package Weldman
 */

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function weldman_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'weldman_brand',
		array(
			'title'    => __( 'Weldman Brand', 'weldman' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'weldman_primary_color',
		array(
			'default'           => '#1a1450',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);

	if ( class_exists( 'WP_Customize_Color_Control' ) ) {
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'weldman_primary_color',
				array(
					'label'   => __( 'Primary (brand) color', 'weldman' ),
					'section' => 'weldman_brand',
				)
			)
		);
	}

	$wp_customize->add_setting(
		'weldman_accent_color',
		array(
			'default'           => '#7a1fa2',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);

	if ( class_exists( 'WP_Customize_Color_Control' ) ) {
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'weldman_accent_color',
				array(
					'label'   => __( 'Accent color', 'weldman' ),
					'section' => 'weldman_brand',
				)
			)
		);
	}
}
add_action( 'customize_register', 'weldman_customize_register' );

/**
 * Output brand colors as CSS custom properties so the Customizer values
 * override the defaults set in assets/css/style.css.
 */
function weldman_customizer_css() {
	$primary = get_theme_mod( 'weldman_primary_color', '#1a1450' );
	$accent  = get_theme_mod( 'weldman_accent_color', '#7a1fa2' );

	if ( '#1a1450' === $primary && '#7a1fa2' === $accent ) {
		return;
	}

	printf(
		'<style id="weldman-customizer-css">:root{--color-primary:%s;--color-accent:%s;}</style>',
		esc_attr( $primary ),
		esc_attr( $accent )
	);
}
add_action( 'wp_head', 'weldman_customizer_css', 20 );
