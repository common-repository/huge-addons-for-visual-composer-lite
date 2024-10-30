<?php
if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

/*
 * HAVC Module: HAVC Divider
 * Description: Havc Divider.
 * Author Name: NCreative
 * Author URL: https://ncreativ.net
 * Version: 1.0.0
 */
class HavcDivider extends HavcModule {
	const slug = 'havc_divider';
	const base = 'havc_divider';

	public function __construct()
	{
		add_action('vc_before_init', array($this, 'vc_before_init'));
		add_shortcode(self::slug, array($this, 'build_shortcode'));
	}
	public function vc_before_init()
	{
		vc_map(

			array(
				'name' 		=> __('Havc divider', HAVC_SLUG),
				'base' 		=> self::base,
				'category' 	=> __('HUGE', HAVC_SLUG),
				'icon' 		=> 'havc-divider-icon',
				'content_element' => true,
				'is_container' => true,
                'show_settings_on_create' => true,
				'params'	=> array(
					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Divider Type', HAVC_SLUG),
						'param_name'	=> 'divider_type',
						'value'			=> array(
							__('Line', HAVC_SLUG) 				=> 'line_mode',
							__('Icon', HAVC_SLUG) 				=> 'icon_mode',
							__('Icon With Border', HAVC_SLUG) 	=> 'icon_border',
							__('Text', HAVC_SLUG)				=> 'text_mode',
							__('Text With Border', HAVC_SLUG)	=> 'text_border',
						),
						'description'	=> __('Select the type of the divider.', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'line_mode',
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Line Type', HAVC_SLUG),
						'param_name'	=> 'line_type',
						'value'			=> array(
							__('Single Line', HAVC_SLUG) 		=> 'single_line',
							__('Double Line', HAVC_SLUG) 		=> 'double_line',
							__('Triple Line', HAVC_SLUG)		=> 'triple_line',
							__('Dashed Line', HAVC_SLUG) 		=> 'dash_line',
							__('Dotted Line', HAVC_SLUG) 		=> 'dott_line',
						),
						'description'	=> __('Select the type of the line.', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'single_line',
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Width Divider', HAVC_SLUG),
						'param_name'	=> 'width_divider',
						'value'			=> array(
							__('Full Width', HAVC_SLUG) => '100',
							__('50%', HAVC_SLUG) 		=> '50',
							__('25%', HAVC_SLUG)		=> '25',
						),
						'description'	=> __('Select the width divider.', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> '100',
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Divider Position', HAVC_SLUG),
						'param_name'	=> 'divider_position',
						'value'			=> array(
							__('Left', HAVC_SLUG) 				=> 'left',
							__('Center', HAVC_SLUG) 			=> 'center',
							__('Right', HAVC_SLUG)				=> 'right',
						),
						'description'	=> __('Select the Icon or Text Position', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'center',
						'dependency'	=> array(
							'element'	=> 'width_divider',
							'value'		=> array(
								'50',
								'25'
							),
						),
					),					

					array(
						'type'			=> 'colorpicker',
						'heading'		=> __('Line Color', HAVC_SLUG),
						'param_name'	=> 'line_color',
						'description'	=> __('Select color the line',HAVC_SLUG),
						'std'			=> '#2ABB9B',
					),

					array(
						'type'			=> 'textfield',
						'heading'		=> __('Line Size', HAVC_SLUG),
						'param_name'	=> 'line_size',
						'description'	=> __('Size of the Line Without px. ( default 14 )', HAVC_SLUG),
						'std'			=> '2',
					),

					array(
						'type' => 'iconpicker',
						'heading' => __( 'Icon', HAVC_SLUG ),
						'param_name' => 'icon',
						'settings' => array(
							'emptyIcon' => false, 
			                'iconsPerPage' => 200, 
			            ),
						'dependency' => array(
							'element' => 'divider_type',
							'value' => array(
								'icon_mode', 
								'icon_border'
							),
						),
						'std'	=> 'fa-handshake-o'
					),

					array(
						'type'	=> 'textfield',
						'heading'	=> __('Divider Text', HAVC_SLUG),
						'param_name'	=> 'divider_text',
						'description'	=> 'Title for the divider',
						'dependency'	=> array(
							'element'	=> 'divider_type',
							'value'		=> array(
								'text_mode',
								'text_border'
							),
						),
						'std'			=> 'Text Divider',
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Icon & Text Position', HAVC_SLUG),
						'param_name'	=> 'content_position',
						'value'			=> array(
							__('Left Line', HAVC_SLUG) 			=> 'left',
							__('Center Line', HAVC_SLUG) 		=> 'center',
							__('Right Line', HAVC_SLUG)			=> 'right',
							__('Custom Position', HAVC_SLUG) 	=> 'custom_position',
						),
						'description'	=> __('Select the Icon or Text Position', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'center',
						'dependency'	=> array(
							'element'	=> 'divider_type',
							'value'		=> array(
								'icon_mode',
								'icon_border',
								'text_mode',
								'text_border'
							)
						),
					),

					array(
						'type'			=> 'dropdown',
						'heading'	=> __('Custom Icon or Text Position', HAVC_SLUG),
						'param_name'	=> 'custom_position',
						'value'			=> array(
							__('10%', HAVC_SLUG) 	=> '10',
							__('15%', HAVC_SLUG) 	=> '15',
							__('20%', HAVC_SLUG)	=> '20',
							__('25%', HAVC_SLUG) 	=> '25',
							__('30%', HAVC_SLUG) 	=> '30',
							__('45%', HAVC_SLUG) 	=> '45',
							__('50%', HAVC_SLUG)	=> '50',
							__('55%', HAVC_SLUG) 	=> '55',
							__('60%', HAVC_SLUG) 	=> '60',
							__('65%', HAVC_SLUG) 	=> '65',
							__('70%', HAVC_SLUG)	=> '70',
							__('75%', HAVC_SLUG) 	=> '75',
							__('80%', HAVC_SLUG) 	=> '80',
							__('85%', HAVC_SLUG) 	=> '85',
							__('90%', HAVC_SLUG) 	=> '90',
							__('95%', HAVC_SLUG) 	=> '95',
							__('100%', HAVC_SLUG) 	=> '100',
						),
						'description'	=> __('Custom Icon or Text Position without px( example: 100)', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'center',
						'dependency'	=> array(
							'element'	=> 'content_position',
							'value'		=> array('custom_position'),
						),
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Border Style', HAVC_SLUG),
						'param_name'	=> 'border_style',
						'value'			=> array(
							__('Square Border', HAVC_SLUG) 		=> 'square',
							__('Round Border', HAVC_SLUG) 		=> 'round',
						),
						'description'	=> __('Select the border style', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'round',
						'dependency'	=> array(
							'element'	=> 'divider_type',
							'value'		=> array(
								'icon_border',
								'text_border'
							)
						),
					),

					array(
						'type'			=> 'textfield',
						'heading'		=> __('Font Size', HAVC_SLUG),
						'param_name'	=> 'font_size',
						'description'	=> __('Font Size Text & Icon', HAVC_SLUG),
						'dependency'	=> array(
							'element'	=> 'divider_type',
							'value'		=> array(
								'icon_mode',
								'icon_border',
								'text_mode',
								'text_border'
							),
						),
					),

					array(
						'type'	=> 'colorpicker',
						'heading'	=> __('Icon & Text Color', HAVC_SLUG),
						'param_name'	=> 'text_color',
						'description'	=> __('Select color Text & Icon',HAVC_SLUG),
						'dependency'	=> array(
							'element'	=> 'divider_type',
							'value'		=> array(
								'icon_mode',
								'icon_border',
								'text_mode',
								'text_border'
							),
						),
						'std'			=> '#2ABB9B',
					),

					array(
						'type'			=> 'textfield',
						'heading'		=> __('Extra class name', HAVC_SLUG),
						'param_name'	=> 'el_class',
						'description'	=> __( 'Extra class t be customized via css', HAVC_SLUG )
					),
				),
			)

		);
	}

	public function build_shortcode($atts)
	{		
		wp_enqueue_style( 'havc-divider-css', HAVC_URL . 'addons/havc-divider/assets/css/havc-divider.css' );

		$output = $divider_type = $line_type = $custom_width = $divider_position = $line_color = $line_size = $icon = $divider_text = $content_position = $custom_position_left = $custom_position_right = $custom_position = $border_style = $font_size = $text_color = $el_class = '';

		extract(
			shortcode_atts(
				array(
					'divider_type' 		=> 'line_mode',
					'line_type' 		=> 'single_line',
					'width_divider' 	=> '100',
					'divider_position' 	=> 'center',
					'line_color' 		=> '#2ABB9B',
					'line_size' 		=> '2',
					'icon' 				=> '',
					'divider_text' 		=> 'Text Divider',
					'content_position' 	=> 'center',
					'custom_position' 	=> '',
					'border_style' 		=> '',
					'font_size' 		=> '',
					'text_color' 		=> '#2ABB9B',
					'el_class' 			=> '',
				), $atts
			)
		);
		if (empty($line_size)) {
			$line_size = '2';
		}
		$line = '';
		if (empty($line_type) || $line_type == 'single_line') {
			$line = '<span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span>';
		}
		if ($line_type == 'double_line') {
			$line = '<span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span><span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span>';
		}
		if ($line_type == 'triple_line') {
			$line = '<span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span><span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span><span class="havc-line" style="border-top-style:solid;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span>';
		}
		if($line_type == 'dash_line'){
			$line = '<span class="havc-line-dashed" style="border-top-style:dashed;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span>';
		}
		if ($line_type == 'dott_line') {
			$line = '<span class="havc-line-dotted" style="border-top-style:dotted;border-top-color:'.$line_color.';border-top-width:'.$line_size.'px;"></span>';
		}
		if (empty($width_divider) || $width_divider == '100') {
			$width_divider = '100';
		}
		if (empty($content_position)) {
			$content_position = 'center';
		}
		$custom_position_style = '';
		if ($content_position == 'custom_position'){
			if (empty($custom_position) || $custom_position == '10') {
				$custom_position = '10';
			}
			$custom_position_left = 'style="width:'.$custom_position.'%;"';
			$custom_position_right = 'style="width:100%;"';
		}

		$style_content = '';
		if ($divider_type == 'icon_border' || $divider_type == 'text_border') {	
			$style_content .= 'border: '.$line_size.'px solid '.$line_color.';';		
			if (empty($border_style) || $border_style == 'round') {
				if ($divider_type == 'text_border') {
					$style_content .= 'border-radius:30px;';
				} else {
					$style_content .= 'border-radius:50%;';
				}
				
				
			}
			if ($border_style == 'square') {
				$style_content .= 'border-radius:0;';
			}
		}
		if (empty($icon)) {
			$icon = 'fa fa-handshake-o';
		}
		if (empty($font_size)) {
			$style_content .= 'font_size:inherit;';
		}else{
			$style_content .= 'font-size:'.$font_size.'px;';
		}
		if (empty($text_color)) {
			$style_content .= 'color:#2ABB9B;';
		}else{
			$style_content .= 'color:'.$text_color.';';
		}
		$content_divider = '';
		if (empty($divider_type) || $divider_type == 'line_mode') {
			$content_divider = '<div class="havc-divider-line havc-side-left">'.$line.'</div>';
			$content_divider .= '<div class="havc-divider-line havc-side-right">'.$line.'</div>';
		}
		if ($divider_type == 'icon_mode' || $divider_type == 'icon_border') {
			$content_divider = '<div class="havc-divider-line havc-side-left" ' .$custom_position_left. '>'.$line.'</div>';
			$content_divider .= '<div class="havc-divider-line havc-side-content" style="'.$style_content.'"><i class="'.$icon.'"></i></div>';
			$content_divider .= '<div class="havc-divider-line havc-side-right" '.$custom_position_right .'>'.$line.'</div>';
		}
		if ($divider_type == 'text_mode' || $divider_type == 'text_border'){
			$content_divider = '<div class="havc-divider-line havc-side-left" ' .$custom_position_left. '>'.$line.'</div>';
			$content_divider .= '<div class="havc-divider-line havc-side-content" style="'.$style_content.'">'.$divider_text.'</div>';
			$content_divider .= '<div class="havc-divider-line havc-side-right" '.$custom_position_right .'>'.$line.'</div>';
		}

		$output .= '<div class="havc-divider '.$el_class.'">';
		$output .= '<div class="havc-divider-'.$divider_position.' havc-content-'.$content_position.' havc-type-'.$divider_type.'" style="width:'.$width_divider.'%;display:table;">';
		$output .= $content_divider;
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}
new HavcDivider();
