<?php
/*
Plugin Name: Connections Export
Plugin URI: 
Description: 
Version: 0.1
Author: 
Author URI: 
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if (!class_exists('connectionsExportLoad')) {
	class connectionsExportLoad {
		public $options;
		public $settings;
		public function __construct() {
			
			self::defineConstants();
			
			add_filter('cn_list_atts_permitted', array(__CLASS__, 'expand_atts_permitted'));
			
			if (is_admin()) {
				add_action('plugins_loaded', array( $this, 'start' ));
				add_filter('cn_submenu', array(  __CLASS__, 'addMenu' ));

				// Since we're using a custom field, we need to add our own sanitization method.
				//add_filter( 'cn_meta_sanitize_field-last_emailed', array( __CLASS__, 'sanitize') );
				
				if (isset($_REQUEST['start_export'])) {// Check if option save is performed
					$this->doExport();
				}
				
				
				
			}
			
			
			
			
			/*
			 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
			 * Init the registered settings.
			 * NOTE: The init method must be run after registering the tabs, sections and fields.
			 */
			$this->settings = cnSettingsAPI::getInstance();
			add_filter( 'cn_register_settings_sections' , array( $this, 'registerSettingsSections' ) );
			add_filter( 'cn_register_settings_fields' , array( $this, 'registerSettingsFields' ) );
			
			$this->settings->init();

			add_action( 'wp_print_styles', array( $this, 'loadStyles' ) );
			add_action( 'init', array($this, 'loadJs') );
			//add_filter('wp_head', array($this, 'add_cnexpsh_data'));


		}
		private function defineConstants() {
			define( 'CNEXPORT_CURRENT_VERSION', '1.0.2' );
			define( 'CNEXPORT_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNEXPORT_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNEXPORT_BASE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNEXPORT_BASE_URL', plugin_dir_url( __FILE__ ) );
		}
		public function start() {
			if (class_exists('connectionsLoad')) {
				//load_plugin_textdomain( 'connections_emails' , false , CNFM_DIR_NAME . '/lang' );//comeback to 
			} else {
				add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order to use Form.</p></div>\';'));
			}
		}
		/**
		 * Adds the menu as a sub item of Connections.
		 *
		 * @access  private
		 * @since  unkown
		 * @param array $menu
		 * @return array
		 */
		public static function addMenu($menu) {
			$menu[72] = array(
				'hook' => 'export',
				'page_title' => 'Connections : Export',
				'menu_title' => 'Export',
				'capability' => 'connections_add_entry',
				'menu_slug' => 'connections_export',
				'function' => array(
					__CLASS__,
					'showPage'
				)
			);
			return $menu;
		}
		/**
		 * Renders the admin page.
		 *
		 * @access  private
		 * @since  unknown
		 * @return void
		 */
		public static function showPage() {
			if (!isset($_GET['page']))
				return;
			switch ($_GET['page']) {
				case 'connections_export':
					include_once(dirname(__FILE__) . '/admin/pages/export.php');
					connectionsExportPage();
					break;
			}
		}
		
		
		
		public function init() { }
		/**
		 * Called when running the wp_print_styles action.
		 *
		 * @return null
		 */
		public function loadStyles() {
			//if ( ! is_admin() ) wp_enqueue_style('cn-expsearch', CNEXSCH_BASE_URL . 'css/cn-expsearch.css', array(), CNEXSCH_CURRENT_VERSION);
			
		}		
		public static function expand_atts_permitted($permittedAtts){
			$permittedAtts['mode'] = NULL;
			$permittedAtts['fields'] = NULL;
			$permittedAtts['hide_empty'] = True;
			$permittedAtts['theme_file'] = NULL;
			
			return $permittedAtts;
		}

		public function loadJs(){
			if ( ! is_admin() ){ 
				//wp_enqueue_script( 'jquery-chosen-min' );
				//wp_enqueue_script( 'cn-expsearch' , CNEXSCH_BASE_URL . 'js/cn-expsearch.js', array('jquery') , CNEXSCH_CURRENT_VERSION , TRUE );
			}
		}

		/**
		 * Register the settings sections.
		 *
		 * @author Steven A. Zahm
		 * @since 0.4
		 * @param array $sections
		 * @return array
		 */
		public function registerSettingsSections( $sections ) {
			global $connections;

			$settings = 'connections_page_connections_settings';

			// Register the core setting sections.
			$sections[] = array(
				'tab'       => 'search' ,
				'id'        => 'connections_export_exp_defaults' ,
				'position'  => 1 ,
				'title'     => __( 'Exporting defaults' , 'connections_export' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			return $sections;
		}

		public function registerSettingsFields( $fields ) {
			$current_user = wp_get_current_user();

			$settings = 'connections_page_connections_settings';

			$fields[] = array(
				'plugin_id' => 'connections_export',
				'id'        => 'use_geolocation',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_export_exp_defaults',
				'title'     => __('Add geo location to the search', 'connections_export'),
				'desc'      => __('', 'connections_export'),
				'help'      => __('', 'connections_export'),
				'type'      => 'checkbox',
				'default'   => 1
			);
			/*$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'use_autosearch',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_exp_defaults',
				'title'     => __('Use the auto geo location search service', 'connections_expsearch'),
				'desc'      => __('NOTE: you can use css to configure your alert to match you site.', 'connections_expsearch'),
				'help'      => __('', 'connections_expsearch'),
				'type'      => 'checkbox',
				'default'   => 1
			);			
			$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'visiable_search_fields',
				'position'  => 10.1,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_exp_defaults',
				'title'     => __('Choose the visible on search form fields', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'multiselect',
				'options'   => $this->getSearchFields(),
				'default'   => array('region','category','keyword')
			);
			$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'unit',
				'position'  => 10.2,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_exp_defaults',
				'title'     => __('The default units for geo location service', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'select',
				'options'   => $this->getUnitOptions(),
				'default'   => array()
			);			
			$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'radius',
				'position'  => 10.3,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_exp_defaults',
				'title'     => __('The default Radius for geo location service', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'text',
				'default'   => '50'
			);	*/
			
			return $fields;
		}

		/*
		* Get the units that the geo service can use
		* returns array
		*/
		public function getUnitOptions(){
			$options = array(
				'mi'=>__('Miles', 'connections'),
				'km'=>__('Kilometre', 'connections')
			);
			return $options;
		}
		
		//Note this is hard coded for the tmp need to finish a site
		public function getSearchFields(){
			
			$fields = array(
				'region'=>__('Region', 'connections'),
				'country'=>__('Country', 'connections'),
				'category'=>__('Category', 'connections'),
				'keywords'=>__('Keywords', 'connections')
			);
			
			return $fields;
		}

		/*
		* Do search action 
		* returns string - The html of the search results
		*/
		public function doExport() {
			global $post,$connections, $wpdb;
			require_once(dirname( __FILE__ ) . '/includes/exporter.php');//temp correct later
		}
		
		
	}



	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return mixed (object)|(bool)
	 */
	function connectionsExportLoad() {
		if ( class_exists('connectionsLoad') ) {
			return new connectionsExportLoad();
		} else {
			add_action(
				'admin_notices',
				 create_function(
					 '',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Exporting.</p></div>\';'
				)
			);
			return FALSE;
		}
	}
	
	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'connectionsExportLoad', 11 );
}