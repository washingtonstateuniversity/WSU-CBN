<?php
/**
 * Class WNPA_Feed_Item
 *
 * Manage the feed item content type used by the WNPA Syndication plugin.
 */
if (!class_exists('Connections_Emails')) {
    class Connections_Emails {
        public static $options;
        public function __construct() {
            //self::loadConstants();
            //self::loadDependencies();
            //self::initOptions();
            //register_activation_hook( dirname(__FILE__) . '/connections_emails.php', array( __CLASS__, 'activate' ) );
            if (is_admin()) {
                add_action('plugins_loaded', array( $this, 'start' ));
                add_filter('cn_submenu', array(  __CLASS__, 'addMenu' ));
				
				
				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-last_emailed', array( __CLASS__, 'sanitize') );

            }
				$this->settings = cnSettingsAPI::getInstance();
				add_filter( 'cn_register_settings_tabs' , array(  $this,  'registerSettingsTabs' ) );
				add_filter( 'cn_register_settings_sections' , array( $this, 'registerSettingsSections' ) );
				add_filter( 'cn_register_settings_fields' , array( $this, 'registerSettingsFields' ) );
				
				$this->settings->init();
			
			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );
			
			// Business Hours uses a custom field type, so let's add the action to add it.
			add_action( 'cn_meta_field-last_emailed', array( __CLASS__, 'field' ), 10, 2 );
			add_action( 'cn_meta_field-email_count', array( __CLASS__, 'field' ), 10, 2 );
			
			add_action( 'cn_meta_output_field-cnemail', array( __CLASS__, 'block' ), 10, 3 );
        }
        public function start() {
            if (class_exists('connectionsLoad')) {
                //load_plugin_textdomain( 'connections_emails' , false , CNFM_DIR_NAME . '/lang' );//comeback to 

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
		
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_emails';

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
            $menu[71] = array(
                'hook' => 'emails',
                'page_title' => 'Connections : Emails',
                'menu_title' => 'Emails',
                'capability' => 'connections_add_entry',
                'menu_slug' => 'connections_emails',
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
                case 'connections_emails':
                    include_once(dirname(__FILE__) . '/admin/pages/emails.php');
                    connectionsEmailsPage();
                    break;
            }
        }

		public static function registerSettingsTabs( $tabs ) {
			global $connections;
		
			$settings = 'connections_page_connections_settings';
		
			// Register the core tab banks.
			$tabs[] = array(
				'id'        => 'email' ,
				'position'  => 30 ,
				'title'     => __( 'Emails' , 'connections' ) ,
				'page_hook' => $settings
			);
		
			return $tabs;
		}
		public function registerSettingsSections( $sections ) {
			global $connections;

			$settings = 'connections_page_connections_settings';

			// Register the core setting sections.
			$sections[] = array(
				'tab'       => 'email' ,
				'id'        => 'connections_email_defaults' ,
				'position'  => 20 ,
				'title'     => __( 'Email defaults' , 'connections_emails' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			return $sections;
		}

		
		public function registerSettingsFields( $fields ) {
			$current_user = wp_get_current_user();

			$settings = 'connections_page_connections_settings';

			$fields[] = array(
				'plugin_id' => 'connections_emails',
				'id'        => 'from_email',
				'position'  => 101,
				'page_hook' => $settings,
				'tab'       => 'email',
				'section'   => 'connections_email_defaults',
				'title'     => __('Sender Email', 'connections_emails'),
				'desc'      => __('', 'connections_emails'),
				'help'      => __('', 'connections_emails'),
				'type'      => 'text',
				'default'   => get_bloginfo( 'admin_email' )
			);
			
			$fields[] = array(
				'plugin_id' => 'connections_emails',
				'id'        => 'from_name_email',
				'position'  => 102,
				'page_hook' => $settings,
				'tab'       => 'email',
				'section'   => 'connections_email_defaults',
				'title'     => __('Sender Name', 'connections_emails'),
				'desc'      => __('', 'connections_emails'),
				'help'      => __('', 'connections_emails'),
				'type'      => 'text',
				'default'   => get_bloginfo( 'description' )
			);
			
			$fields[] = array(
				'plugin_id' => 'connections_emails',
				'id'        => 'to_name_format_email',
				'position'  => 103,
				'page_hook' => $settings,
				'tab'       => 'email',
				'section'   => 'connections_email_defaults',
				'title'     => __('Recipient Name Format', 'connections_emails'),
				'desc'      => __('', 'connections_emails'),
				'help'      => __('Look to the connections web site for more info on formats for names', 'connections_emails'),
				'type'      => 'text',
				'default'   => '%last%, %first%'
			);
			

			$fields[] = array(
				'plugin_id' => 'connections_emails',
				'id'        => 'default_subject_email',
				'position'  => 104,
				'page_hook' => $settings,
				'tab'       => 'email',
				'section'   => 'connections_email_defaults',
				'title'     => __('Default email subject', 'connections_emails'),
				'desc'      => __('', 'connections_emails'),
				'help'      => __('', 'connections_emails'),
				'type'      => 'text',
				'default'   => ' '
			);						
			$fields[] = array(
				'plugin_id' => 'connections_emails',
				'id'        => 'default_html_email',
				'position'  => 105,
				'page_hook' => $settings,
				'tab'       => 'email',
				'section'   => 'connections_email_defaults',
				'title'     => __('Default HTML email', 'connections_emails'),
				'desc'      => __('', 'connections_emails'),
				'help'      => __('', 'connections_emails'),
				'type'      => 'rte',
				'default'   => ' '
			);
			return $fields;
		}

		public static function registerMetabox( $metabox ) {
			$atts = array(
				'id'       => 'last-emailed',
				'title'    => __( 'Last Email Sent', 'sent_datetime' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
						array(
								'id'    => 'cnemail',
								'type'  => 'last_emailed',
								),
						),
				);
			$metabox::add( $atts );
		}
		public static function field( $field, $value ) {
			
			if(empty($value)){
				$value=array(
					'last'=>'',
					'count'=>0
				);	
			}
			
			
			printf( '<label>%s</label><p><strong>%s</strong></p><input type="hidden" value="%s" name="cnemail[\'last\']" /><input type="hidden" value="%s" name="cnemail[\'count\']" />', __( 'Last Sent', 'connections_emails' ), (!empty($value['last']))?date("m/d/Y h:i:s a",$value['last']): "Never sent", $value['last'],$value['count']);
 
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
			$return=array();
			$return['last'] = isset($value['\'last\''])?cnSanitize::string( 'text', $value['\'last\''] ):"";
			$return['count'] =isset($value['\'count\''])? $value['\'count\'']:0;

			return $return;
		}
		
		
		
		
		
    }
	
	
	
	
	
	
	
	
	
	
	
    /**
     * Start up the extension.
     *
     * @access public
     * @since 1.0
     * @return mixed (object)|(bool)
     */
    function Connections_Emails() {
        if (class_exists('connectionsLoad')) {
            return new Connections_Emails();
        } else {
            add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Emails.</p></div>\';'));
            return FALSE;
        }
    }
    /**
     * Since Connections loads at default priority 10, and this extension is dependent on Connections,
     * we'll load with priority 11 so we know Connections will be loaded and ready first.
     */
    add_action('plugins_loaded', 'Connections_Emails', 11);
}

