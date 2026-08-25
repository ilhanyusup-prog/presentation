<?php
/**
 * ACF Free field groups.
 *
 * Only field types included in the free Advanced Custom Fields plugin are
 * used here. The homepage has a fixed section order, so each section gets
 * its own prefixed fields instead of Flexible Content. Repeating values are
 * represented by fixed slots instead of Repeater fields.
 *
 * @package Weldman
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

add_action( 'acf/init', 'weldman_register_field_groups' );

/**
 * Register all field groups used by the theme.
 */
function weldman_register_field_groups() {
	weldman_register_home_fields();
	weldman_register_contact_page_fields();
	weldman_register_seo_fields();
}

/**
 * Build a tab field used to separate fixed homepage sections in wp-admin.
 *
 * @param string $key   Unique key suffix.
 * @param string $label Tab label.
 * @return array
 */
function weldman_acf_tab( $key, $label ) {
	return array(
		'key'       => 'field_weldman_tab_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/**
 * Build the common fields for Mission, Innovation and Quality.
 *
 * @param string $prefix Field name prefix.
 * @param string $label  Admin label.
 * @param string $default_position Default image position.
 * @return array
 */
function weldman_acf_text_section_fields( $prefix, $label, $default_position = 'right' ) {
	return array(
		weldman_acf_tab( $prefix, $label ),
		array(
			'key'      => 'field_weldman_' . $prefix . '_title',
			'name'     => $prefix . '_title',
			'label'    => 'Title',
			'type'     => 'text',
			'required' => 1,
		),
		array(
			'key'          => 'field_weldman_' . $prefix . '_text',
			'name'         => $prefix . '_text',
			'label'        => 'Text',
			'type'         => 'wysiwyg',
			'tabs'         => 'all',
			'toolbar'      => 'basic',
			'media_upload' => 0,
		),
		array(
			'key'           => 'field_weldman_' . $prefix . '_image',
			'name'          => $prefix . '_image',
			'label'         => 'Image',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'medium',
		),
		array(
			'key'           => 'field_weldman_' . $prefix . '_image_position',
			'name'          => $prefix . '_image_position',
			'label'         => 'Image position',
			'type'          => 'select',
			'choices'       => array(
				'left'  => 'Left',
				'right' => 'Right',
			),
			'default_value' => $default_position,
			'ui'            => 0,
		),
	);
}

/**
 * Fixed homepage fields. The front-page template controls section order:
 * Hero → Mission → Innovation → Quality → Partners → Contact form.
 */
function weldman_register_home_fields() {
	$fields = array(
		weldman_acf_tab( 'hero', 'Hero' ),
		array(
			'key'          => 'field_weldman_hero_title',
			'name'         => 'hero_title',
			'label'        => 'Title',
			'type'         => 'text',
			'instructions' => 'Main H1 headline.',
			'required'     => 1,
		),
		array(
			'key'   => 'field_weldman_hero_subtitle',
			'name'  => 'hero_subtitle',
			'label' => 'Subtitle',
			'type'  => 'textarea',
			'rows'  => 2,
		),
		array(
			'key'   => 'field_weldman_hero_quote_text',
			'name'  => 'hero_quote_text',
			'label' => 'Quote text',
			'type'  => 'textarea',
			'rows'  => 3,
		),
		array(
			'key'   => 'field_weldman_hero_quote_author',
			'name'  => 'hero_quote_author',
			'label' => 'Quote author',
			'type'  => 'text',
		),
		array(
			'key'           => 'field_weldman_hero_image',
			'name'          => 'hero_image',
			'label'         => 'Hero image',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'medium',
		),
	);

	$fields = array_merge(
		$fields,
		weldman_acf_text_section_fields( 'mission', 'Mission', 'right' ),
		weldman_acf_text_section_fields( 'innovation', 'Innovation', 'left' ),
		weldman_acf_text_section_fields( 'quality', 'Quality', 'right' )
	);

	$fields[] = weldman_acf_tab( 'partners', 'Partners / Logos' );
	$fields[] = array(
		'key'   => 'field_weldman_partners_title',
		'name'  => 'partners_title',
		'label' => 'Title',
		'type'  => 'text',
	);

	for ( $i = 1; $i <= 6; $i++ ) {
		$fields[] = array(
			'key'           => 'field_weldman_partner_' . $i . '_logo',
			'name'          => 'partner_' . $i . '_logo',
			'label'         => sprintf( 'Partner %d logo', $i ),
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'thumbnail',
		);
		$fields[] = array(
			'key'   => 'field_weldman_partner_' . $i . '_link',
			'name'  => 'partner_' . $i . '_link',
			'label' => sprintf( 'Partner %d link', $i ),
			'type'  => 'url',
		);
	}

	$fields[] = weldman_acf_tab( 'contact_form', 'Contact form' );
	$fields[] = array(
		'key'           => 'field_weldman_form_title',
		'name'          => 'form_title',
		'label'         => 'Title',
		'type'          => 'text',
		'default_value' => 'Kirjuta meile!',
	);
	$fields[] = array(
		'key'          => 'field_weldman_cf7_shortcode',
		'name'         => 'cf7_shortcode',
		'label'        => 'Contact Form 7 shortcode (optional)',
		'type'         => 'text',
		'instructions' => 'Leave empty to use the built-in form.',
	);

	acf_add_local_field_group(
		array(
			'key'        => 'group_weldman_home',
			'title'      => 'Homepage sections',
			'fields'     => $fields,
			'location'   => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'menu_order' => 0,
		)
	);
}

/**
 * Fields for the Kontakt page template.
 */
function weldman_register_contact_page_fields() {
	acf_add_local_field_group(
		array(
			'key'    => 'group_weldman_contact_page',
			'title'  => 'Contact page',
			'fields' => array(
				array(
					'key'          => 'field_weldman_contact_intro',
					'name'         => 'contact_intro',
					'label'        => 'Intro text',
					'type'         => 'wysiwyg',
					'tabs'         => 'all',
					'toolbar'      => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'          => 'field_weldman_contact_map_embed',
					'name'         => 'contact_map_embed',
					'label'        => 'Map embed URL (optional)',
					'type'         => 'url',
					'instructions' => 'Google Maps iframe src URL.',
				),
				array(
					'key'           => 'field_weldman_contact_form_title',
					'name'          => 'contact_form_title',
					'label'         => 'Contact form title',
					'type'          => 'text',
					'default_value' => 'Kirjuta meile!',
				),
				array(
					'key'          => 'field_weldman_contact_cf7_shortcode',
					'name'         => 'cf7_shortcode',
					'label'        => 'Contact Form 7 shortcode (optional)',
					'type'         => 'text',
					'instructions' => 'Leave empty to use the built-in form.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-templates/template-contact.php',
					),
				),
			),
		)
	);
}

/**
 * Lightweight SEO field usable on posts and pages.
 */
function weldman_register_seo_fields() {
	acf_add_local_field_group(
		array(
			'key'      => 'group_weldman_seo',
			'title'    => 'SEO',
			'fields'   => array(
				array(
					'key'          => 'field_weldman_meta_description',
					'name'         => 'meta_description',
					'label'        => 'Meta description',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'Falls back to the excerpt/content. Aim for under 155 characters.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'position' => 'side',
		)
	);
}
