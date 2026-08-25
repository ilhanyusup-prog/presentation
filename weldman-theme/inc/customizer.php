<?php
/**
 * Minimal Customizer additions.
 *
 * Site-wide contact and social values live here instead of an ACF Options
 * Page, which is a Pro-only feature. Every setting uses WordPress core and
 * therefore works with ACF Free.
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

	$wp_customize->add_section(
		'weldman_contact',
		array(
			'title'       => __( 'Weldman Contact & Social', 'weldman' ),
			'description' => __( 'Site-wide details used in the footer, contact page, forms and structured data.', 'weldman' ),
			'priority'    => 31,
		)
	);

	$text_settings = array(
		'weldman_company_address' => array(
			'label'    => __( 'Company address', 'weldman' ),
			'default'  => 'Lennujaama tee 7, 11101 Tallinn',
			'sanitize' => 'sanitize_text_field',
		),
		'weldman_phone_1' => array(
			'label'    => __( 'Phone 1', 'weldman' ),
			'default'  => '',
			'sanitize' => 'sanitize_text_field',
		),
		'weldman_phone_2' => array(
			'label'    => __( 'Phone 2', 'weldman' ),
			'default'  => '',
			'sanitize' => 'sanitize_text_field',
		),
		'weldman_email' => array(
			'label'    => __( 'Email', 'weldman' ),
			'default'  => 'info@weldman.ee',
			'sanitize' => 'sanitize_email',
		),
	);

	foreach ( $text_settings as $setting_id => $setting ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $setting['default'],
				'sanitize_callback' => $setting['sanitize'],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $setting['label'],
				'section' => 'weldman_contact',
				'type'    => 'text',
			)
		);
	}

	$social_platforms = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
		'linkedin'  => 'LinkedIn',
		'whatsapp'  => 'WhatsApp',
	);

	foreach ( $social_platforms as $platform => $label ) {
		$setting_id = 'weldman_social_' . $platform;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'       => sprintf( /* translators: %s: social network name. */ __( '%s URL', 'weldman' ), $label ),
				'section'     => 'weldman_contact',
				'type'        => 'url',
				'input_attrs' => array(
					'placeholder' => 'https://',
				),
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
