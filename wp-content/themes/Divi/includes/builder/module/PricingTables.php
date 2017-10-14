<?php

class ET_Builder_Module_Pricing_Tables extends ET_Builder_Module {
	function init() {
		$this->name                 = esc_html__( 'Pricing Tables', 'et_builder' );
		$this->slug                 = 'et_pb_pricing_tables';
		$this->fb_support           = true;
		$this->main_css_element 	= '%%order_class%%.et_pb_pricing';
		$this->child_slug           = 'et_pb_pricing_table';
		$this->child_item_text      = esc_html__( 'Pricing Table', 'et_builder' );

		$this->whitelisted_fields = array(
			'admin_label',
			'module_id',
			'module_class',
			'featured_table_background_color',
			'header_background_color',
			'featured_table_header_background_color',
			'featured_table_header_text_color',
			'featured_table_subheader_text_color',
			'featured_table_price_color',
			'featured_table_text_color',
			'show_bullet',
			'bullet_color',
			'featured_table_bullet_color',
			'show_featured_drop_shadow',
			'center_list_items',
		);

		$this->fields_defaults = array(
			'show_bullet'                 => array( 'on' ),
			'show_featured_drop_shadow'   => array( 'on' ),
			'center_list_items'           => array( 'off' ),
		);

		$this->additional_shortcode = 'et_pb_pricing_item';
		$this->main_css_element = '%%order_class%%';
		$this->custom_css_options = array(
			'pricing_heading' => array(
				'label'    => esc_html__( 'Pricing Heading', 'et_builder' ),
				'selector' => '.et_pb_pricing_heading',
			),
			'pricing_title' => array(
				'label'    => esc_html__( 'Pricing Title', 'et_builder' ),
				'selector' => '.et_pb_pricing_heading h2',
			),
			'pricing_subtitle' => array(
				'label'    => esc_html__( 'Pricing Subtitle', 'et_builder' ),
				'selector' => '.et_pb_pricing_heading .et_pb_best_value',
			),
			'pricing_top' => array(
				'label'    => esc_html__( 'Pricing Top', 'et_builder' ),
				'selector' => '.et_pb_pricing_content_top',
			),
			'price' => array(
				'label'    => esc_html__( 'Price', 'et_builder' ),
				'selector' => '.et_pb_et_price',
			),
			'currency' => array(
				'label'    => esc_html__( 'Currency', 'et_builder' ),
				'selector' => '.et_pb_dollar_sign',
			),
			'frequency' => array(
				'label'    => esc_html__( 'Frequency', 'et_builder' ),
				'selector' => '.et_pb_frequency',
			),
			'pricing_content' => array(
				'label'    => esc_html__( 'Pricing Content', 'et_builder' ),
				'selector' => '.et_pb_pricing_content',
			),
			'pricing_item' => array(
				'label'    => esc_html__( 'Pricing Item', 'et_builder' ),
				'selector' => 'ul.et_pb_pricing li',
			),
			'pricing_item_excluded' => array(
				'label'    => esc_html__( 'Excluded Item', 'et_builder' ),
				'selector' => 'ul.et_pb_pricing li.et_pb_not_available',
			),
			'pricing_button' => array(
				'label'    => esc_html__( 'Pricing Button', 'et_builder' ),
				'selector' => '.et_pb_pricing_table_button',
			),
			'featured_table' => array(
				'label'    => esc_html__( 'Featured Table', 'et_builder' ),
				'selector' => '.et_pb_featured_table',
			),
		);

		$this->options_toggles = array(
			'general'  => array(
				'toggles' => array(
					'elements'    => esc_html__( 'Elements', 'et_builder' ),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'layout' => esc_html__( 'Layout', 'et_builder' ),
					'bullet' => esc_html__( 'Bullet', 'et_builder' ),
				),
			),
		);

		$this->advanced_options = array(
			'fonts' => array(
				'header' => array(
					'label'    => esc_html__( 'Header', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .et_pb_pricing_heading h2",
						'important' => 'all',
					),
					'letter_spacing' => array(
						'default' => '0px',
					),
				),
				'body'   => array(
					'label'    => esc_html__( 'Body', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .et_pb_pricing li",
						'plugin_main' => "{$this->main_css_element} .et_pb_pricing li, {$this->main_css_element} .et_pb_pricing li span, {$this->main_css_element} .et_pb_pricing li a",
					),
					'line_height' => array(
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
					'font_size' => array(
						'default' => '14px',
					),
					'letter_spacing' => array(
						'default' => '0px',
					),
				),
				'subheader' => array(
					'label'    => esc_html__( 'Subheader', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .et_pb_best_value",
					),
					'letter_spacing' => array(
						'default' => '0px',
					),
					'line_height' => array(
						'default' => '1em',
					),
				),
				'currency_frequency' => array(
					'label'    => esc_html__( 'Currency &amp; Frequency', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .et_pb_dollar_sign, {$this->main_css_element} .et_pb_frequency",
					),
					'hide_text_align' => true,
				),
				'price' => array(
					'label'    => esc_html__( 'Price', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} .et_pb_sum",
						'text_align' => "{$this->main_css_element} .et_pb_pricing_content_top",
					),
					'line_height' => array(
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
				),
			),
			'background' => array(
				'css'  => array(
					'main' => "{$this->main_css_element} .et_pb_pricing_table",
				),
				'settings' => array(
					'color' => 'alpha',
				),
			),
			'border' => array(
				'css' => array(
					'main' => "{$this->main_css_element} .et_pb_pricing_table",
				),
				'additional_elements' => array(
					"{$this->main_css_element} .et_pb_pricing_content_top" => array( 'bottom' ),
				),
			),
			'button' => array(
				'button' => array(
					'label' => esc_html__( 'Button', 'et_builder' ),
					'css' => array(
						'plugin_main' => "{$this->main_css_element} .et_pb_pricing_table_button.et_pb_button",
						'alignment'   => "{$this->main_css_element} .et_pb_button_wrapper",
					),
					'use_alignment' => true,
				),
			),
			'custom_margin_padding' => array(
				'css' => array(
					'important'      => 'all', // needed to overwrite last module margin-bottom styling
					'main'           => '%%order_class%% .et_pb_pricing_heading, %%order_class%% .et_pb_pricing_content_top, %%order_class%% .et_pb_pricing_content',
					'padding-right'  => '%%order_class%% .et_pb_button_wrapper',
					'padding-bottom' => '%%order_class%% .et_pb_pricing_table',
					'padding-left'   => '%%order_class%% .et_pb_button_wrapper',
				),
			),
			'max_width' => array(),
			'text'      => array(
				'css' => array(
					'text_orientation' => '%%order_class%% .et_pb_pricing_table, %%order_class%% .et_pb_pricing_content',
				),
			),
		);
	}

	function get_fields() {
		$fields = array(
			'featured_table_background_color' => array(
				'label'             => esc_html__( 'Featured Table Background Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'layout',
				'priority'          => 23,
			),
			'featured_table_header_background_color' => array(
				'label'             => esc_html__( 'Featured Table Header Background Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'header',
				'priority'          => 21,
			),
			'featured_table_header_text_color' => array(
				'label'             => esc_html__( 'Featured Table Header Text Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'header',
				'priority'          => 20,
			),
			'header_background_color' => array(
				'label'             => esc_html__( 'Table Header Background Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'header',
			),
			'featured_table_subheader_text_color' => array(
				'label'             => esc_html__( 'Featured Table Subheader Text Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'subheader',
				'priority'          => 20,
			),
			'featured_table_price_color' => array(
				'label'             => esc_html__( 'Featured Table Price Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'price',
				'priority'          => 20,
			),
			'featured_table_text_color' => array(
				'label'             => esc_html__( 'Featured Table Body Text Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'body',
				'priority'          => 22,
			),
			'show_bullet' => array(
				'label'           => esc_html__( 'Show Bullet', 'et_builder' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'et_builder' ),
					'off' => esc_html__( 'No', 'et_builder' ),
				),
				'toggle_slug'     => 'elements',
				'affects'         => array(
					'bullet_color',
				),
			),
			'bullet_color' => array(
				'label'             => esc_html__( 'Bullet Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'bullet',
				'depends_show_if'   => 'on',
			),
			'featured_table_bullet_color' => array(
				'label'             => esc_html__( 'Featured Table Bullet Color', 'et_builder' ),
				'type'              => 'color-alpha',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
				'toggle_slug'       => 'bullet',
				'priority'          => 22,
			),
			'show_featured_drop_shadow' => array(
				'label'           => esc_html__( 'Show Featured Table Drop Shadow', 'et_builder' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'et_builder' ),
					'off' => esc_html__( 'No', 'et_builder' ),
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'layout',
				'priority'        => 24,
			),
			'center_list_items' => array(
				'label'           => esc_html__( 'Center List Items', 'et_builder' ),
				'type'            => 'yes_no_button',
				'option_category' => 'layout',
				'options'         => array(
					'off' => esc_html__( 'No', 'et_builder' ),
					'on'  => esc_html__( 'Yes', 'et_builder' ),
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'layout',
			),
			'disabled_on' => array(
				'label'           => esc_html__( 'Disable on', 'et_builder' ),
				'type'            => 'multiple_checkboxes',
				'options'         => array(
					'phone'   => esc_html__( 'Phone', 'et_builder' ),
					'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
					'desktop' => esc_html__( 'Desktop', 'et_builder' ),
				),
				'additional_att'  => 'disable_on',
				'option_category' => 'configuration',
				'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
				'tab_slug'        => 'custom_css',
				'toggle_slug'     => 'visibility',
			),
			'admin_label' => array(
				'label'       => esc_html__( 'Admin Label', 'et_builder' ),
				'type'        => 'text',
				'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
				'toggle_slug' => 'admin_label',
			),
			'module_id' => array(
				'label'           => esc_html__( 'CSS ID', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'tab_slug'        => 'custom_css',
				'toggle_slug'     => 'classes',
				'option_class'    => 'et_pb_custom_css_regular',
			),
			'module_class' => array(
				'label'           => esc_html__( 'CSS Class', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'tab_slug'        => 'custom_css',
				'toggle_slug'     => 'classes',
				'option_class'    => 'et_pb_custom_css_regular',
			),
		);
		return $fields;
	}

	function pre_shortcode_content() {
		global $et_pb_pricing_tables_num, $et_pb_pricing_tables_icon, $et_pb_pricing_tab, $et_pb_pricing_tables_button_rel;

		$button_custom = $this->shortcode_atts['custom_button'];
		$custom_icon   = $this->shortcode_atts['button_icon'];

		$et_pb_pricing_tables_num = 0;

		$et_pb_pricing_tables_icon = 'on' === $button_custom ? $custom_icon : '';

		$et_pb_pricing_tables_button_rel = $this->shortcode_atts['button_rel'];
	}

	function shortcode_callback( $atts, $content = null, $function_name ) {
		$module_id                              = $this->shortcode_atts['module_id'];
		$module_class                           = $this->shortcode_atts['module_class'];
		$featured_table_background_color        = $this->shortcode_atts['featured_table_background_color'];
		$featured_table_text_color              = $this->shortcode_atts['featured_table_text_color'];
		$header_background_color                = $this->shortcode_atts['header_background_color'];
		$featured_table_header_background_color = $this->shortcode_atts['featured_table_header_background_color'];
		$featured_table_header_text_color       = $this->shortcode_atts['featured_table_header_text_color'];
		$featured_table_subheader_text_color    = $this->shortcode_atts['featured_table_subheader_text_color'];
		$featured_table_price_color             = $this->shortcode_atts['featured_table_price_color'];
		$bullet_color                           = $this->shortcode_atts['bullet_color'];
		$featured_table_bullet_color            = $this->shortcode_atts['featured_table_bullet_color'];
		$show_featured_drop_shadow              = $this->shortcode_atts['show_featured_drop_shadow'];
		$center_list_items                      = $this->shortcode_atts['center_list_items'];
		$show_bullet                            = $this->shortcode_atts['show_bullet'];

		global $et_pb_pricing_tables_num, $et_pb_pricing_tables_icon;

		$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

		if ( 'on' !== $show_featured_drop_shadow ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table',
				'declaration' => '-moz-box-shadow: none; -webkit-box-shadow: none; box-shadow: none;',
			) );
		}

		if ( 'off' === $show_bullet ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_pricing li span:before',
				'declaration' => 'display: none;',
			) );
		}

		if ( 'on' === $center_list_items ) {
			$module_class .= ' et_pb_centered_pricing_items';
		}

		if ( '' !== $featured_table_background_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table',
				'declaration' => sprintf(
					'background-color: %1$s;',
					esc_html( $featured_table_background_color )
				),
			) );
		}

		if ( '' !== $header_background_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_pricing_heading',
				'declaration' => sprintf(
					'background-color: %1$s;',
					esc_html( $header_background_color )
				),
			) );
		}

		if ( '' !== $featured_table_header_background_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table .et_pb_pricing_heading',
				'declaration' => sprintf(
					'background-color: %1$s;',
					esc_html( $featured_table_header_background_color )
				),
			) );
		}

		if ( '' !== $featured_table_header_text_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table .et_pb_pricing_heading h2',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_html( $featured_table_header_text_color )
				),
			) );
		}

		if ( '' !== $featured_table_subheader_text_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table .et_pb_best_value',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_html( $featured_table_subheader_text_color )
				),
			) );
		}

		if ( '' !== $featured_table_price_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table .et_pb_sum',
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_html( $featured_table_price_color )
				),
			) );
		}

		if ( '' !== $featured_table_text_color ) {
			$featured_table_text_color_selector = et_is_builder_plugin_active() ? '%%order_class%% .et_pb_featured_table .et_pb_pricing_content li, %%order_class%% .et_pb_featured_table .et_pb_pricing_content li span, %%order_class%% .et_pb_featured_table .et_pb_pricing_content li a' : '%%order_class%% .et_pb_featured_table .et_pb_pricing_content li';
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => $featured_table_text_color_selector,
				'declaration' => sprintf(
					'color: %1$s !important;',
					esc_html( $featured_table_text_color )
				),
			) );
		}

		if ( '' !== $bullet_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_pricing li span:before',
				'declaration' => sprintf(
					'border-color: %1$s;',
					esc_html( $bullet_color )
				),
			) );
		}

		if ( '' !== $featured_table_bullet_color ) {
			ET_Builder_Element::set_style( $function_name, array(
				'selector'    => '%%order_class%% .et_pb_featured_table .et_pb_pricing li span:before',
				'declaration' => sprintf(
					'border-color: %1$s;',
					esc_html( $featured_table_bullet_color )
				),
			) );
		}

		$video_background = $this->video_background();
		$parallax_image_background = $this->get_parallax_image_background();

		$content = $this->shortcode_content;

		$output = sprintf(
			'<div%3$s class="et_pb_module et_pb_pricing clearfix%2$s%4$s%5$s%7$s">
				%8$s
				%6$s
				<div class="et_pb_pricing_table_wrap">
					%1$s
				</div>
			</div>',
			$content,
			esc_attr( " et_pb_pricing_{$et_pb_pricing_tables_num}" ),
			( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
			( '' !== $module_class ? sprintf( ' %1$s', esc_attr( ltrim( $module_class ) ) ) : '' ),
			'' !== $video_background ? ' et_pb_section_video et_pb_preload' : '',
			$video_background,
			'' !== $parallax_image_background ? ' et_pb_section_parallax' : '',
			$parallax_image_background
		);

		return $output;
	}

	function additional_shortcode_callback( $atts, $content = null, $function_name ) {
		$attributes = shortcode_atts( array(
			'available' => 'on',
		), $atts );

		$output = sprintf( '<li%2$s><span>%1$s</span></li>',
			$content,
			( 'on' !== $attributes['available'] ? ' class="et_pb_not_available"' : '' )
		);
		return $output;
	}
}

new ET_Builder_Module_Pricing_Tables;
