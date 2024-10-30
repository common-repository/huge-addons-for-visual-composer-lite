<?php

if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

/**
 * Plugin Name:       Huge Addons for Visual Composer
 * Plugin URI:        https://ncreative.net/huge-addons-for-visual-composer
 * Description:       Huge Addons for Visual Composer.
 * Version:           1.0.1
 * Author:            NCreative
 * Author URI:        https://ncreative.net/huge-addons-for-visual-composer
 * Text Domain:       HAVC
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !defined( 'HAVC_PATH' ) )
	define( 'HAVC_PATH', plugin_dir_path( __FILE__ ) );

if ( !defined( 'HAVC_URL' ) )
	define( 'HAVC_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'HAVC_SLUG' ) )
	define( 'HAVC_SLUG', 'HAVC' );


require_once( dirname(__FILE__).'/includes/class-havc-base.php' );
require_once( dirname(__FILE__).'/includes/class-havc-module.php' );

add_action('init', 'havc_register_script');
function havc_register_script() {
	// enqueue & register icons (entypo, entypo socials, steadyicons, lineicons, icons)
	wp_register_style( "havc_icons_font", plugins_url( '/assets/fonts.css', __FILE__ ) );
	// enqueue & register default css if user defined not found
	wp_register_style( "havc_style", plugins_url( '/assets/havc.css', __FILE__ ) );
	wp_register_style("font-awesome" , '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'); 
			
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'havc_enqueue_style');
if( !function_exists('havc_enqueue_style')) {
	function havc_enqueue_style(){
		wp_enqueue_style( "havc_icons_font" );
		wp_enqueue_style("havc_style");
		wp_enqueue_style("font-awesome");
	}
}
if( !function_exists('havc_admin_style')) {
	function havc_admin_style() {
	        wp_enqueue_style( 'havc_admin_css', plugins_url('/assets/admin-style.css', __FILE__) );
	}
}
add_action( 'admin_enqueue_scripts', 'havc_admin_style' );
final class HAVC extends HavcBase{

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const addons_dir = 'addons';
	const min_vc_version = '4.0';

	/**
	 * Single Instance
	 */
	private static $_instance = null;

	private $_modules_activated = array();
	private $_modules_installed = array();

	private $_module;
	private $_action;

	private $_admin_page;

	private $_plugin_data = array();
	
	/**
	 * Get Instance
	 */
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		// Initialize plugin data
		if( !function_exists( 'get_plugin_data' ) ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->_plugin_data = get_plugin_data(__FILE__);

		$this->_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : false;
		$this->_module = isset($_GET['module']) ? sanitize_text_field($_GET['module']) : false;

		// Register an activation hook for the plugin
		register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );

		// Register a deactivation hook for the plugin
		register_deactivation_hook( __FILE__, array( $this, 'deactivation_hook' ) );

		// Run plugins
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Run plugins
	 */
	public function init(){

		// Check dependencies
		if(!$this->is_vc_activated()) return false;

		// Check ompatibilities
		if(!$this->is_vc_version_compatible()) return false;

		$this->setup_localization();

		$this->load_modules(true);

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	/**
	 * Run on admin_init action
	 */
	public function admin_init(){
		$nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : false;

		switch ($this->_action) {

			case 'activate_module':
					if( wp_verify_nonce( $nonce, 'activate_module' ) && $this->_module ) {
						$this->activate_module($this->_module);
					}else{
						wp_die( __('Invalid request!', HAVC_SLUG) );
					}
				break;

			case 'deactivate_module':
					if( wp_verify_nonce( $nonce, 'deactivate_module' ) && $this->_module ) {
						$this->deactivate_module($this->_module);
					}else{
						wp_die( __('Invalid request!', HAVC_SLUG) );
					}
				break;
			
			default:
				# code...
				break;
		}

	}

	/**
	 * Add admin page
	 */
	public function admin_menu(){
		$this->_admin_page = add_menu_page( 
			$this->_plugin_data['Name'], 
			'HAVC', 
			'manage_options', 
			HAVC_SLUG, 
			array($this, 'render_admin_page')
		);

		add_action('load-'.$this->_admin_page, array($this, 'flush_modules'));
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page(){
	?>
	<div class="wrap">
	<h2><?php echo $this->_plugin_data['Name']; ?></h2>
	<ul class="subsubsub">
		<li class="all"><a href="<?php echo add_query_arg(array('page' => HAVC_SLUG), admin_url( 'admin.php' )) ;?>">All <span class="count">(<?php echo count($this->_modules_installed); ?>)</span></a> |</li>
		<li class="active"><a href="<?php echo add_query_arg(array('page' => HAVC_SLUG, 'filter' => 'active'), admin_url( 'admin.php' )) ;?>">Active <span class="count">(<?php echo count($this->_modules_activated); ?>)</span></a> |</li>
		<li class="inactive"><a href="<?php echo add_query_arg(array('page' => HAVC_SLUG, 'filter' => 'inactive'), admin_url( 'admin.php' )) ;?>">Inactive <span class="count">(<?php echo (count($this->_modules_installed) - count($this->_modules_activated)); ?>)</span></a></li>
	</ul>
	<table class="wp-list-table widefat plugins">
		<thead>
		<tr>
			<th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Module', HAVC_SLUG); ?></th>
			<th scope="col" id="description" class="manage-column column-description" style=""><?php _e('Description', HAVC_SLUG); ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
			<?php
			foreach ($this->_modules_installed as $key => $module) {
				$show = true;
				$filter = isset($_GET['filter']) ? $_GET['filter'] : false;
				if($filter){
					switch ($filter) {
						case 'active':
								if(FALSE === $this->is_module_active($key)){
									$show = FALSE;
								}
							break;

						case 'inactive':
								if(TRUE === $this->is_module_active($key)){
									$show = FALSE;
								}
							break;
						
						default:
							# code...
							break;
					}
				}
				if(!$show){
					continue;
				}

				$name = (!empty($module['data']['name'])) ? $module['data']['name'] : $key;
				$description = (!empty($module['data']['description'])) ? $module['data']['description'] : __('No description available', HAVC_SLUG);

				$row_actions = array();
				if($this->is_module_active($key)){
					$row_actions['deactivate'] = sprintf('<span class="deactivate"><a href="%s">%s</a></span>', 
						wp_nonce_url( 
							add_query_arg( 
								array(
									'page' => HAVC_SLUG,
									'module' => $key,
									'action' => 'deactivate_module'
								),
								admin_url( 'admin.php' )
							), 
							'deactivate_module'
						), 
						__('Deactivate', HAVC_SLUG) 
					);
					$row_class = 'active';
				}else{
					$row_actions['activate'] = sprintf('<span class="activate"><a href="%s">%s</a></span>', 
						wp_nonce_url( 
							add_query_arg( 
								array(
									'page' => HAVC_SLUG,
									'module' => $key,
									'action' => 'activate_module',
								),
								admin_url( 'admin.php' )
							), 
							'activate_module'
						), 
						__('Activate', HAVC_SLUG) 
					);
					$row_class = 'inactive';
				}
				if($this->is_module_active($key)){
					$row_actions = apply_filters('havc_module_row_actions', $row_actions, $key);
				}
				
				$row_metas = array();
				$version = (!empty($module['data']['version'])) ? $module['data']['version'] : false;
				if($version){
					$row_metas['version'] = sprintf('<span class="version">%s %s</span>', 
						__('Version', HAVC_SLUG),
						$version
					);
				}
				$author_name = (!empty($module['data']['author_name'])) ? $module['data']['author_name'] : false;
				$author_url = (!empty($module['data']['author_url'])) ? $module['data']['author_url'] : false;
				if($author_name){
					if($author_url){
						$row_metas['author'] = sprintf('<span class="author">%s <a href="%s">%s</a></span>', 
							__('By', HAVC_SLUG),
							$author_url, 
							$author_name
						);
					}else{
						$row_metas['author'] = sprintf('<span class="author">%s %s</span>', 
							__('By', HAVC_SLUG),
							$author_name
						);
					}
				}
				$row_metas = apply_filters('havc_module_row_metas', $row_metas, $key);
			?>
			<tr id="huge-addons-for-visual-composer" class="<?php echo $row_class; ?>" data-slug="">
				<td class="plugin-title"><strong><?php echo $name; ?></strong>
				<div class="row-actions visible">
				<?php echo implode( " | ", $row_actions ); ?>
				</div>
				</td>
				<td class="column-description desc">
					<div class="plugin-description"><p><?php echo $description; ?></p></div>
					<div class="row-metas visible">
					<?php echo implode( " | ", $row_metas ); ?>
					</div>
				</td>
			</tr>
			<?php
			}
			?>
		</tbody>
		<tfoot>
		<tr>
			<th scope="col" class="manage-column column-name" style=""><?php _e('Module', HAVC_SLUG); ?></th>
			<th scope="col" class="manage-column column-description" style=""><?php _e('Description', HAVC_SLUG); ?></th>
		</tr>
		</tfoot>
	</table>
	</div>
	<?php
	}

	/**
	 * Load modules
	 */
	private function load_modules( $run = false ){

		$this->_modules_activated = get_option( 'havc_modules', array() );

		foreach(glob($this->get_module_dir()."/*", GLOB_ONLYDIR) as $dir){
			$module_dir = basename($dir);
			foreach(glob($dir."/*.php") as $module_file){
				$file_data = get_file_data($module_file, $this->get_module_data());
				if(!empty($file_data['name'])){
					$this->_modules_installed[$module_dir] = array(
						'data' => $file_data,
						'file' => basename($module_file)
					);
					break;
				}
			}
		}

		if($run){
			$this->run_modules();
		}

	}

	/**
	 * Run all activated modules
	 */
	private function run_modules(){
		foreach ($this->_modules_activated as $key => $module) {
			if($this->is_module_exists($key, $module['file'])){
				require_once( $this->get_module_path($key, $module['file']) );
			}
		}
	}

	/**
	 * Flush activated modules data
	 */
	public function flush_modules(){
		foreach ($this->_modules_activated as $key => $module) {
			if(isset($this->_modules_installed[$key]) && $this->is_module_exists($key, $module['file'])){
				$this->_modules_activated[$key] = $this->_modules_installed[$key];
			}else{
				unset($this->_modules_activated[$key]);
				$this->add_admin_notice('error', sprintf(__('The module %s has been deactivated due to an error: Module file does not exist.', HAVC_SLUG), $module['data']['name']));
			}
		}
		$this->save_activated_modules(  );
	}

	/**
	 * Save all activated modules data to database
	 */
	private function save_activated_modules(){
		return update_option( 'havc_modules', $this->_modules_activated );
	}

	/**
	 * Delete all activated modules data from database
	 */
	private function delete_activated_modules(){
		return delete_option( 'havc_modules' );
	}

	/**
	 * Check if module is activated
	 */
	private function is_module_active($module){
		return isset( $this->_modules_activated[$module] );
	}

	/**
	 * Check if module file is exists
	 */
	private function is_module_exists($dir, $file){
		return file_exists( $this->get_module_path( $dir, $file ) );
	}

	/**
	 * Activate module
	 */
	private function activate_module( $module ){
		if(!$this->is_module_active( $module )){
			$this->_modules_activated[$module] = $this->_modules_installed[$module];
			$this->save_activated_modules( );
			$this->add_admin_notice('updated', __('Module activated.', HAVC_SLUG));
			$this->run_modules();
			do_action('havc_module_activated', $module, $this->_modules_activated[$module]);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Deactivate module
	 */
	private function deactivate_module( $module ){
		if($this->is_module_active( $module )){
			do_action('havc_module_deactivated', $module, $this->_modules_activated[$module]);
			unset($this->_modules_activated[$module]);
			$this->save_activated_modules( );
			$this->add_admin_notice('updated', __('Module deactivated.', HAVC_SLUG));
			$this->run_modules();
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Get modules base directory
	 */
	private function get_module_dir(){
		return HAVC_PATH . DIRECTORY_SEPARATOR . self::addons_dir . DIRECTORY_SEPARATOR;	
	}

	/**
	 * Get module file path
	 */
	private function get_module_path( $dir, $file ){
		return $this->get_module_dir() . $dir . DIRECTORY_SEPARATOR . $file;	
	}

	/**
	 * Get modules header data
	 */
	private function get_module_data(){
		return array(
			'name' => 'HAVC Module', 
			'description' => 'Description', 
			'version' => 'Version',
			'author_name' => 'Author Name',
			'author_url' => 'Author URL'
		);
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	public function activation_hook() {

		// Check dependencise
		if( !$this->is_vc_activated() ) {
			die( sprintf( __( 'You must install and activate WPBakery Visual Composer plugin before activating this plugin.', HAVC_SLUG ) ) );
		}

		//Check compatibility
		if( !$this->is_vc_version_compatible() ) {
			die( sprintf( __( 'This plugin requires WPBakery Visual Composer plugin version %s or greater', HAVC_SLUG ), self::min_vc_version ) );
		}

		if( FALSE === get_option( 'havc_first_install_time' ) ){
			$this->load_modules();
			if($this->_modules_installed){
				foreach ( $this->_modules_installed as $key => $value ) {
					$this->_modules_activated[$key] = $value;
				}
			}
			$this->save_activated_modules( );
			update_option( 'havc_first_install_time', current_time('timestamp') );
		}
	}
  
	/**
	 * Runs when the plugin is deactivated
	 */  
	public function deactivation_hook() {
	}

  
	/**
	 * Check if VC plugin is activated
	 */  
	private function is_vc_activated() {
		if(defined( 'WPB_VC_VERSION' ))return true;
		//return is_plugin_active( 'js_composer/js_composer.php' );
	}
  
	/**
	 * Check if VC plugin version is compatible
	 */  
	private function is_vc_version_compatible() {
		if( !defined('WPB_VC_VERSION') ) return false;
		return version_compare( WPB_VC_VERSION,  self::min_vc_version, '>' );
	}
  
	/**
	 * Setup localization
	 */
	public function setup_localization() {
		load_plugin_textdomain( HAVC_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}
  
} // end class

HAVC::getInstance();