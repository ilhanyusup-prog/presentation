<?php
/**
 * ACF field group registration (code-based, versioned in git).
 *
 * The theme registers its ACF field groups directly in PHP with
 * acf_add_local_field_group() rather than relying solely on hand-edited
 * JSON exports. This keeps the field structure 100% in sync with the
 * template code that reads it (no risk of the JSON export drifting from
 * what front-page.php / template-parts actually expect) and needs zero
 * manual "click through wp-admin, then export" steps when standing up a
 * new environment.
 *
 * The acf-json/ directory is still wired up in functions.php
 * (acf/settings/save_json + acf/settings/load_json) so that if an editor
 * ever tweaks these groups from wp-admin > Custom Fields, ACF will write
 * the changes there automatically and they'll be picked up by
 * acf_add_local_field_group()'s local-JSON loader on the next deploy.
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
	weldman_register_home_sections_fields();
	weldman_register_site_options_fields();
	weldman_register_contact_page_fields();
	weldman_register_seo_fields();
}

/**
 * "Home / flexible sections" Flexible Content field.
 *
 * Attached to the site's configured front page (front-page.php) and to any
 * page using the generic page-templates/template-sections.php template
 * (e.g. Innovatsioon), so the same reusable layouts can build either.
 */
function weldman_register_home_sections_fields() {
	acf_add_local_field_group(
		array(
			'key'      => 'group_weldman_home_sections',
			'title'    => 'Page sections',
			'fields'   => array(
				array(
					'key'          => 'field_weldman_home_sections',
					'name'         => 'page_sections',
					'label'        => 'Sections',
					'type'         => 'flexible_content',
					'instructions' => 'Build the page by stacking sections. Drag to reorder.',
					'button_label' => 'Add section',
					'layouts'      => array(

						'layout_hero' => array(
							'key'    => 'layout_weldman_hero',
							'name'   => 'hero',
							'label'  => 'Hero',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_weldman_hero_title',
									'name'  => 'hero_title',
									'label' => 'Title',
									'type'  => 'text',
									'instructions' => 'Main H1 headline of the page.',
									'required' => 1,
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
									'label'         => 'Background / side image',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'medium',
								),
							),
						),

						'layout_text_block' => array(
							'key'     => 'layout_weldman_text_block',
							'name'    => 'text_block',
							'label'   => 'Text block with image',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_weldman_block_title',
									'name'  => 'block_title',
									'label' => 'Title',
									'type'  => 'text',
									'required' => 1,
								),
								array(
									'key'   => 'field_weldman_block_text',
									'name'  => 'block_text',
									'label' => 'Text',
									'type'  => 'wysiwyg',
									'tabs'  => 'text',
									'toolbar' => 'basic',
									'media_upload' => 0,
								),
								array(
									'key'           => 'field_weldman_block_image',
									'name'          => 'block_image',
									'label'         => 'Image',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'medium',
								),
								array(
									'key'     => 'field_weldman_image_position',
									'name'    => 'image_position',
									'label'   => 'Image position',
									'type'    => 'select',
									'choices' => array(
										'right' => 'Right',
										'left'  => 'Left',
									),
									'default_value' => 'right',
									'allow_null'    => 0,
									'ui'            => 1,
								),
							),
						),

						'layout_partners' => array(
							'key'     => 'layout_weldman_partners',
							'name'    => 'partners',
							'label'   => 'Partners / Logos',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_weldman_partners_title',
									'name'  => 'partners_title',
									'label' => 'Title',
									'type'  => 'text',
								),
								array(
									'key'          => 'field_weldman_partners',
									'name'         => 'partners',
									'label'        => 'Partners',
									'type'         => 'repeater',
									'layout'       => 'table',
									'button_label' => 'Add partner',
									'sub_fields'   => array(
										array(
											'key'           => 'field_weldman_partner_logo',
											'name'          => 'logo',
											'label'         => 'Logo',
											'type'          => 'image',
											'return_format' => 'array',
											'preview_size'  => 'thumbnail',
											'required'      => 1,
										),
										array(
											'key'   => 'field_weldman_partner_link',
											'name'  => 'link',
											'label' => 'Link (optional)',
											'type'  => 'url',
										),
									),
								),
							),
						),

						'layout_contact_form' => array(
							'key'     => 'layout_weldman_contact_form',
							'name'    => 'contact_form',
							'label'   => 'Contact form',
							'display' => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_weldman_form_title',
									'name'  => 'form_title',
									'label' => 'Title',
									'type'  => 'text',
									'default_value' => 'Kirjuta meile!',
								),
								array(
									'key'          => 'field_weldman_cf7_shortcode',
									'name'         => 'cf7_shortcode',
									'label'        => 'Contact Form 7 shortcode (optional)',
									'type'         => 'text',
									'instructions' => 'Leave empty to use the built-in lightweight contact form. Paste a Contact Form 7 shortcode (e.g. [contact-form-7 id="123" title="Contact"]) to use CF7 instead.',
								),
							),
						),

					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-templates/template-sections.php',
					),
				),
			),
			'menu_order' => 0,
		)
	);
}

/**
 * "Site Options" fields (Options Page) — reused by the footer and by the
 * Organization/LocalBusiness JSON-LD schema on every page.
 */
function weldman_register_site_options_fields() {
	acf_add_local_field_group(
		array(
			'key'    => 'group_weldman_site_options',
			'title'  => 'Contacts &amp; Social',
			'fields' => array(
				array(
					'key'   => 'field_weldman_company_address',
					'name'  => 'company_address',
					'label' => 'Company address',
					'type'  => 'text',
					'default_value' => 'Lennujaama tee 7, 11101 Tallinn',
				),
				array(
					'key'          => 'field_weldman_phone_numbers',
					'name'         => 'phone_numbers',
					'label'        => 'Phone numbers',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add phone number',
					'sub_fields'   => array(
						array(
							'key'   => 'field_weldman_phone_number',
							'name'  => 'phone',
							'label' => 'Phone',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'   => 'field_weldman_email',
					'name'  => 'email',
					'label' => 'Email',
					'type'  => 'email',
					'default_value' => 'info@weldman.ee',
				),
				array(
					'key'          => 'field_weldman_social_links',
					'name'         => 'social_links',
					'label'        => 'Social links',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add social link',
					'sub_fields'   => array(
						array(
							'key'     => 'field_weldman_social_platform',
							'name'    => 'platform',
							'label'   => 'Platform',
							'type'    => 'select',
							'choices' => array(
								'facebook'  => 'Facebook',
								'instagram' => 'Instagram',
								'tiktok'    => 'TikTok',
								'youtube'   => 'YouTube',
								'linkedin'  => 'LinkedIn',
								'whatsapp'  => 'WhatsApp',
							),
							'ui' => 1,
						),
						array(
							'key'   => 'field_weldman_social_url',
							'name'  => 'url',
							'label' => 'URL',
							'type'  => 'url',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'weldman-options',
					),
				),
			),
		)
	);
}

/**
 * Fields for the Kontakt page template (page-templates/template-contact.php).
 */
function weldman_register_contact_page_fields() {
	acf_add_local_field_group(
		array(
			'key'    => 'group_weldman_contact_page',
			'title'  => 'Contact page',
			'fields' => array(
				array(
					'key'   => 'field_weldman_contact_intro',
					'name'  => 'contact_intro',
					'label' => 'Intro text',
					'type'  => 'wysiwyg',
					'tabs'  => 'text',
					'toolbar' => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'          => 'field_weldman_contact_map_embed',
					'name'         => 'contact_map_embed',
					'label'        => 'Map embed URL (optional)',
					'type'         => 'url',
					'instructions' => 'Google Maps "Embed a map" iframe src URL. Leave empty to hide the map.',
				),
				array(
					'key'          => 'field_weldman_contact_form_title',
					'name'         => 'contact_form_title',
					'label'        => 'Contact form title',
					'type'         => 'text',
					'default_value' => 'Kirjuta meile!',
				),
				array(
					'key'          => 'field_weldman_contact_cf7_shortcode',
					'name'         => 'cf7_shortcode',
					'label'        => 'Contact Form 7 shortcode (optional)',
					'type'         => 'text',
					'instructions' => 'Leave empty to use the built-in lightweight contact form.',
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
 * Lightweight SEO fields (manual, no plugin) usable on posts and pages.
 */
function weldman_register_seo_fields() {
	acf_add_local_field_group(
		array(
			'key'    => 'group_weldman_seo',
			'title'  => 'SEO',
			'fields' => array(
				array(
					'key'          => 'field_weldman_meta_description',
					'name'         => 'meta_description',
					'label'        => 'Meta description',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'Shown in search results. Falls back to the excerpt/content if left empty. Aim for under 155 characters.',
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
