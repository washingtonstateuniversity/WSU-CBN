<?php
/**
 * Class WNPA_Feed_Item
 *
 * Manage the feed item content type used by the WNPA Syndication plugin.
 */
if (!class_exists('Connections_benefits')) {
    class Connections_benefits {
        public static $options;
        public function __construct() {
            //self::loadConstants();
            //self::loadDependencies();
            //self::initOptions();
            //register_activation_hook( dirname(__FILE__) . '/connections_benefits.php', array( __CLASS__, 'activate' ) );
            if (is_admin()) {
                add_action('plugins_loaded', array( $this, 'start' ));
                //add_filter('cn_submenu', array(  __CLASS__, 'addMenu' ));
				
				
				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-entry_benefit', array( __CLASS__, 'sanitize') );
				add_filter( 'cncsv_map_import_fields', array( __CLASS__, 'map_import_fields' ));
				add_filter( 'cncsv_import_fields', array($this, 'import_fields_benefit' ));
				
				
            }
			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );
			
			// Business Hours uses a custom field type, so let's add the action to add it.
			add_action( 'cn_meta_field-entry_benefit', array( __CLASS__, 'field' ), 10, 2 );
			
			add_action( 'cn_meta_output_field-cnbenefits', array( __CLASS__, 'block' ), 10, 3 );
        }
        public function start() {
            if (class_exists('connectionsLoad')) {
                //load_plugin_textdomain( 'connections_benefits' , false , CNFM_DIR_NAME . '/lang' );//comeback to 
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
                add_action('init', array( $this, 'init' ));
            } else {
                add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order to use Form.</p></div>\';'));
            }
        }
        public function init() {
        }
		
		public static function map_import_fields( $fields ){
			$fields['cnbenefits_description'] = 'Benefits | Description';
			$fields['cnbenefits_categories'] = 'Benefits | Categories';
			$fields['cnbenefits_wsuaa_discounts'] = 'Benefits | WSUAA discounts';
			$fields['cnbenefits_online'] = 'Benefits | online discount';
			return $fields;
		}
		public  function import_fields_benefit( $row ){
			$entryId =  $row->entryID;
			$tmp=array(
				'description'=>'',
				'wsuaa_discounts'=>1,
				'categories'=>'',
				'online'=>0
			);	
			if( isset($row->cnbenefits_description) ){
				$tmp['description'] = $row->cnbenefits_description;
			}
			if( isset($row->cnbenefits_categories) ){
				$tmp['categories'] = $row->cnbenefits_categories;
			}
			if( isset($row->cnbenefits_wsuaa_discounts) ){
				$tmp['wsuaa_discounts'] = $row->cnbenefits_wsuaa_discounts;
			}
			if( isset($row->cnbenefits_online) ){
				$tmp['online'] = $row->cnbenefits_online;
			}
			cnEntry_Action::meta('add', $entryId, array(
				array(
					'key' => "cnbenefits",
					'value' =>$tmp
				)
			));
			return $row;
		}		

		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_benefits';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_lang_dir', CNBH_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_wp_lang_dir',
				WP_LANG_DIR . '/connections-benefits/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}

		
		
		
		
		public static function registerMetabox( $metabox ) {
			$atts = array(
				'id'       => 'benefited',
				'title'    => __( 'Membership benefit', 'said_benefit' ),
				'context'  => 'normal',
				'priority' => 'core',
				'fields'   => array(
						array(
								'id'    => 'cnbenefits',
								'type'  => 'entry_benefit',
								),
						),
				);
			$metabox::add( $atts );
		}
		public static function field( $field, $value ) {
			//this should be a merge.. no?
			if(empty($value)){
				$value=array(
					'description'=>'',
					'wsuaa_discounts'=>1,
					'categories'=>'',
					'online'=>0
				);	
			}
			//var_dump($value);
			$out ="";

			$out .='
			<div><label>Benefit description:
			<br/><textarea name="cnbenefits[\'description\']" rows="5" cols="30">'.$value['description'].'</textarea>
			</label>
			<br/><br/>
			
			<label>Is this offer for only WSUAA Members?:<br/>
            
			<input name="cnbenefits[\'wsuaa_discounts\']" id="discounts_0_wsuaa_discounts"  type="radio" value="1" '.($value['description']>0?"checked":"").'> Yes 
            <input name="cnbenefits[\'wsuaa_discounts\']" id="discounts_0_wsuaa_discounts" type="radio" value="0" '.($value['description']>0?"":"checked").'> No 
			</label>
			<br/><br/>
            <label>Discount Category:
            <br/>
			<select name="cnbenefits[\'categories\']" id="discounts_0_categories_id">
				<option value="" '.($value['categories']==""?"selected":"").'></option>
				<option value="1" '.($value['categories']=="1"?"selected":"").'>Automotive</option>
				<option value="2" '.($value['categories']=="2"?"selected":"").'>Dining</option>
				<option value="3" '.($value['categories']=="3"?"selected":"").'>Entertainment</option>
				<option value="4" '.($value['categories']=="4"?"selected":"").'>Financial</option>
				<option value="5" '.($value['categories']=="5"?"selected":"").'>Health</option>
				<option value="6" '.($value['categories']=="6"?"selected":"").'>Insurance</option>
				<option value="7" '.($value['categories']=="7"?"selected":"").'>Lodging</option>
				<option value="8" '.($value['categories']=="8"?"selected":"").'>Services</option>
				<option value="9" '.($value['categories']=="9"?"selected":"").'>Shopping</option>
				<option value="10" '.($value['categories']=="10"?"selected":"").'>Travel</option>
			</select></label> 	<br/><br/>
			<label>Is this an online offer?:
            <br/>
            <input name="cnbenefits[\'online\']" id="discounts_0_online" type="radio" value="1" '.($value['online']>0?"checked":"").'> Yes 
            <input name="cnbenefits[\'online\']" id="discounts_0_online" type="radio" value="0" '.($value['online']>0?"":"checked").'> No 
			</label>
			<br/>
            <!--<em><strong>Note:</strong> to check if the online use is WSUAA Member have your web developer use this url http://cbn.wsu.edu/Business/is_member.castle with a url query of "Wsuid".  <br>The example is [ <strong>http://cbn.wsu.edu/Business/is_member.castle?Wsuid=47614823</strong> ]</em>-->
			';

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
				'description'=>isset($value['\'description\''])?$value['\'description\'']:"",
				'wsuaa_discounts'=>isset($value['\'wsuaa_discounts\''])?$value['\'wsuaa_discounts\'']:"",
				'categories'=>isset($value['\'categories\''])?$value['\'categories\'']:"",
				'online'=>isset($value['\'online\''])?$value['\'online\'']:0
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
    function Connections_benefits() {
        if (class_exists('connectionsLoad')) {
            return new Connections_benefits();
        } else {
            add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections benefits.</p></div>\';'));
            return FALSE;
        }
    }
    /**
     * Since Connections loads at default priority 10, and this extension is dependent on Connections,
     * we'll load with priority 11 so we know Connections will be loaded and ready first.
     */
    add_action('plugins_loaded', 'Connections_benefits', 11);
}

