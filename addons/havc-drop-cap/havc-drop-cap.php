<?php
if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

/*
 * HAVC Module: HAVC Drop Cap
 * Description: HAVC Drop Cap.
 * Author Name: NCreative
 * Author URL: https://ncreativ.net
 * Version: 1.0.0
 */
class HavcDropcap extends HavcModule {
	const slug = 'havc_dropcap';
	const base = 'havc_dropcap';

	public function __construct()
	{
		add_action('vc_before_init', array($this, 'vc_before_init'));
		add_shortcode(self::slug, array($this, 'build_shortcode'));
	}
	public function vc_before_init()
	{
		vc_map(

			array(
				'name' 						=> __('Havc Drop Cap', HAVC_SLUG),
				'base' 						=> self::base,
				'category' 					=> __('HUGE', HAVC_SLUG),
				'icon' 						=> 'havc-drop-cap-icon',
				'content_element' 			=> true,
				'is_container' 				=> true,
                'show_settings_on_create' 	=> true,
				'params'					=> array(
					array(
						'type' => 'font_container',
						'heading'	=> __('Dropcap letter Style', HAVC_SLUG),
						'param_name' => 'font_container',
						'value'=>'',
						'settings'=>array(
							'fields'=>array(								
								'font_size',
								'line_height',
								'color',
								'font_style_italic',
								'font_style_bold',
								'font_family',
								'font_size_description' => __('Dropcap letter font size.',HAVC_SLUG),
								'line_height_description' => __('Dropcap letter line height.',HAVC_SLUG),
								'color_description' => __('Dropcap letter font color.',HAVC_SLUG),
								'font_style_description' => __('Dropcap letter font style.',HAVC_SLUG),
								'font_family_description' => __('Dropcap letter font family.',HAVC_SLUG),
							),
						),
						'description' => __( 'Dropcap letter Style.', HAVC_SLUG ),
					),
					array(
						'type'		=> 'css_editor',
						'heading'	=> __('Dropcap letter styling', HAVC_SLUG),
						'param_name'	=> 'dropcap_css',
					),
					array(
						'type'		=> 'textarea_html',
						'heading'	=> __('Content', HAVC_SLUG),
						'param_name'	=> 'content',
						'value'			=> 'Ei mea cibo dicit graeco, ex reque probatus sit, id justo officiis mei. Eum no ullum aeterno. Et usu ullum conclusionemque, te nec sint putant impetus. Mei zril maiestatis ei, per in amet lorem epicurei. Cum tantas meliore te, usu quod nominavi voluptua ne, sed te labores inimicus. Vix at recusabo posidonium dissentiet, eam ullum graece temporibus cu.',
					),
				),
			)

		);
	}

	public function build_shortcode($atts, $content = null )
	{

		//$content = '';

		extract(
			shortcode_atts(
				array(
					'font_container' 	=> '',
					'dropcap_css'		=> '',
					), $atts
			)
		);
		$dropcap_css = apply_filters(
			VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 
			vc_shortcode_custom_css_class( $dropcap_css, '' ), 
			self::slug, 
			$atts
		);

		$content = wpb_js_remove_wpautop( $content, true );
		
		$font_container_obj = new Vc_Font_Container();
		$font_container_data = $font_container_obj->_vc_font_container_parse_attributes( 
			array(
				'font_size',
				'line_height',
				'color',
				'font_style_italic',
				'font_style_bold'
			), 
			$font_container 
		);

		$styles = array('float' => 'float: left');

		if ( ! empty( $font_container_data ) && isset( $font_container_data['values'] ) ) {
			foreach ( $font_container_data['values'] as $key => $value ) {
				if ( $key != 'tag' && strlen( $value ) > 0 ) {
					if ( preg_match( '/description/', $key ) ) {
						continue;
					}
					if ( $key == 'font_size' || $key == 'line_height' ) {
						$value = preg_replace( '/\s+/', '', $value );
					}
					if ( $key == 'font_size' ) {
						$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
						// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
						$regexr = preg_match( $pattern, $value, $matches );
						$value = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
						$unit = isset( $matches[2] ) ? $matches[2] : 'px';
						$value = $value . $unit;
					}
					if ( strlen( $value ) > 0 ) {
						if(array_key_exists($key, $font_container_data['fields'])){
							switch ($key) {
								case 'font_style_italic':
								if($value == 1){
									$styles[$key] = 'font-style: italic';
								}
								break;

								case 'font_style_bold':
								if($value == 1){
									$styles[$key] = 'font-weight: bold';
								}
								break;
								
								default:
								$styles[$key] = str_replace( '_', '-', $key ) . ': ' . $value;
								break;
							}
						}
					}
				}
			}
		}

		$is_content = preg_replace('/^([\<\sa-z\d\/\>]*)(([a-z\&\;]+)|([\"\'\w]))/', '$1<span class="drop-cap '.$dropcap_css.'" style="'.implode(";", $styles).'">$2</span>', $content);

		return print_r($is_content, true);
	}
}
new HavcDropcap();
