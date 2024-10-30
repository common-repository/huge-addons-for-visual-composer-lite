<?php
if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

/*
 * HAVC Module: HAVC Chart
 * Description: Chart using canvas.
 * Author Name: NCreative
 * Author URL: https://ncreativ.net
 * Version: 1.0.0
 */
class HavcChart extends HavcModule {
	const slug = 'havc_chart';
	const base = 'havc_chart';

	public function __construct()
	{
		add_action('vc_before_init', array($this, 'vc_before_init'));
		add_shortcode(self::slug, array($this, 'build_shortcode'));
	}
	public function vc_before_init()
	{
		vc_map(

			array(
				'name' 						=> __('Havc Chart', HAVC_SLUG),
				'base' 						=> self::base,
				'category' 					=> __('HUGE', HAVC_SLUG),
				'icon' 						=> 'havc-chart-icon',
				'content_element' 			=> true,
				'is_container' 				=> true,
                'show_settings_on_create' 	=> true,
				'params'					=> array(
					array(
						'type'			=> 'dropdown',
						'heading'		=> __('Chart Type', HAVC_SLUG),
						'param_name'	=> 'chart_type',
						'value'			=> array(
							__('Icon', HAVC_SLUG) 						=> 'icon_mode',
							__('Icon With Percentage', HAVC_SLUG) 		=> 'icon_mode_percentage',
							__('Custom Text', HAVC_SLUG)				=> 'text_mode',
							__('Percentage', HAVC_SLUG)					=> 'percentage'
						),
						'description'	=> __('Select the type of the chart.', HAVC_SLUG),
						'admin_label'	=> false,
						'std'			=> 'percentage',
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
							'element' => 'chart_type',
							'value' => array(
								'icon_mode', 
								'icon_mode_percentage'
							),
						),
						'std'	=> 'fa-handshake-o'
					),

					array(
						'type'	=> 'textfield',
						'heading'	=> __('Chart Text', HAVC_SLUG),
						'param_name'	=> 'chart_text',
						'description'	=> 'Title for the chart',
						'dependency'	=> array(
							'element'	=> 'chart_type',
							'value'		=> array('text_mode'),
						),
					),

					array(
						'type'	=> 'textfield',
						'heading'	=> __('Font Size', HAVC_SLUG),
						'param_name'	=> 'font_size',
						'description'	=> 'Font Size Text & Icon',
					),

					array(
						'type'	=> 'colorpicker',
						'heading'	=> __('Icon & Text Color', HAVC_SLUG),
						'param_name'	=> 'text_color',
						'description'	=> __('Select color Text & Icon',HAVC_SLUG),
					),

					array(
						'type'		=> 'textfield',
						'heading'	=> __('Chart Percentage', HAVC_SLUG),
						'param_name'	=> 'chart_percentage',
						'description'	=> __('Percentage for the bar',HAVC_SLUG),
					),

					array(
						'type'			=> 'colorpicker',
						'heading'		=> __('Bar Color',HAVC_SLUG),
						'param_name'	=> 'bar_color',
						'value' 		=> '#2ABB9B',
						'description'	=> __('Color fot the bar',HAVC_SLUG),
					),

					array(
						'type'			=> 'colorpicker',
						'heading'		=> __('Track Color',HAVC_SLUG),
						'param_name'	=> 'track_color',
						'value' 		=> '#EBEDEF',
						'description'	=> __('Color for the track of the bar', HAVC_SLUG),
					),

					array(
						'type'			=> 'textfield',
						'heading'		=> __('Chart Size', HAVC_SLUG),
						'param_name'	=> 'bar_size',
						'description'	=> __('Size of the chart. ( default = 270 )', HAVC_SLUG),
					),

					array(
						'type'			=> 'textfield',
						'heading'		=> __('Bar Width', HAVC_SLUG),
						'param_name'	=> 'bar_width',
						'description'	=> __('Width for the bar. ( default = 6 )',HAVC_SLUG)
					),

					array(
						'type'			=> 'dropdown',
						'heading'		=> __( 'Bar edge style', HAVC_SLUG ),
						'param_name'	=> 'line_style',
						'description'	=> __( 'Shape for the corners of the bar', HAVC_SLUG ),
						'value'			=> array(
							__('Round', HAVC_SLUG)	=> 'round',
							__('Square', HAVC_SLUG)	=> 'square',
						),
						'std'			=> 'square',
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
		wp_enqueue_style( 'havc-chart-css', HAVC_URL . 'addons/havc-chart/assets/css/havc-chart.css' );
		// ---------------------
		// srcipts needed to make the chart
		wp_enqueue_script( 'easy-pie-chart-js', HAVC_URL . 'addons/havc-chart/assets/js/easypiechart.min.js', 'jquery', 1.0, true );
		wp_register_script( 'havc-chart-js', HAVC_URL . 'addons/havc-chart/assets/js/havc-chart.js', 'jquery', 1.0, true );
		wp_enqueue_script( 'havc-chart-js' );

		$output = $chart_type = $icon = $chart_text = $text_color = $chart_percentage = $bar_color = $track_color = $bar_size = $bar_width = $line_style = $el_class = $font_size = '';

		extract(
			shortcode_atts(
				array(
					'chart_type'		=> 'percentage',
					'icon'				=> 'fa fa-handshake-o',
					'chart_text'		=> 'Chart Text',
					'text_color'		=> '#2ABB9B',
					'chart_percentage'	=> '50',
					'bar_color'			=> '#2ABB9B',
					'track_color'		=> '#EBEDEF',
					'bar_size'			=> '270',
					'bar_width'			=> '10',
					'line_style'		=> 'square',
					'font_size'			=> '32',
					'el_class'			=> '',
				), $atts
			)
		);
		$chart_out = '';
		if ($chart_type == 'text_mode') {
			$chart_out = '<div class="havc-chart-text" style="color:'.$text_color.';">' .$chart_text . '</div>';
		}
		if ($chart_type == 'percentage') {
			$chart_out = '<div class="havc-chart-text" style="color:'.$text_color.';">' .$chart_percentage . '%</div>';
		}
		if ($chart_type == 'icon_mode') {
			$chart_out = '<div class="havc-chart-text" style="color:'.$text_color.';"><i class="' .$icon . '"></i></div>';
		}
		if ($chart_type == 'icon_mode_percentage') {
			$chart_out = '<div class="havc-chart-text" style="color:'.$text_color.';"><i class="' .$icon . '"></i><div class="percent-text">'.$chart_percentage.'%</div></div>';
		}
		

		$output .= '<div class="'.$el_class.'" style="font-size:'.$font_size.'px;" >';
		$output .= '<div class="havc-chart" data-barstyle="'.$line_style.'" data-bgcolor="'.$bar_color.'" data-trackcolor="'.$track_color.'" data-size="'.$bar_size.'" data-line="'.$bar_width.'" data-percent="'.$chart_percentage.'" style="width: '.$bar_size.'px;">'.$chart_out.'</div>';
		$output .= '</div>';
		return $output;
	}
}
new HavcChart();
