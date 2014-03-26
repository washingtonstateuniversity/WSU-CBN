<?php
/**
 * Class WNPA_Feed_Item
 *
 * Manage the feed item content type used by the WNPA Syndication plugin.
 */
if (!class_exists('Connections_Levels')) {
    class Connections_Levels {
        public static $options;
        public function __construct() {
            //self::loadConstants();
            //self::loadDependencies();
            //self::initOptions();
            //register_activation_hook( dirname(__FILE__) . '/connections_levels.php', array( __CLASS__, 'activate' ) );
            if (is_admin()) {
                add_action('plugins_loaded', array( $this, 'start' ));
                //add_filter('cn_submenu', array(  __CLASS__, 'addMenu' ));
				
				
				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-entry_level', array( __CLASS__, 'sanitize') );
				add_filter( 'cncsv_map_import_fields', array( __CLASS__, 'map_import_fields' ));
				add_action( 'cncsv_import_fields', array($this, 'import_fields' ),10,2);
            }
			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );
			
			// Business Hours uses a custom field type, so let's add the action to add it.
			add_action( 'cn_meta_field-entry_level', array( __CLASS__, 'field' ), 10, 2 );
			
			add_action( 'cn_meta_output_field-cnlevels', array( __CLASS__, 'block' ), 10, 3 );
        }
        public function start() {
            if (class_exists('connectionsLoad')) {
                //load_plugin_textdomain( 'connections_levels' , false , CNFM_DIR_NAME . '/lang' );//comeback to 
                $this->settings = cnSettingsAPI::getInstance();
                /*
                 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
                 * Init the registered settings.
                 * NOTE: The init method must be run after registering the tabs, sections and fields.
                 */
                add_filter('cn_register_settings_tabs', array( $this, 'registerSettingsTab' ));
                add_filter('cn_register_settings_sections', array( $this, 'registerSettingsSections' ));
                add_filter('cn_register_settings_fields', array( $this, 'registerSettingsFields' ));
                $this->settings->init();
                //add_action( 'admin_init' , array( $this, 'adminInit' ) );
                add_action('init', array( $this, 'init' ));
            } else {
                add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order to use Form.</p></div>\';'));
            }
        }
        public function adminInit() {
        }
        public function init() {
        }
		public static function map_import_fields( $fields ){
			$fields['cnlevels'] = 'Membership Level | Level';
			return $fields;
		}
		public  function import_fields( $entryId, $row ){
			$tmp='';	
			if( isset($row->cnlevels) ){
				$tmp = $row->cnlevels;
			}
			cnEntry_Action::meta('add', $entryId, array(
				array(
					'key' => "cnlevels",
					'value' =>$tmp
				)
			));
		}		
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_levels';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_lang_dir', CNBH_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_wp_lang_dir',
				WP_LANG_DIR . '/connections-emails/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
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
            $menu[70] = array(
                'hook' => 'emails',
                'page_title' => 'Connections : Levels',
                'menu_title' => 'Levels',
                'capability' => 'connections_add_entry',
                'menu_slug' => 'connections_levels',
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
                case 'connections_levels':
                    include_once(dirname(__FILE__) . '/admin/pages/emails.php');
                    connectionsLevelsPage();
                    break;
            }
        }
		
		
		
		
		
		
		
		
		
		public static function registerMetabox( $metabox ) {
			$atts = array(
				'id'       => 'leveled',
				'title'    => __( 'Membership Levels', 'sent_datetime' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
						array(
								'id'    => 'cnlevels',
								'type'  => 'entry_level',
								),
						),
				);
			$metabox::add( $atts );
		}
		public static function field( $field, $value ) {
			
			$levels = array(
				//'pending'=>__('Pending', 'connections_levels' ),
				'member'=>__('Member', 'connections_levels' ),
				'affiliate'=>__('Affiliate', 'connections_levels' )
			);			
			
			$out ='<select name="cnlevels" >';
			$out .='<option value="">'.__('Must choose', 'connections_levels' ).'</option>';	
			//this would be pulled from the ?options?
		
			foreach($levels as $slug=>$label){
				$out .='<option value="'.$slug.'" '.selected($value, $slug, false).'>'.$label.'</option>';	
			}
			$out .='</select>';
			
			printf( '%s', $out);	
		}

		/**
		 * Sanitize the value as a text input using the cnSanitize class.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  text $value   date string.
		 *
		 * @return text
		 */
		public static function sanitize( $value ) {

			$value=cnSanitize::string( 'text', $value );

			return $value;
		}
		
		
		
		
		
    }
	
	
	
	
	
	
	
	
	
	
	
    /**
     * Start up the extension.
     *
     * @access public
     * @since 1.0
     * @return mixed (object)|(bool)
     */
    function Connections_Levels() {
        if (class_exists('connectionsLoad')) {
            return new Connections_Levels();
        } else {
            add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Levels.</p></div>\';'));
            return FALSE;
        }
    }
    /**
     * Since Connections loads at default priority 10, and this extension is dependent on Connections,
     * we'll load with priority 11 so we know Connections will be loaded and ready first.
     */
    add_action('plugins_loaded', 'Connections_Levels', 11);
}

