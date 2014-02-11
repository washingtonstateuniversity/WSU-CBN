<?php
/*
Plugin Name: Connections ExpSearch
Plugin URI: 
Description: 
Version: 0.1
Author: 
Author URI: 
*/

if (!class_exists('connectionsExpSearchLoad')) {
	class connectionsExpSearchLoad {
		public $options;
		public $settings;
		public function __construct() {
			$this->loadConstants();
			add_action( 'plugins_loaded', array( $this , 'start' ) );
		}
		
		public function start() {
			
			
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
			
			if (isset($_POST['start_search'])) {// Check if option save is performed
				add_filter('the_content', array( $this, 'doSearch' ));
			}
		}
		private function loadConstants() {

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
			if ( ! is_admin() )wp_enqueue_script( 'cn-expsearch' , CNEXSCH_BASE_URL . 'js/cn-expsearch.js', array('jquery') , CNEXSCH_CURRENT_VERSION , TRUE );
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
				'id'        => 'connections_expsearch_defaults' ,
				'position'  => 20 ,
				'title'     => __( 'Search defaults' , 'connections_expsearch' ) ,
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
				'section'   => 'connections_expsearch_defaults',
				'title'     => __('Add geo location to the search', 'connections_expsearch'),
				'desc'      => __('', 'connections_expsearch'),
				'help'      => __('', 'connections_expsearch'),
				'type'      => 'checkbox',
				'default'   => 1
			);
			$fields[] = array(
				'plugin_id' => 'connections_expsearch',
				'id'        => 'visiable_search_fields',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'search',
				'section'   => 'connections_expsearch_defaults',
				'title'     => __('Choose the visible on search form fields', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'multiselect',
				'options'   => $this->getSearchFields(),
				'default'   => array('region','category','keyword')
			);
			return $fields;
		}


		//Note this is hard coded for the tmp need to finish a site
		public function getSearchFields(){
			
			$fields = array(
				'region'=>'Region',
				'country'=>'Country',
				'category'=>'Category',
				'keywords'=>'Keywords'
			);
			
			return $fields;
		}











		public function doSearch() {
			global $post,$connections;
			$permittedAtts = array(
				'id'                    => NULL,
				'slug'                  => NULL,
				'category'              => isset($_POST['cn-cat'])&& !empty($_POST['cn-cat']) ?$_POST['cn-cat']:NULL,
				'enable_category_select'	=>false,
				'enable_search'			=> false,
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
				'organization'          => isset($_POST['cn-keyword']) && !empty($_POST['cn-keyword'])?$_POST['cn-keyword']:NULL,
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
				'search_terms'  		=> isset($_POST['cn-keyword']) && !empty($_POST['cn-keyword'])?explode(' ',$_POST['cn-keyword']):array(),
				'home_id'               => in_the_loop() && is_page() ? get_the_id() : cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			);
			
			if( (isset($_POST['cn-latitude']) && isset($_POST['cn-longitude'])) || (isset($_POST['cn-near_addr'])) ){
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
			$categories = $connections->retrieve->categories();
			$opSortbyCat=true;//would be an option
			
			//var_dump($categories);
			//die();

			$results = $connections->retrieve->entries( $permittedAtts );
			
			if(!empty($results)){
			
			
				$markers = new stdClass();
				$markers->markers=array();
				foreach($results as $entry){
					$entryObj=new stdClass();
					$entryObj->id=$entry->id;
					$entryObj->title= $entry->organization;
					$entryObj->position=new stdClass();
					$addy = unserialize ($entry->addresses);
					$array = (array) $addy;
					$addy = array_pop($addy);
					if(!empty($addy['latitude']) && !empty($addy['longitude'])){
						$entryObj->position->latitude=$addy['latitude'];
						$entryObj->position->longitude=$addy['longitude'];
						$markers->markers[]= $entryObj;
					}
				}
				$markerJson=json_encode($markers);
	
	
				
				$out .= '
				<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
					<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
						
						<li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a href="#tabs-2">Listings</a></li>
						<li class="ui-state-default ui-corner-top"><a href="#tabs-1">Map</a></li>
					</ul>
				
					<div id="tabs-2" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
					';
					if($permittedAtts['category']==NULL){
						$state = isset($_POST['cn-state']) && !empty($_POST['cn-state'])?$_POST['cn-state'].' and ':'';
						foreach($categories as $cat){
							$permittedAtts['category']=$cat->term_id;
							$catblock = $connections->shortcode->connectionsList( $permittedAtts,NULL,'connections' );;
							//var_dump($catblock);
							if(!empty($catblock) && strpos($catblock,'No results')===false){
								$out .= '<h3>'.$state.$cat->name.'</h3>';
								$out .= '<div class="accordion">';
								$out .= $catblock;
								$out .= '</div>';
							}
						}
					}else{
						$state = isset($_POST['cn-state']) && !empty($_POST['cn-state'])?$_POST['cn-state'].' and ':'';
						$category = $connections->retrieve->category($permittedAtts['category']);
						$out .= '<h3>'.$state.$category->name.'</h3>';
						$out .= '<div class="accordion">';
						$out .= connectionsList( $permittedAtts,NULL,'connections' );
						$out .= '</div>';
					}
		
					$out .='
						</div>
						<div id="tabs-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom ">
							<h2>Hover on a point to find a business and click for more information</h2>
							<div id="mapJson">'.$markerJson.'</div>
							<div id="front_cbn_map" class="byState " rel="'.$_POST['cn-state'].'" style="width:100%;height:450px;"></div>
							<div class="ui-widget-content ui-corner-bottom" style="padding:5px 15px;">
								<div id="data_display"></div>
								<div style="clear:both;"></div>
							</div>
						</div>
					</div>';
			}else{
				$out = "No results";	
			}
			
			
			return $out;
		}
		
		
		
				
		/**
		 * @todo: Add honeypot fields for bots.
		 */
		public function shortcode( $atts , $content = NULL ) {
			global $connections;

			$date = new cnDate();
			$form = new cnFormObjects();
			$convert = new cnFormatting();
			$format =& $convert;
			$entry = new cnEntry();
			$out = '';


			
			
			$atts = shortcode_atts(
				array(
					'default_type'     => 'individual',
					'show_label'		=> TRUE,
					'select_type'      => TRUE,
					'photo'            => FALSE,
					'logo'             => FALSE,
					'address'          => TRUE,
					'phone'            => TRUE,
					'email'            => TRUE,
					'messenger'        => TRUE,
					'social'           => TRUE,
					'link'             => TRUE,
					'anniversary'      => FALSE,
					'birthday'         => FALSE,
					'category'         => TRUE,
					'rte'              => TRUE,
					'bio'              => TRUE,
					'notes'            => FALSE,
					'str_contact_name' => __( 'Entry Name' , 'connections_form' ),
					'str_bio'          => __( 'Biography' , 'connections_form' ),
					'str_notes'        => __( 'Notes' , 'connections_form' )
				), $atts );


			/*
			 * Convert some of the $atts values in the array to boolean.
			 */
			$convert->toBoolean($atts['select_type']);
			$convert->toBoolean($atts['photo']);
			$convert->toBoolean($atts['logo']);
			$convert->toBoolean($atts['address']);
			$convert->toBoolean($atts['phone']);
			$convert->toBoolean($atts['email']);
			$convert->toBoolean($atts['messenger']);
			$convert->toBoolean($atts['social']);
			$convert->toBoolean($atts['link']);
			$convert->toBoolean($atts['anniversary']);
			$convert->toBoolean($atts['birthday']);
			$convert->toBoolean($atts['category']);
			$convert->toBoolean($atts['rte']);
			$convert->toBoolean($atts['bio']);
			$convert->toBoolean($atts['notes']);
			//$out .= var_dump($atts);


			$visiblefields = $connections->settings->get( 'connections_expsearch' , 'connections_expsearch_defaults' , 'visiable_search_fields' );
			$use_geolocation = $connections->settings->get( 'connections_expsearch' , 'connections_expsearch_defaults' , 'use_geolocation' );
			// switch out for a template that can be changed. ie: {$category_select}, {$state_dropdown} etc.
			$out .= '<div id="cn-form-container">' . "\n";
				$out .= '<div id="cn-form-ajax-response"><ul></ul></div>' . "\n";
				$out .= '<form id="cn-search-form" method="POST" enctype="multipart/form-data">' . "\n";
	
					$defaults = array(
						'show_label' => TRUE
					);
			
					$atts = wp_parse_args( $atts, $defaults );	
					$searchValue = ( get_query_var('cn-s') ) ? get_query_var('cn-s') : '';

					if(in_array('category',$visiblefields)){
						$out .= '<div>';
						$out .= cnTemplatePartExended::flexSelect(
																$connections->retrieve->categories(
																	array(
																			'orderby'	=>array('parent','name'),
																			'order'		=>array('ASC','ASC')
																		)
																)
																,array(
																	'type'            => 'select',
																	'group'           => FALSE,
																	'default'         => __('Select a category', 'connections'),
																	'label'           => __('Search by category', 'connections'),
																	'show_select_all' => TRUE,
																	'select_all'      => __('Any', 'connections'),
																	'show_empty'      => TRUE,
																	'show_count'      => FALSE,
																	'depth'           => 0,
																	'parent_id'       => array(),
																	'exclude'         => array(),
																	'return'          => TRUE,
																	'class'				=>'search-select'
																));
						$out .= '<hr/></div>';
					}
					
					if(in_array('region',$visiblefields)){
						$out .= '<div>';
	
						$out 			.= '<label class="search-select"><strong>Search by state:</strong></label><br/>';
						$display_code 	= $connections->settings->get('connections_form', 'connections_form_preferences', 'form_preference_regions_display_code');
						$out          	.= '<select name="cn-state">';
						$out 			.= '<option value="" selected >Any</option>';
						foreach (cnDefaultValues::getRegions() as $code => $regions) {
							$lable = $display_code ? $code : $regions;
							$out .= '<option value="' . $code . '" >' . $lable . '</option>';
						}
						$out .= '</select>';
						$out .= '<hr/></div>';
					}

					if(in_array('keywords',$visiblefields)){
						$out .= '<div>';
						$out .= '<label for="cn-s"><strong>Keywords:</strong></label><br/>';
						$out .= '<span class="cn-search" style="width:50%; display:inline-block">';
							$out .= '<input type="text" id="cn-search-input" name="cn-keyword" value="' . esc_attr( $searchValue ) . '" placeholder="' . __('Search', 'connections') . '"/>';
						$out .= '</span>';
						$out .= '<hr/></div>';
					}
					
					if($use_geolocation){
						$out .= '<h2 ><a id="mylocation" style="" class="button" hidefocus="true" href="#">Search near my location</a></h2>';
						$out .= '<input type="hidden" name="cn-near_addr" />';
						$out .= '<input type="hidden" name="cn-latitude" />';
						$out .= '<input type="hidden" name="cn-longitude" />';
						$out .= '<input type="hidden" name="cn-radius" value="10" />';
						$out .= '<input type="hidden" name="cn-unit" value="mi" />';
					}
					$out .=  '<hr/><br/><p class="cn-add"><input class="cn-button-shell cn-button red" id="cn-form-search" type="submit" name="start_search" value="' . __('Submit' , 'connections_form' ) . '" /></p><br/>' . "\n";
	
				$out .= '</form>';
			$out .= '</div>';

			// Output the the search input.
			return $out;
		}		
		


		


	}
	
	/*
	 * Checks for PHP 5 or greater as required by Connections Pro and display an error message
	 * rather that havinh PHP thru an error.
	 */
	if (version_compare(PHP_VERSION, '5.0.0', '>')) {
		/*
		 * Initiate the plug-in.
		 */
		global $connectionsExpSearch;
		$connectionsExpSearch = new connectionsExpSearchLoad();
	} else {
		add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>Connections ROT13 requires at least PHP5. You are using version: ' . PHP_VERSION . '</strong></p></div>\';') );
	}
	
}