<?php
/**
 * Class WNPA_Feed_Item
 *
 * Manage the feed item content type used by the WNPA Syndication plugin.
 */
if (!class_exists('Connections_Education')) {
    class Connections_Education {
        public static $options;
        public function __construct() {
            //self::loadConstants();
            //self::loadDependencies();
            //self::initOptions();
            //register_activation_hook( dirname(__FILE__) . '/connections_education.php', array( __CLASS__, 'activate' ) );
            if (is_admin()) {
                add_action('plugins_loaded', array( $this, 'start' ));
				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-entry_education', array( __CLASS__, 'sanitize') );
				add_action( 'cncsv_map_import_fields', array( __CLASS__, 'map_import_fields' ));
				add_action( 'cncsv_import_fields', array( __CLASS__, 'import_fields' ), 10, 2);
            }
			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );
			
			// Business Hours uses a custom field type, so let's add the action to add it.
			add_action( 'cn_meta_field-entry_education', array( __CLASS__, 'field' ), 10, 2 );
			
			add_action( 'cn_meta_output_field-cneducation', array( __CLASS__, 'block' ), 10, 3 );
        }
        public function start() {
            if (class_exists('connectionsLoad')) {
                //load_plugin_textdomain( 'connections_education' , false , CNFM_DIR_NAME . '/lang' );//comeback to 
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
			$fields['cneducation_degree'] = 'Education | Degree';
			$fields['cneducation_year'] = 'Education | Year';
			$fields['cneducation_schoolid'] = 'Education | School ID';
			return $fields;
		}
		public static function import_fields( $entryId, $row ){
			$tmp=array(
				'degree'=>'',
				'year'=>'',
				'schoolid'=>''
			);	
			if( isset($row->cneducation_degree) ){
				$tmp['degree'] = $row->cneducation_degree;
			}
			if( isset($row->cneducation_year) ){
				$tmp['year'] = $row->cneducation_year;
			}
			if( isset($row->cneducation_schoolid) ){
				$tmp['schoolid'] = $row->cneducation_schoolid;
			}
			cnEntry_Action::meta('add', $entryId, array(
				array(
					'key' => "cneducation",
					'value' =>$tmp
				)
			));
			return;
		}	
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_education';

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

		
		
		
		
		public static function registerMetabox( $metabox ) {
			$atts = array(
				'id'       => 'educationed',
				'title'    => __( 'Education', 'said_education' ),
				'context'  => 'normal',
				'priority' => 'core',
				'fields'   => array(
						array(
								'id'    => 'cneducation',
								'type'  => 'entry_education',
							),
						),
				);
			$metabox::add( $atts );
		}
		public static function field( $field, $value ) {
			
			$out ='';
			$out .='<label>'.__('Degree', 'connections_education' ).'<br/><input type="text" name="cneducation[\'degree\']" value="'.(isset($value['degree'])?$value['degree']:"").'" required /></label><br/>';
			$out .='<label>'.__('Class Year', 'connections_education' ).'<br/><input type="text" name="cneducation[\'year\']" value="'.(isset($value['year'])?$value['year']:"").'" required /></label><br/>';
			$out .='<label>'.__('WSUid', 'connections_education' ).'<br/><input type="text" name="cneducation[\'schoolid\']" value="'.(isset($value['schoolid'])?$value['schoolid']:"").'"  /></label><br/>';	

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
			$return=array(
				'degree'=>isset($value['\'degree\''])?$value['\'degree\'']:"",
				'year'=>isset($value['\'year\''])?$value['\'year\'']:"",
				'schoolid'=>isset($value['\'schoolid\''])?$value['\'schoolid\'']:""
			);
			
			return $return ;
		}
		
		
		
		
		
    }
	
	
	
	
	
	
	
	
	
	
	
    /**
     * Start up the extension.
     *
     * @access public
     * @since 1.0
     * @return mixed (object)|(bool)
     */
    function Connections_Education() {
        if (class_exists('connectionsLoad')) {
            return new Connections_Education();
        } else {
            add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Education.</p></div>\';'));
            return FALSE;
        }
    }
    /**
     * Since Connections loads at default priority 10, and this extension is dependent on Connections,
     * we'll load with priority 11 so we know Connections will be loaded and ready first.
     */
    add_action('plugins_loaded', 'Connections_Education', 11);
}

