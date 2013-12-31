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
				
				// Register the metabox and fields.
				add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

				// Business Hours uses a custom field type, so let's add the action to add it.
				add_action( 'cn_meta_field-last_emailed', array( __CLASS__, 'field' ), 10, 2 );
				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-last_emailed', array( __CLASS__, 'sanitize') );
				

				
				
            }
			add_action( 'cn_meta_output_field-cnemail', array( __CLASS__, 'block' ), 10, 3 );
        }
        public function start() {
            if (class_exists('connectionsLoad')) {
                //load_plugin_textdomain( 'connections_emails' , false , CNFM_DIR_NAME . '/lang' );//comeback to 
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
		public static function loadTextdomain() {//come back to 
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
			$atts = array(
				'id'       => 'level',
				'title'    => __( 'Level', 'level' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
						array(
								'id'    => 'cnemail',
								'type'  => 'level',
								),
						),
				);
			$metabox::add( $atts );
		}

		
		
		
        /**
         * Register the settings sections.
         *
         * @param array $sections
         * @return array
         */
        public function registerSettingsSections($sections) {
            global $connections;
            $settings   = 'connections_page_connections_settings';
            // Register the core setting sections.
            $sections[] = array(
                'tab' => 'emails',
                'id' => 'connections_emails_login',
                'position' => 10,
                'title' => __('Require Login', 'connections_emails'),
                'callback' => '',
                'page_hook' => $settings
            );
            $sections[] = array(
                'tab' => 'emails',
                'id' => 'connections_emails_email_notifications',
                'position' => 20,
                'title' => __('Email Notifications', 'connections_emails'),
                'callback' => '',
                'page_hook' => $settings
            );
            return $sections;
        }
        /**
         * Add the Form settings tab on the Connections : Settings admin page.
         */
        public function registerSettingsTab($tabs) {
            global $connections;
            $tabs[] = array(
                'id' => 'emails',
                'position' => 35,
                'title' => __('Emails', 'connections'),
                'page_hook' => 'connections_page_connections_settings'
            );
            return $tabs;
        }
        public function registerSettingsFields($fields) {
            $current_user = wp_get_current_user();
            $settings     = 'connections_page_connections_settings';
            $fields[]     = array(
                'plugin_id' => 'connections_emails',
                'id' => 'required',
                'position' => 10,
                'page_hook' => $settings,
                'tab' => 'emails',
                'section' => 'connections_emails_login',
                'title' => __('Login Required', 'connections_emails'),
                'desc' => __('Require registered users to login before showing the entry submission form.', 'connections_emails'),
                'help' => __('Check this option if you wish to only allow registered users to submit entries for your review and approval.', 'connections_emails'),
                'type' => 'checkbox',
                'default' => 0
            );
            return $fields;
        }
		
		
		
		
		
		
		public static function field( $field, $value ) {
			cnHTML::field(
				array(
					'type'     => 'text',
					'class'    => '',
					'id'       => $field['id'] ,
					'required' => false,
					'label'    => '',
					'before'   => '',
					'after'    => '',
					'return'   => false,
				)
			);
		}

		/**
		 * Sanitize the times as a text input using the cnSanitize class.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  array $value   The opening/closing hours.
		 *
		 * @return array
		 */
		public static function sanitize( $value ) {

			/*foreach ( $value as $key => $day ) {

				foreach ( $day as $period => $time ) {

					// Save all time values in 24hr format.
					$time['open']  = self::formatTime( $time['open'], 'H:i' );
					$time['close'] = self::formatTime( $time['close'], 'H:i' );

					$value[ $key ][ $period ]['open']  = cnSanitize::string( 'text', $time['open'] );
					$value[ $key ][ $period ]['close'] = cnSanitize::string( 'text', $time['close'] );

				}
			}*/

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

