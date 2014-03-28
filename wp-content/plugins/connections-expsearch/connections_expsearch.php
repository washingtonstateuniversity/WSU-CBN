<?php
/*
Plugin Name: Connections ExpSearch
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
if (!class_exists('connectionsExpSearchLoad')) {
	class connectionsExpSearchLoad {
		public $options;
		public $settings;
		public function __construct() {
			
			self::defineConstants();
			
			add_filter('cn_list_atts_permitted', array(__CLASS__, 'expand_atts_permitted'));
			/*
			 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
			 * Init the registered settings.
			 * NOTE: The init method must be run after registering the tabs, sections and fields.
			 */
			$this->settings = cnSettingsAPI::getInstance();
			add_filter( 'cn_register_settings_sections' , array( $this, 'registerSettingsSections' ) );
			add_filter( 'cn_register_settings_fields' , array( $this, 'registerSettingsFields' ) );
			
			$this->settings->init();
			
			
			add_shortcode( 'connections_search', array( $this, 'shortcode') );
			require_once(dirname( __FILE__ ) . '/includes/class.template-parts-extended.php');//temp correct later

			add_action( 'wp_print_styles', array( $this, 'loadStyles' ) );
			add_action( 'init', array($this, 'loadJs') );
			add_filter('wp_head', array($this, 'add_cnexpsh_data'));
			if (isset($_POST['start_search'])) {// Check if option save is performed
				add_filter('the_content', array( $this, 'doSearch' ));
			}
		}
		private function defineConstants() {
			define( 'CNEXSCH_CURRENT_VERSION', '1.0.2' );
			define( 'CNEXSCH_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNEXSCH_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNEXSCH_BASE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNEXSCH_BASE_URL', plugin_dir_url( __FILE__ ) );
		}
		
		public function init() { }
		/**
		 * Called when running the wp_print_styles action.
		 *
		 * @return null
		 */
		public function loadStyles() {
			if ( ! is_admin() ) wp_enqueue_style('cn-expsearch', CNEXSCH_BASE_URL . 'css/cn-expsearch.css', array(), CNEXSCH_CURRENT_VERSION);
			
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
				wp_enqueue_script( 'jquery-chosen-min' );
				wp_enqueue_script( 'cn-expsearch' , CNEXSCH_BASE_URL . 'js/cn-expsearch.js', array('jquery') , CNEXSCH_CURRENT_VERSION , TRUE );
			}
		}
		// Add items to the footer
		function add_cnexpsh_data() {
			global $connections;
			$homeID = $connections->settings->get( 'connections', 'connections_home_page', 'page_id' );
			if ( in_the_loop() && is_page() ) {
				$permalink = trailingslashit ( get_permalink() );
			} else {
				$permalink = trailingslashit ( get_permalink( $homeID ) );
			}


			$use_geolocation	= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'use_geolocation' );
			$radius				= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'radius' );
			$unit				= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'unit' );			
			$homeID 			= $connections->settings->get( 'connections', 'connections_home_page', 'page_id' );
			$use_autoSearch		= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'use_autosearch' );
			echo '
			<script type="text/javascript">
				var cn_search_use_geolocation = '. ($use_geolocation?1:0).';
				var cn_search_use_autosearch = '. ($use_autoSearch?1:0).';
				var cn_search_form_url = "'.$permalink.'";
				var cn_search_radius = "'.$radius.'";
				var cn_search_unit = "'.$unit.'";
			</script>';
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
				'id'        => 'connections_expsearch_exp_defaults' ,
				'position'  => 1 ,
				'title'     => __( 'Expanded Search defaults' , 'connections_expsearch' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			return $sections;
		}

		public function registerSettingsFields( $fields ) {
			$current_user = wp_get_current_user();

			$settings = 'connections_page_connections_settings';

			$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'use_geolocation',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_exp_defaults',
				'title'     => __('Add geo location to the search', 'connections_expsearch'),
				'desc'      => __('', 'connections_expsearch'),
				'help'      => __('', 'connections_expsearch'),
				'type'      => 'checkbox',
				'default'   => 1
			);
			$fields[] = array(
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
			);	
			
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
		public function doSearch() {
			global $post,$connections;
			$permittedAtts = array(
				'id'                    => NULL,
				'slug'                  => NULL,
				'category'              => isset($_POST['cn-cat'])&& !empty($_POST['cn-cat']) ?$_POST['cn-cat']:NULL,
				'enable_category_select'	=>false,
				'enable_search'			=> false,
				'cards_only'			=> true,
				/*'category_in'           => NULL,
				'exclude_category'      => NULL,
				'category_name'         => NULL,
				'category_slug'         => NULL,
				'wp_current_category'   => 'false',
				'allow_public_override' => 'false',
				'private_override'      => 'false',
				'show_alphaindex'       => cnSettingsAPI::get( 'connections', 'connections_display_results', 'index' ),
				'repeat_alphaindex'     => cnSettingsAPI::get( 'connections', 'connections_display_results', 'index_repeat' ),
				'show_alphahead'        => cnSettingsAPI::get( 'connections', 'connections_display_results', 'show_current_character' ),
				'list_type'             => NULL,
				'order_by'              => NULL,
				'limit'                 => NULL,
				'offset'                => NULL,
				'family_name'           => NULL,
				'last_name'             => NULL,
				'title'                 => NULL,*/
				'show_alphaindex'       => false,
				'repeat_alphaindex'     => false,
				'show_alphahead'       	=> false,
				//'organization'          => isset($_POST['cn-keyword']) && !empty($_POST['cn-keyword'])?$_POST['cn-keyword']:NULL,
				'department'            => NULL,
				'city'                  => NULL,
				'state'                 => isset($_POST['cn-state']) && !empty($_POST['cn-state'])?$_POST['cn-state']:NULL,
				/*'zip_code'              => NULL,*/
				'country'               => isset($_POST['cn-country']) && !empty($_POST['cn-country'])?$_POST['cn-country']:NULL,
				'template'              => NULL, /* @since version 0.7.1.0 */
				'template_name'         => NULL, /* @deprecated since version 0.7.0.4 */
				'width'                 => NULL,
				'lock'                  => FALSE,
				'force_home'            => FALSE,
				'search_terms'  		=> isset($_POST['cn-keyword']) && !empty($_POST['cn-keyword'])?$_POST['cn-keyword']:"",
				'home_id'               => in_the_loop() && is_page() ? get_the_id() : cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			);
			
			if( (isset($_POST['cn-latitude']) && isset($_POST['cn-longitude']) && !empty($_POST['cn-latitude']) && !empty($_POST['cn-longitude'])) || (isset($_POST['cn-near_addr']) && !empty($_POST['cn-near_addr'])) ){
				$locationalPermittedAtts = array(
					'near_addr'		=> isset($_POST['cn-near_addr']) && !empty($_POST['cn-near_addr'])?"":NULL,
					'latitude'		=> isset($_POST['cn-latitude']) && !empty($_POST['cn-latitude'])?"":NULL,
					'longitude'		=> isset($_POST['cn-longitude']) && !empty($_POST['cn-longitude'])?"":NULL,
					'radius'		=> isset($_POST['cn-near_addr']) && !empty($_POST['cn-near_addr'])?"":10,
					'unit'			=> isset($_POST['cn-near_addr']) && !empty($_POST['cn-near_addr'])?"":'mi',
				);
				$permittedAtts = array_merge($permittedAtts,$locationalPermittedAtts);
			}
			
			$out = '';
//var_dump($permittedAtts);die();
			$results = $connections->retrieve->entries( $permittedAtts );
			//var_dump($results);die();
			set_transient( "results", $results, 0 );	
			set_transient( "atts", $permittedAtts, 0 );	
			ob_start();
				if ( $overridden_template = locate_template( 'searchResults.php' ) ) {
					load_template( $overridden_template );
				} else {
					load_template( dirname( __FILE__ ) . '/templates/searchResults.php' );
				}
				$out .= ob_get_contents();
			ob_end_clean();				

			// Output the the search input.
			return $out;
		}
		
		
		
				
		/**
		 * @todo: Add honeypot fields for bots.
		 */
		public function shortcode( $atts , $content = NULL ) {
			global $connections;

			$convert = new cnFormatting();
			$format =& $convert;
			$formObject = array();

			$atts = shortcode_atts(
				array(
					'default_type'		=> 'individual',
					'show_label'		=> TRUE,
					'select_type'		=> TRUE,
					'photo'				=> FALSE,
					'logo'				=> FALSE,
					'address'			=> TRUE,
					'phone'				=> TRUE,
					'email'				=> TRUE,
					'messenger'			=> TRUE,
					'social'			=> TRUE,
					'link'				=> TRUE,
					'anniversary'		=> FALSE,
					'birthday'			=> FALSE,
					'category'			=> TRUE,
					'rte'				=> TRUE,
					'bio'				=> TRUE,
					'notes'				=> FALSE,
					'str_contact_name'	=> __( 'Entry Name' , 'connections_form' ),
					'str_bio'			=> __( 'Biography' , 'connections_form' ),
					'str_notes'			=> __( 'Notes' , 'connections_form' )
				), $atts );

			$defaults = array(
				'show_label' => TRUE
			);
		
			$atts = wp_parse_args( $atts, $defaults );

			$formObject = $atts;
			set_transient( "formObject", $formObject, 0 );	
			ob_start();
				if ( $overridden_template = locate_template( 'searchForm.php' ) ) {
					load_template( $overridden_template );
				} else {
					load_template( dirname( __FILE__ ) . '/templates/searchForm.php' );
				}
				$out .= ob_get_contents();
			ob_end_clean();				

			// Output the the search input.
			return $out;
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
	function connectionsExpSearchLoad() {
		if ( class_exists('connectionsLoad') ) {
			return new connectionsExpSearchLoad();
		} else {
			add_action(
				'admin_notices',
				 create_function(
					 '',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Extended Search.</p></div>\';'
				)
			);
			return FALSE;
		}
	}
	
	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'connectionsExpSearchLoad', 11 );
}