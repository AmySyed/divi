<?php

class DIPE_CF7_Styler extends ET_Builder_Module {

	protected $module_credits = array(
		'module_uri' => 'https://divipeople.com/plugins/contact-form-7-styler/',
		'author'     => 'Divi People',
		'author_uri' => 'https://divipeople.com',
	);

	public function init() {
		$this->vb_support = 'on';
		$this->slug       = 'dvppl_cf7_styler';
		$this->name       = esc_html__( 'Contact Form 7 Styler', 'dvppl-cf7-styler' );
		$this->icon_path  = plugin_dir_path( __FILE__ ) . 'cf7.svg';
		$this->main_css_element = '%%order_class%%';

		$this->settings_modal_toggles = array(
			'general'  => array(
				'toggles' => array(
					'general' => esc_html__( 'General', 'dvppl-cf7-styler' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'common'     => esc_html__( 'Common', 'dvppl-cf7-styler' ),
					'form_header' => [
						'title' =>	esc_html( 'Form Header', 'dvppl-cf7-styler' ),
						'tabbed_subtoggles'	=>	true,
						'sub_toggles' => [
							'common_tab' =>	[
								'name' =>	esc_html( 'Common', 'dvppl-cf7-styler' )
							],
							'title_tab' =>	[
								'name' =>	esc_html( 'Title', 'dvppl-cf7-styler' )
							],
							'text_tab' => [
								'name' => esc_html( 'Text', 'dvppl-cf7-styler' )
							],
						]
					],
					'form_text' => [
						'title' =>	esc_html( 'Form Text', 'dvppl-cf7-styler' ),
						'tabbed_subtoggles'	=>	true,
						'sub_toggles' => [
							'field_tab' =>	[
								'name' =>	esc_html( 'Field', 'dvppl-cf7-styler' )
							],
							'label_tab' => [
								'name' => esc_html( 'Label', 'dvppl-cf7-styler' )
							],

							'placeholder_tab' => [
								'name' => esc_html( 'Placeholder', 'dvppl-cf7-styler' )
							],
						]
					],
					'form_field'     => esc_html__('Fields', 'dvppl-cf7-styler' ),
					'radio_checkbox' => esc_html__('Radio & Checkbox', 'dvppl-cf7-styler'),
					'submit_button'  => esc_html__('Button', 'dvppl-cf7-styler' ),
					'suc_err_msg'    => esc_html__('Message', 'dvppl-cf7-styler' )
				),
			),
		);

		$this->custom_css_fields = array(
			'cf7_fields' => array(
				'label'    => esc_html__( 'Form Fields', 'dvppl-cf7-styler' ),
				'selector' => '%%order_class%% .dipe-cf7-styler input',
			),
			'cf7_labels' => array(
				'label'    => esc_html__( 'Form Label', 'dvppl-cf7-styler' ),
				'selector' => '%%order_class%% .dipe-cf7-styler label',
			),
		);
	}

	public static function select_wpcf7() {

		if ( function_exists( 'wpcf7' ) ) {
			$options = [];
			$args = [
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1
			];
			$contact_forms = get_posts( $args );
			
			if (!empty( $contact_forms ) && ! is_wp_error( $contact_forms ) ) {
				$i = 0;
				foreach ( $contact_forms as $post ) {	
					if ( $i == 0 ) {
						(int)$options[0] = esc_html__( 'Select a Contact form', 'dvppl-cf7-styler' );
					}
					(int)$options[ $post->ID ] = $post->post_title;
					$i++;
				}
			}

		} else {
			$options = array();
		}

		return $options;
	}

	public function get_fields() {

		return array(

			'use_form_header' => array(
				'label'   => esc_html__('Show Form Header', 'dvppl-cf7-styler' ),
				'type'    => 'yes_no_button',
				'options' => array(
					'on'  => esc_html__( 'Yes', 'dvppl-cf7-styler' ),
					'off' => esc_html__( 'No', 'dvppl-cf7-styler' ),
				),
				'default' => 'off',
				'toggle_slug' => 'general',
				'affects' => array(
					'title_font',
					'title_text_color',
					'title_line_height',
					'title_font_size',
					'title_all_caps',
					'title_letter_spacing',
					'title_text_shadow',
					'text_font',
					'text_text_color',
					'text_line_height',
					'text_font_size',
					'text_all_caps',
					'text_letter_spacing',
					'text_text_shadow'
				),
			),

			'form_header_title' => [
				'label'   => esc_html__('Header Title', 'dvppl-cf7-styler' ),
				'type'    => 'text',
				'show_if' => [
					'use_form_header' => 'on'
				],
				'toggle_slug' => 'general'
            ],

            'form_header_text' => [
				'label'   => esc_html__('Header Text', 'dvppl-cf7-styler' ),
				'type'    => 'text',
				'show_if' => [
					'use_form_header' => 'on'
				],
				'toggle_slug' => 'general'
            ],

            'use_icon' => [
				'label'          	 	=> esc_html__( 'Use Icon', 'dvppl-cf7-styler' ),
				'type'             		=> 'yes_no_button',
				'options'          		=> [
					'on'  => esc_html__( 'Yes', 'dvppl-cf7-styler' ),
					'off' => esc_html__( 'No', 'dvppl-cf7-styler' ),
				],
				'show_if' => [
					'use_form_header' => 'on'
				],
				'default'     => 'off',
				'toggle_slug' => 'general'
            ],

			'header_img' => [
				'label'              => esc_html__( 'Header Image', 'dvppl-cf7-styler' ),
				'type'               => 'upload',
				'upload_button_text' => esc_attr__( 'Upload an image', 'dvppl-cf7-styler' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'dvppl-cf7-styler' ),
				'update_text'        => esc_attr__( 'Set As Image', 'dvppl-cf7-styler' ),
				'show_if' => [
					'use_icon' => 'off',
					'use_form_header' => 'on'
				],
				'toggle_slug' => 'general'
            ],

            'header_icon' => [
				'label' => esc_html__( 'Header Icon', 'dvppl-cf7-styler' ),
				'type' => 'select_icon',
				'show_if' => [
					'use_form_header' => 'on',
					'use_icon' => 'on'
				],
				'default' => '',
				'toggle_slug' => 'general'
            ],

            'form_header_bg' => [
				'label'        => esc_html__('Form Header Background', 'dvppl-cf7-styler' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if' => [
					'use_form_header' => 'on',
				],
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab'
			],

			'form_header_padding' => [
				'label'          => esc_html__( 'Header Padding', 'dvppl-cf7-styler' ),
				'type'           => 'custom_padding',
				'default'        => '0px|0px|0px|0px',
				'show_if' => [
					'use_form_header' => 'on',
				],
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'form_header',
				'sub_toggle'     => 'common_tab',
				'mobile_options' => true
            ],

			'form_header_bottom' => [
				'label'          => esc_html__('Bottom Spacing', 'dvppl-cf7-styler' ),
				'type'           => 'range',
				'default_unit'   => 'px',
				'default'        => '0px',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'show_if' => [
					'use_form_header' => 'on',
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'form_header',
				'sub_toggle'  => 'common_tab'
			],

            'form_header_img_bg' => [
				'label'        => esc_html__('Header Image/Icon Background', 'dvppl-cf7-styler' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if' => [
					'use_form_header' => 'on',
				],
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab'
			],

			'form_header_icon_color' => [
				'label'        => esc_html__('Header Icon Color', 'dvppl-cf7-styler' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'show_if' => [
					'use_form_header' => 'on',
					'use_icon' => 'on'
				],
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'form_header',
				'sub_toggle'   => 'common_tab'
			],

			'form_bg' => [
				'label'        => esc_html__('Form Background', 'dvppl-cf7-styler' ),
				'type'         => 'color-alpha',
				'custom_color' => true,
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'common'
			],

			'form_padding' => [
				'label'          => esc_html__( 'Form Padding', 'dvppl-cf7-styler' ),
				'type'           => 'custom_padding',
				'default'        => '0px|0px|0px|0px',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'common',
				'mobile_options' => true,
            ],

            'use_form_button_fullwidth' => array(
				'label'   => esc_html__('Fullwidth Button', 'dvppl-cf7-styler' ),
				'type'    => 'yes_no_button',
				'options' => array(
					'on'  => esc_html__( 'Yes', 'dvppl-cf7-styler' ),
					'off' => esc_html__( 'No', 'dvppl-cf7-styler' ),
				),
				'default'     => 'off',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'common',
			),

			'button_alignment' => [
				'label'   => esc_html__( 'Button Alignment', 'dvppl-cf7-styler' ),
				'type'    => 'select',
				'options' => [
					'left'   => esc_html__('Left', 'dvppl-cf7-styler'),
					'center' => esc_html__('Center', 'dvppl-cf7-styler'),
					'right'  => esc_html__('Right', 'dvppl-cf7-styler')
				],
				'show_if' => [
					'use_form_button_fullwidth' => 'off'
				],
				'default'     => 'left',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'common',
            ],

			'cf7' => array(
				'label'            => esc_html__('Select Form', 'dvppl-cf7-styler' ),
				'type'             => 'select',
				'option_category'  => 'layout',
				'options'          => self::select_wpcf7(),
				'description'      => esc_html__('Choose a contact form to display.', 'dvppl-cf7-styler' ),
				'computed_affects' => array(
					'__cf7form',
				),
				'toggle_slug'      => 'general',
			),

			'form_background_color' => array(
				'label'             => esc_html__('Form Field Background Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'default'          	=> '#f5f5f5',
				'toggle_slug'       => 'form_field',
				'tab_slug'          => 'advanced',
			),

			'form_field_active_color' => array(
				'label'             => esc_html__('Form Field Active Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'form_field',
			),

			'cr_custom_styles' => array(
				'label'          	 => esc_html__('Enable Custom Styles', 'dvppl-cf7-styler' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => array(
					'on'  => esc_html__( 'Yes', 'dvppl-cf7-styler' ),
					'off' => esc_html__( 'No', 'dvppl-cf7-styler' ),
				),
				'default'          => 'off',
				'computed_affects' => array(
					'__cf7form',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'radio_checkbox',
			),

			'cr_size' => array(
				'label'           => esc_html__('Size', 'dvppl-cf7-styler' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'default_unit'    => 'px',
				'default'         => '20px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'radio_checkbox',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				)
			),

			'cr_background_color' => array(
				'label'             => esc_html__('Background Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'radio_checkbox',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_selected_color' => array(
				'label'             => esc_html__('Selected Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'default'          	=> '#222222',
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'radio_checkbox',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_border_color' => array(
				'label'             => esc_html__('Border Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'default'          	=> '#222222',
				'toggle_slug'       => 'radio_checkbox',
				'tab_slug'          => 'advanced',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_border_size' => array(
				'label'           => esc_html__('Border Size', 'dvppl-cf7-styler' ),
				'type'            => 'range',
				'option_category' => 'layout',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'radio_checkbox',
				'default_unit'    => 'px',
				'default'         => '1px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '5',
					'step' => '1',
				),
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			'cr_label_color' => array(
				'label'             => esc_html__('Label Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'radio_checkbox',
				'show_if'         => array(
					'cr_custom_styles' => 'on',
				),
			),

			// Success / Error Message
			'cf7_message_color' => array(
				'label'             => esc_html__('Message Text Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_message_bg_color' => array(
				'label'             => esc_html__('Message Background Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_border_highlight_color' => array(
				'label'             => esc_html__('Border Highlight Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			// Success
			'cf7_success_message_color' => array(
				'label'             => esc_html__('Success Message Text Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_success_message_bg_color' => array(
				'label'             => esc_html__('Success Message Background Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_success_border_color' => array(
				'label'             => esc_html__('Success Border Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),
			
			// Error
			'cf7_error_message_color' => array(
				'label'             => esc_html__('Error Message Text Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_error_message_bg_color' => array(
				'label'             => esc_html__('Error Message Background Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_error_border_color' => array(
				'label'             => esc_html__('Error Border Color', 'dvppl-cf7-styler' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'suc_err_msg',
			),

			'cf7_message_padding' => array(
				'label'           => esc_html__('Message Padding', 'dvppl-cf7-styler' ),
				'type'            => 'range',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'suc_err_msg',
				'default_unit'    => 'px',
				'default'         => '0px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
			),

			'cf7_message_margin_top' => array(
				'label'           => esc_html__('Message Margin Top', 'dvppl-cf7-styler' ),
				'type'            => 'range',
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'suc_err_msg',
				'default_unit'    => 'px',
				'default'         => '0px',
				'range_settings'  => array(
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				),
			),
			
			'__cf7form' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'DIPE_CF7_Styler', 'get_cf7_shortcode_html' ),
				'computed_depends_on' => array(
					'cf7'
				),
			),
		);
	}

	public function get_advanced_fields_config() {

		$advanced_fields = [];
		$advanced_fields['fonts'] = false;
		$advanced_fields['text'] = false;
		$advanced_fields['text_shadow'] = false;

		$advanced_fields['fonts']['form_field_font'] = array(
			'label' => esc_html__( 'Field', 'dvppl-cf7-styler'),
			'css' => array(
				'main' => implode( ', ', array(
						"{$this->main_css_element} .dipe-cf7 .wpcf7 input:not([type=submit])",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 input::placeholder",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 select",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea",
						"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea::placeholder",
					) ),
				'important' => array(
					'font',
					'size',
					'letter-spacing',
					'line-height',
					'text-align',
					'all_caps',
				),
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'field_tab'
		);

		$advanced_fields['fonts']['labels'] = array(
			'label' => esc_html__( 'Label', 'dvppl-cf7-styler' ),
			'css' => array(
				'main' => "{$this->main_css_element} .dipe-cf7 .wpcf7 label",
				'important' => 'all'
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'label_tab'
		);

		$advanced_fields['fonts']['placeholder'] = array(
			'label' => esc_html__( 'Placeholder', 'dvppl-cf7-styler' ),
			'css' => array(
				'main' => implode( ', ', array(
					"{$this->main_css_element} .dipe-cf7 .wpcf7 input::placeholder",
					"{$this->main_css_element} .dipe-cf7 .wpcf7 textarea::placeholder",
				) ),
				'important' => 'all'
			),
			'toggle_slug' => 'form_text',
			'sub_toggle'  => 'placeholder_tab'
		);

		$advanced_fields['fonts']['title'] = array(
			'label' => esc_html__( 'Title', 'dvppl-cf7-styler'),
			'css'   => array(
				'main' => "%%order_class%% .dipe-form-header-title",
				'important' => 'all'
			),
			'depends_show_if' => 'on',
			'hide_text_align' => true,
			'hide_text_shadow' => true,
			'toggle_slug' => 'form_header',
			'sub_toggle'  => 'title_tab'
		);

		$advanced_fields['fonts']['text'] = array(
			'label' => esc_html__( 'Text', 'dvppl-cf7-styler'),
			'css'   => array(
				'main' => "%%order_class%% .dipe-form-header-text",
				'important' => 'all'
			),
			'depends_show_if' => 'on',
			'hide_text_align' => true,
			'hide_text_shadow' => true,
			'toggle_slug' => 'form_header',
			'sub_toggle'  => 'text_tab'
		);

		$advanced_fields['button']['submit_button'] = array(
			'label' => esc_html__( 'Button', 'dvppl-cf7-styler' ),
			'css' => [
				'main'	=> "%%order_class%% .wpcf7-form input[type=submit]",
				'important'   => 'all',
			],
			'box_shadow'  => [
                'css' => [
                    'main' => "%%order_class%% .wpcf7-form input[type=submit]",
                ],
            ],
            'margin_padding' => [
                'css' => [
                    'main' => "%%order_class%% .wpcf7-form input[type=submit]",
                    'important' => 'all',
                ],
            ],
			'toggle_slug' => 'submit_button',
            'hide_icon' => true,
            'use_alignment' => false
		);

		$advanced_fields['borders']['default'] = array();

		$advanced_fields['borders']['field'] = array(
			'label_prefix' => esc_html__( 'Field', 'dvppl-cf7-styler' ),
			'toggle_slug'  => 'form_field',
			'css'          => array(
				'main'      => array(
					'border_radii'  => sprintf('
						%1$s .dipe-cf7-styler .wpcf7 input:not([type=submit]),
						%1$s .dipe-cf7-styler .wpcf7 input[type=email],
						%1$s .dipe-cf7-styler .wpcf7 input[type=text],
						%1$s .dipe-cf7-styler .wpcf7 input[type=url],
						%1$s .dipe-cf7-styler .wpcf7 input[type=tel],
						%1$s .dipe-cf7-styler .wpcf7 input[type=date],
						%1$s .dipe-cf7-styler .wpcf7 select,
						%1$s .dipe-cf7-styler .wpcf7 textarea',
						$this->main_css_element
					),

					'border_styles' => sprintf('
						%1$s .dipe-cf7-styler .wpcf7 input:not([type=submit]),
						%1$s .dipe-cf7-styler .wpcf7 input[type=email],
						%1$s .dipe-cf7-styler .wpcf7 input[type=text],
						%1$s .dipe-cf7-styler .wpcf7 input[type=url],
						%1$s .dipe-cf7-styler .wpcf7 input[type=tel],
						%1$s .dipe-cf7-styler .wpcf7 input[type=date],
						%1$s .dipe-cf7-styler .wpcf7 select, 
						%1$s .dipe-cf7-styler .wpcf7 textarea
						', 
						$this->main_css_element 
					),
				),

				'important' => 'all',
			),
		);

		return $advanced_fields;
	}

	function get_cf7_shortcode( $args = [] ) {

		$cf7_id = $this->props['cf7'];

		$cf7_shortcode = '';

		if( 0 == $cf7_id ) {
			$cf7_shortcode = 'Please select a Contact Form 7.';
		} else {
			$cf7_shortcode = do_shortcode( sprintf( '[contact-form-7 id="%1$s"]', $cf7_id ) );
		}
		return $cf7_shortcode;
	}

	static function get_cf7_shortcode_html( $args = [] ) {

		$cf7_shortcode        = new self();
		$cf7_shortcode->props = $args;
		$output               = $cf7_shortcode->get_cf7_shortcode( [] );
		return $output;
	}

	public function render( $attrs, $content = null, $render_slug ) {

		$this->_apply_css( $render_slug );

		$cf7_fields        = $this->props['cf7'];
		$cr_custom_styles  = $this->props['cr_custom_styles'];
		$use_form_header   = $this->props['use_form_header'];
		$form_header_title = $this->props['form_header_title'];
		$form_header_text  = $this->props['form_header_text'];
		$use_form_button_fullwidth = $this->props['use_form_button_fullwidth'];
		$button_alignment  = $this->props['button_alignment'];

		$form_header = '';

		if('on' == $use_form_header ) {

			$header_img  = $this->props['header_img'] !== '' ? $this->props['header_img'] : false;
			$image       = $header_img ? sprintf('<div class="dipe-form-header-image"><img src="%1$s" alt=""/></div>', $header_img ) : '';
			$header_icon = esc_attr( et_pb_process_font_icon( $this->props['header_icon'] ) );
			$icon        = sprintf( '<div class="dipe-form-header-icon"> <span class="et-pb-icon">%1$s</span> </div> ', $header_icon);
			$icon_image  = 'on' === $this->props['use_icon'] ? $icon : $image;
			$title       = isset($form_header_title) ? sprintf( '<h2 class="dipe-form-header-title">%1$s</h2>', $form_header_title ) : '';
			$text        = isset($form_header_text) ? sprintf('<div class="dipe-form-header-text">%1$s</div>', $form_header_text) : '';
			$header_info = $title || $text ? sprintf('<div class="dipe-form-header-info">%1$s%2$s</div>', $title, $text) : '';

			$form_header = sprintf('
            	<div class="dipe-form-header-container">
                	<div class="dipe-form-header">
                		%1$s%2$s
                	</div>
                </div>', $icon_image, $header_info);
        }

		$cr_custom_class  = 'on' == $cr_custom_styles ? 'dipe-cf7-cr' : '';

		return sprintf( '
			<div class="dipe-cf7-container dipe-cf7-button-%4$s">
				%3$s
				<div class="dipe-cf7 dipe-cf7-styler %2$s">
					%1$s
				</div>
			</div>
			',
			$this->get_cf7_shortcode([]),
			$cr_custom_class,
			$form_header,
			'on' !== $use_form_button_fullwidth ? $button_alignment : 'fullwidth'
		);
	}

	public function _apply_css( $render_slug ) {

		$this->_render_header_css( $render_slug );
		$this->_render_form_header_padding( $render_slug );
		$this->_render_form_padding( $render_slug );

		$form_background_color        = $this->props['form_background_color'];
		$form_background_color_hover  = $this->get_hover_value( 'form_background_color' );
		$form_field_active_color      = $this->props['form_field_active_color'];
		$cr_custom_styles             = $this->props['cr_custom_styles'];
		$cr_size                      = $this->props['cr_size'];
		$cr_border_size               = $this->props['cr_border_size'];
		$cr_background_color          = $this->props['cr_background_color'];
		$cr_selected_color            = $this->props['cr_selected_color'];
		$cr_border_color              = $this->props['cr_border_color'];
		$cr_label_color               = $this->props['cr_label_color'];
		$cf7_message_color            = $this->props['cf7_message_color'];
		$cf7_message_bg_color         = $this->props['cf7_message_bg_color'];
		$cf7_border_highlight_color   = $this->props['cf7_border_highlight_color'];
		$cf7_success_message_color    = $this->props['cf7_success_message_color'];
		$cf7_success_message_bg_color = $this->props['cf7_success_message_bg_color'];
		$cf7_success_border_color     = $this->props['cf7_success_border_color'];
		$cf7_error_message_color      = $this->props['cf7_error_message_color'];
		$cf7_error_message_bg_color   = $this->props['cf7_error_message_bg_color'];
		$cf7_error_border_color       = $this->props['cf7_error_border_color'];
		$cf7_message_padding          = $this->props['cf7_message_padding'];
		$cf7_message_margin_top       = $this->props['cf7_message_margin_top'];
		$use_form_button_fullwidth    = $this->props['use_form_button_fullwidth'];

		if ( 'on' == $use_form_button_fullwidth ) {
			ET_Builder_Element::set_style($render_slug,[
				'selector'    => '%%order_class%% .dipe-cf7 .wpcf7 input[type=submit], %%order_class%% .wpcf7-form button.wpcf7-submit',
				'declaration' => "width: 100% !important;"
			]);
		}

		if ( '' !== $form_background_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 input:not([type=submit]), %%order_class%% .dipe-cf7 select, %%order_class%% .dipe-cf7 textarea, %%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before',

				'declaration' => sprintf(
					'background-color: %1$s%2$s;',
					esc_html( $form_background_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $form_field_active_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 .wpcf7 input:not([type=submit]):focus, %%order_class%% .dipe-cf7 .wpcf7 select:focus, %%order_class%% .dipe-cf7 .wpcf7 textarea:focus',
				'declaration' => sprintf(
					'border-color: %1$s%2$s;',
					esc_html( $form_field_active_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if('on' == $cr_custom_styles ) {

			if ( '' !== $cr_size || '' !== $cr_border_size ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"] + span:before',
					'declaration' => sprintf(
						'width: %1$s%2$s; height: %1$s%2$s; border-width:%3$s%2$s;',
						esc_html( $cr_size ),
						et_is_builder_plugin_active() ? ' !important' : '',
						esc_html( $cr_border_size )
					),
				) );
			}

			if ( '' !== $cr_size && is_numeric($cr_size) ) {
				$font_size = $cr_size / 1.2;
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-acceptance input[type=checkbox]:checked + span:before, %%order_class%% .dipe-cf7 .wpcf7-checkbox input[type=checkbox]:checked + span:before',
					'declaration' => sprintf(
						'font-size: ',
						esc_html( $font_size ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_background_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:not(:checked) + span:before',
					'declaration' => sprintf(
						'background-color: %1$s%2$s;',
						esc_html( $cr_background_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_background_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:checked + span:before',
					'declaration' => sprintf(
						'box-shadow:inset 0px 0px 0px 4px %1$s%2$s;',
						esc_html( $cr_background_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_selected_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type="checkbox"]:checked + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"]:checked + span:before',
					'declaration' => sprintf(
						'color: %1$s%2$s;',
						esc_html( $cr_selected_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_selected_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-radio input[type="radio"]:checked + span:before',
					'declaration' => sprintf(
						'background-color: %1$s%2$s;',
						esc_html( $cr_selected_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_border_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox input[type=radio] + span:before, %%order_class%% .dipe-cf7 .wpcf7-radio input[type=checkbox] + span:before, %%order_class%% .dipe-cf7 .wpcf7-acceptance input[type="checkbox"] + span:before',
					'declaration' => sprintf(
						'border-color: %1$s%2$s;',
						esc_html( $cr_border_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

			if ( '' !== $cr_label_color ) {
				ET_Builder_Element::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-checkbox label, %%order_class%% .wpcf7-radio label',
					'declaration' => sprintf(
						'color: %1$s%2$s;',
						esc_html( $cr_label_color ),
						et_is_builder_plugin_active() ? ' !important' : ''
					),
				) );
			}

		}

		if ( '' !== $cf7_message_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'color: %1$s%2$s;',
					esc_html( $cf7_message_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_message_bg_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'background-color: %1$s%2$s;',
					esc_html( $cf7_message_bg_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_border_highlight_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'border-color: %1$s%2$s;',
					esc_html( $cf7_border_highlight_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		// Success 
		if ( '' !== $cf7_success_message_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-mail-sent-ok',
				'declaration' => sprintf(
					'color: %1$s%2$s;',
					esc_html( $cf7_success_message_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_success_message_bg_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-mail-sent-ok',
				'declaration' => sprintf(
					'background-color: %1$s%2$s;',
					esc_html( $cf7_success_message_bg_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_success_border_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7 .wpcf7-mail-sent-ok',
				'declaration' => sprintf(
					'border-color: %1$s%2$s;',
					esc_html( $cf7_success_border_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		// Error
		if ( '' !== $cf7_error_message_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .wpcf7-validation-errors',
				'declaration' => sprintf(
					'color: %1$s%2$s;',
					esc_html( $cf7_error_message_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_error_message_bg_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .wpcf7-validation-errors',
				'declaration' => sprintf(
					'background-color: %1$s%2$s;',
					esc_html( $cf7_error_message_bg_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_error_border_color ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .wpcf7-validation-errors',
				'declaration' => sprintf(
					'border-color: %1$s%2$s;',
					esc_html( $cf7_error_border_color ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_message_padding ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'padding: %1$s%2$s;',
					esc_html( $cf7_message_padding ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}

		if ( '' !== $cf7_message_margin_top ) {
			ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% span.wpcf7-not-valid-tip',
				'declaration' => sprintf(
					'margin-top: %1$s%2$s;',
					esc_html( $cf7_message_margin_top ),
					et_is_builder_plugin_active() ? ' !important' : ''
				),
			) );
		}
	}

	public function _render_header_css( $render_slug ) {

		$form_header_bg         = $this->props['form_header_bg'];
		$form_header_bottom     = $this->props['form_header_bottom'];
		$form_header_img_bg     = $this->props['form_header_img_bg'];
		$form_header_icon_color = $this->props['form_header_icon_color'];
		$form_bg                = $this->props['form_bg'];

		ET_Builder_Element::set_style( $render_slug, array(
			'selector'    => '%%order_class%% .dipe-form-header-container',
			'declaration' => "background-color: {$form_header_bg};",
		));

		ET_Builder_Element::set_style( $render_slug, array(
			'selector'    => '%%order_class%% .dipe-form-header-container',
			'declaration' => "margin-bottom: {$form_header_bottom};",
		));

		ET_Builder_Element::set_style( $render_slug, array(
			'selector'    => '%%order_class%% .dipe-form-header-icon, %%order_class%% .dipe-form-header-image',
			'declaration' => "background-color: {$form_header_img_bg};",
		));

		ET_Builder_Element::set_style( $render_slug, array(
			'selector'    => '%%order_class%% .dipe-form-header-icon span',
			'declaration' => "color: {$form_header_icon_color};",
		));

		ET_Builder_Element::set_style( $render_slug, array(
			'selector'    => '%%order_class%% .dipe-cf7-styler',
			'declaration' => "background-color: {$form_bg};",
		));
	}

	public function _render_form_header_padding( $render_slug ) {

		$form_header_padding                        = $this->props['form_header_padding'];
		$form_header_padding_tablet                 = $this->props['form_header_padding_tablet'];
		$form_header_padding_phone                  = $this->props['form_header_padding_phone'];
		$form_header_padding_last_edited            = $this->props['form_header_padding_last_edited'];
        $form_header_padding_responsive_status      = et_pb_get_responsive_status( $form_header_padding_last_edited );

		\ET_Builder_Element::set_style($render_slug, array(
            'selector'    => '%%order_class%% .dipe-form-header-container',
            'declaration' => $this->_process_padding( $form_header_padding, false )
        ) );

		if( $form_header_padding_tablet && $form_header_padding_responsive_status ) {
			\ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-form-header-container',
				'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                'declaration' => $this->_process_padding( $form_header_padding_tablet, false )
			) );
        }

		if( $form_header_padding_phone && $form_header_padding_responsive_status ) {
			\ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-form-header-container',
				'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                'declaration' => $this->_process_padding( $form_header_padding_phone, false )
			) );
		}
	}

	public function _render_form_padding( $render_slug ) {

		$form_padding                        = $this->props['form_padding'];
		$form_padding_tablet                 = $this->props['form_padding_tablet'];
		$form_padding_phone                  = $this->props['form_padding_phone'];
		$form_padding_last_edited            = $this->props['form_padding_last_edited'];
        $form_padding_responsive_status      = et_pb_get_responsive_status( $form_padding_last_edited );

		\ET_Builder_Element::set_style($render_slug, array(
            'selector'    => '%%order_class%% .dipe-cf7-styler',
            'declaration' => self::_process_padding( $form_padding, false )
        ) );

		if( $form_padding_tablet && $form_padding_responsive_status ) {
			\ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7-styler',
				'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
                'declaration' => self::_process_padding( $form_padding_tablet, false )
			) );
        }

		if( $form_padding_phone && $form_padding_responsive_status ) {
			\ET_Builder_Element::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .dipe-cf7-styler',
				'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                'declaration' => self::_process_padding( $form_padding_phone, false )
			) );
		}
		
	}

	public static function _process_padding( $val = '0|0|0|0', $imp = false ) {

        $_val = explode( "|", $val );

        $padding_top = '';
        $padding_right = '';
        $padding_bottom = '';
		$padding_left = '';
		$imp_text = '';

		if( $imp ) {
			$imp_text = '!important';
		}

        if( $_val[0] !== "" ) {
            $padding_top = 'padding-top:' . $_val[0] . $imp_text . ';';
        }

        if( $_val[1] !== "" ) {
            $padding_right = 'padding-right:' . $_val[1] . $imp_text . ';';
        }

        if( $_val[2] !== "" ) {
            $padding_bottom = 'padding-bottom:' . $_val[2] . $imp_text . ';';
		}

        if( $_val[3] !== "" ) {
            $padding_left = 'padding-left:' . $_val[3] . $imp_text . ';';
        }

        return esc_html( "{$padding_top} {$padding_right} {$padding_bottom} {$padding_left}" );
	}

}

new DIPE_CF7_Styler;