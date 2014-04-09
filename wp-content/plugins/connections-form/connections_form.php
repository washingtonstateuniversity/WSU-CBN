<?php
/**
 * An extension for the Connections Business Directory plugin
 * which adds support for front-end editing and site visitor submissions.
 *
 * @package   Connections Form
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      http://connections-pro.com
 * @copyright 2014 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Form
 * Plugin URI:        http://connections-pro.com
 * Description:       An extension for the Connections Business Directory plugin which adds support for front-end editing and site visitor submissions.
 * Version:           2.0
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections_form
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Connections_Form' ) ) {

	final class Connections_Form {

		/**
		 * @var Stores the instance of this class.
		 *
		 * @access private
		 * @since 2.0
		 */
		private static $instance;

		private static $demo = FALSE;

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access public
		 * @since 2.0
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * The main Connection Form plugin instance.
		 *
		 * @access  private
		 * @since  2.0
		 * @static
		 * @uses add_action()
		 * @uses add_filter()
		 *
		 * @return object self
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Connections_Form ) ) {

				self::$instance = new Connections_Form;

				self::loadConstants();

				add_action( 'init', array( __CLASS__ , 'loadTextdomain' ) );

				// These filters must be added before the `cn_register_settings_fields` filter.
				add_filter( 'cn_list_action_options', array( __CLASS__, 'listActionsOption') );
				add_filter( 'cn_entry_action_options', array( __CLASS__, 'entryActionsOption') );

				/*
				 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
				 */
				add_filter( 'cn_register_settings_tabs', array( __CLASS__, 'registerSettingsTab' ) );
				add_filter( 'cn_register_settings_sections', array( __CLASS__, 'registerSettingsSections' ) );
				add_filter( 'cn_register_settings_fields', array( __CLASS__, 'registerSettingsFields' ) );

				add_action( 'cn_metabox', array( __CLASS__, 'registerMetaboxes' ), 5 );

				if ( self::$demo !== TRUE ) {

					add_action( 'wp_ajax_cnf-submission', array( __CLASS__, 'submission' ) );
					add_action( 'wp_ajax_nopriv_cnf-submission', array( __CLASS__, 'submission' ) );
				}

				// Add the toolbar and menu items.
				add_action( 'admin_bar_menu', array( __CLASS__, 'toolbar' ), 100 );

				// Register the shortcode.
				add_shortcode( 'connections_form', array( __CLASS__, 'shortcode') );

				// Add the actions to allow front-end editing and submitting an entry via the /submit/ endpoint.
				add_action( 'cn_submit_entry_form', array( __CLASS__, 'form' ), 10, 3 );
				add_action( 'cn_edit_entry_form', array( __CLASS__, 'form' ), 10, 3 );

				// Enqueue the CSS and JS files.
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
				add_action( 'wp_print_styles', array( __CLASS__, 'enqueueStyles' ) );

				// Render the submit entry and edit entry action links.
				add_action( 'cn_list_action-submit', array( __CLASS__, 'listAction') );
				add_action( 'cn_entry_action-edit', array( __CLASS__, 'entryAction'), 10, 2 );

				// License and Updater.
				if ( class_exists( 'CN_License' ) ) {

					new CN_License( __FILE__, 'Form', CNFM_CURRENT_VERSION, 'Steven A. Zahm' );
				}
			}

			return self::$instance;
		}

		/**
		 * Define the constants.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return void
		 */
		private static function loadConstants() {

			define( 'CNFM_CURRENT_VERSION', '2.0' );

			define( 'CNFM_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNFM_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNFM_BASE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNFM_BASE_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since 2.0
		 * @uses apply_filters()
		 * @uses get_locale()
		 * @uses load_textdomain()
		 * @uses load_plugin_textdomain()
		 *
		 * @return (void)
		 */
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_form';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_form_lang_dir', CNFM_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_form_wp_lang_dir',
				WP_LANG_DIR . '/connections-form/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-safe!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}

		/**
		 * Called when activating Connections Form via the activation hook.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return void
		 */
		public static function activate() {

		}

		/**
		 * Called when deactivating Connections Form via the deactivation hook.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return void
		 */
		public static function deactivate() {

		}

		/**
		 * Called when running the wp_enqueue_scripts action.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return void
		 */
		public static function enqueueScripts() {
			global $connections;
 
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';


			wp_enqueue_script( 'jquery-validate' );
			wp_enqueue_script( 'jquery-chosen-min' );
			
			if ( $connections->options->getGoogleMapsAPI()  ) {
 
				if ( ! is_ssl() ) wp_enqueue_script( 'cn-google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
 
				if ( is_ssl() ) wp_enqueue_script( 'cn-google-maps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );

				wp_enqueue_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' , 'cn-google-maps-api' ), '1.3.2', $connections->options->getJavaScriptFooter() );
 
				wp_enqueue_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'cn-google-maps-api' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
 
			} else {
 
				wp_enqueue_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' ), '1.3.2', $connections->options->getJavaScriptFooter() );
 
				wp_enqueue_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
 
			}
			
			
			wp_enqueue_script( 'cn-form-ui-user', CNFM_BASE_URL . 'js/cn-form-user.js', array( 'jquery', 'jquery-form' ), CNFM_CURRENT_VERSION, TRUE );

			$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

			$atts = array(
				'ajaxurl'             => admin_url( 'admin-ajax.php', $protocol ),
				'strSubmitting'       => __( 'Please wait, your submission is being processed.', 'connections_form' ),
				'strSubmitted'        => __( 'Submission received.', 'connections_form' ),
				'strSubmittedMsg'     => __( 'We will review and approve your submission soon.', 'connections_form' ),
				'strUpdatedMsg'       => __( 'Entry updated. The page will automatically refresh, please wait...', 'connections_form' ),
				'strErrMsg'           => __( 'An error adding entry has occurred.', 'connections_form' ),
				'strTokenErrMsg'      => __( 'Token mismatch. Cheating?', 'connections_form' ),
				'strNonceErrMsg'      => __( 'Nonce validation failed.', 'connections_form' ),
				'strAJAXErrMsg'       => __( 'Invalid AJAX action.', 'connections_form' ),
				'strAJAXSubmitErrMsg' => __( 'Unknown error has occurred!', 'connections_form' ),
				'demo'                => self::$demo ? 1 : 0,
				);

			wp_localize_script( 'cn-form-ui-user', 'cn_form', $atts );
			
		}

		/**
		 * Called when running the wp_print_styles action.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return void
		 */
		public static function enqueueStyles() {

			wp_enqueue_style( 'cn-form-user', CNFM_BASE_URL . 'css/cn-form-user.css', array( 'connections-chosen' ), CNFM_CURRENT_VERSION );
		}

		/**
		 * Add the link to the settings tab to the to the Connections Toolbar extension.
		 *
		 * @access private
		 * @since 2.0
		 * @param  object $admin_bar
		 *
		 * @return void
		 */
		public static function toolbar( $admin_bar ) {

			$admin_bar->add_node( array(
				'id'     => 'cn-toolbar-settings-form',
				'parent' => 'cn-toolbar-settings',
				'title'  => __( 'Form', 'connections-toolbar' ),
				'href'   => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'form' ) , admin_url() . 'admin.php' ),
				'meta'   => array(
					'title' => _x( 'Form Settings', 'This is a tooltip shown on mouse hover.', 'connections_form' ),
				),
			));
		}

		/**
		 * Add the Form settings tab on the Connections : Settings admin page.
		 *
		 * @access  private
		 * @since  unknown
		 *
		 * @return array	The settings tabs options array.
 		 */
		public static function registerSettingsTab( $tabs ) {

			$tabs[] = array(
				'id'        => 'form' ,
				'position'  => 35,
				'title'     => __( 'Form' , 'connections' ) ,
				'page_hook' => 'connections_page_connections_settings'
			);

			return $tabs;
		}

		/**
		 * Register the settings sections.
		 *
		 * @access  private
		 * @since  0.4
		 * @param  array $sections
		 *
		 * @return array	The settings sections options array.
		 */
		public static function registerSettingsSections( $sections ) {

			$settings = 'connections_page_connections_settings';

			// Register the core setting sections.
			$sections[] = array(
				'plugin_id' => 'connections_form',
				'tab'       => 'form',
				'id'        => 'login',
				'position'  => 10 ,
				'title'     => __( 'Require Login', 'connections_form' ),
				'callback'  => '',
				'page_hook' => $settings );
			$sections[] = array(
				'plugin_id' => 'connections_form',
				'tab'       => 'form',
				'id'        => 'meta',
				'position'  => 20 ,
				'title'     => __( 'Content Blocks', 'connections_form' ),
				'callback'  => '',
				'page_hook' => $settings );
			$sections[] = array(
				'plugin_id' => 'connections_form',
				'tab'       => 'form',
				'id'        => 'email_notifications',
				'position'  => 30,
				'title'     => __( 'Email Notifications', 'connections_form' ),
				'callback'  => '',
				'page_hook' => $settings );

			return $sections;
		}

		/**
		 * Register the settings fields.
		 *
		 * @access  private
		 * @since  unknown
		 * @uses  wp_get_current_user()
		 * @param  array $fields
		 *
		 * @return array	The settings fields options array.
		 */
		public static function registerSettingsFields( $fields ) {

			$current_user = wp_get_current_user();

			$settings = 'connections_page_connections_settings';

			/*
			 * The require login settings fields.
			 */
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'required',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'login',
				'title'     => __( 'Login Required', 'connections_form' ),
				'desc'      => __( 'Require registered users to login before showing the entry submission form.', 'connections_form' ),
				'help'      => __( 'Check this option if you wish to only allow registered users to submit entries for your review and approval.', 'connections_form' ),
				'type'      => 'checkbox',
				'default'   => 0
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'message',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'login',
				'title'     => __( 'Message', 'connections_form' ),
				'desc'      => '',
				'help'      => '',
				'type'      => 'rte',
				'default'   => __( 'Please login in order to submit your entry to our directory.', 'connections_form' )
			);

			/*
			 * The metabox settings field.
			 */
			$metaboxes = cnMetaboxAPI::get();
			$options   = array();
			$defaults  = array();
			$required  = array();

			foreach ( $metaboxes as $id => $metabox ) {

				// Skip any metaboxes registered to the Connections : Dashboard admin page.
				if ( array_search( 'toplevel_page_connections_dashboard', (array) $metabox['pages'] ) !== FALSE ) continue;

				// Do no show the following core metaboxes as options because they are excluded by Form.
				if ( in_array( $metabox['id'], array( /*'name',*/ 'submitdiv', 'metabox-meta' ) ) ) continue;

				// Create the $options array from the registered metaboxes.
				$options[ $id ] = $metabox['title'];

				// Set the intial default settings.
				$defaults['order'][]  = $id;
				$defaults['active'][] = $id;
			}

			// The "Name" metabox is absolutely required, add it to the options array
			// and add it to the field options required array. The "Name" fields also
			// needs to be added to $default
			$options    =  array( 'name' => __( 'Name', 'connections' ) ) + $options;
			$required[] = 'name';
			array_unshift( $defaults['order'], 'name' );
			array_unshift( $defaults['active'], 'name' );

			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'select_type',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'meta',
				'title'     => __( 'Entry Type', 'connections_form' ),
				'desc'      => __( 'Permit the user to select the entry type to be submitted.', 'connections_form' ),
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 1,
			);

			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'default_type',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'meta',
				'title'     => __( 'Default Entry Type', 'connections_form' ),
				'desc'      => __( 'If the user is permitted to choose the entry type, this option sets the entry type that is selected by default.', 'connections_form' ),
				'help'      => '',
				'type'      => 'radio',
				'options'   => array(
					'individual'   => 'Individual',
					'organization' => 'Organization',
					),
				'default'   => 'individual',
			);

			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'data',
				'position'  => 30,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'meta',
				'title'     => '',
				'desc'      => __( 'Whether or not a content block should be shown. Content blocks can be dragged and dropped in the desired order to be shown. NOTE: The shortcode options will override these settings.', 'connections_form' ),
				'help'      => '',
				'type'      => 'sortable_checklist',
				'options'   => array( 'items' => $options, 'required' => $required ),
				'default'   => $defaults,
			);

			/*
			 * Email auto reply email notification settings fields.
			 */
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'auto_reply',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Auto Reply', 'connections_form'),
				'desc'      => __('Send an email notice of receipt.', 'connections_form'),
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 1
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'auto_reply_from_name',
				'position'  => 15,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Auto Reply From Name', 'connections_form'),
				'desc'      => __('The name in which the email is said to come from.', 'connections_form'),
				'help'      => '',
				'type'      => 'text',
				'default'   => 'Directory Administrator'
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'auto_reply_from_email',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Auto Reply From Email', 'connections_form'),
				'desc'      => __('The address in which email is said to come from. This will set the "from" and "reply to" address.', 'connections_form'),
				'help'      => '',
				'type'      => 'text',
				'default'   => $current_user->user_email
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'auto_reply_subject',
				'position'  => 25,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Auto Reply Subject', 'connections_form'),
				'desc'      => __('The auto reply email subject.', 'connections_form'),
				'help'      => '',
				'type'      => 'text',
				'size'      => 'large',
				'default'   => __('Your directory entry submission has been received.', 'connections_form')
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'auto_reply_message',
				'position'  => 30,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Auto Reply Message', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'textarea',
				'default'   => __('Thank you for your submission. We will review and approve your submission soon.', 'connections_form')
			);

			/*
			 * Email submission email notification settings fields.
			 */
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'submission_notice',
				'position'  => 35,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Submission Notice', 'connections_form'),
				'desc'      => __('Send an email notice of entry submission.', 'connections_form'),
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 1
			);

			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'submission_notice_to',
				'position'  => 40,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Submission Notice To', 'connections_form'),
				'desc'      => __('The email address in which to send the entry submission notice to.', 'connections_form'),
				'help'      => '',
				'type'      => 'text',
				'default'   => $current_user->user_email
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'submission_notice_subject',
				'position'  => 45,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Submission Notice Subject', 'connections_form'),
				'desc'      => __('The submission notice subject.', 'connections_form'),
				'help'      => '',
				'type'      => 'text',
				'size'      => 'large',
				'default'   => __('You have received a new directory entry submission.', 'connections_form')
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'submission_notice_message',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_email_notifications',
				'title'     => __('Submission Notice Message', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'textarea',
				'default'   => __('You have recieved a new directory submission. Please visit your moderation queue to review and approve it.', 'connections_form')
			);

			return $fields;
		}

		/**
		 * Remove and register replacement custom metabox.
		 *
		 * @access  private
		 * @since  2.0
		 * @param  object $metabox Instance of cnMetaboxAPI.
		 *
		 * @return void
		 */
		public static function registerMetaboxes( $metabox ) {

			// This should not run while in the admin; it'll remove the category widget
			// that belongs on the add/edit entry admin page.
			if ( is_admin() ) return;

			// Remove the core metaboxes because we're using custom metaboxes for the front end.
			$metabox::remove( 'metabox-name' ); // This does not actually need to be removed, but lets do it to be safe.
			$metabox::remove( 'categorydiv' );

			$metabox::add( array(
				'id'       => 'categorydiv',
				'title'    => __( 'Category', 'connections_form' ),
				'context'  => 'normal',
				'priority' => 'core',
				'callback' => array( __CLASS__, 'categoryMetabox' ),
				'pages'    => array( 'public' ),
				)
			);

			$metabox::add( array(
				'id'       => 'visibility',
				'title'    => __( 'Visibility', 'connections_form' ),
				'context'  => 'normal',
				'priority' => 'core',
				'callback' => array( __CLASS__, 'visibilityMetabox' ),
				'pages'    => array( 'public' ),
				)
			);
		}

		/**
		 * The AJAX action which process new entry submissions as well as front-end entry edit submissions.
		 *
		 * @access  private
		 * @since  2.0
		 * @uses  check_ajax_referer()
		 * @uses  is_user_logged_in()
		 * @uses  current_user_can()
		 * @uses  get_bloginfo()
		 *
		 * @return int The error/success code.
		 */
		public static function submission() {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$update = FALSE;
			$result = FALSE;

			if ( isset( $_POST['entry_id'] ) && ! empty( $_POST['entry_id'] ) ) {

				// $_POST data for `entry_id`.
				$entryID = $_POST['entry_id'];

				// Retrieve the raw entry data array.
				$data = $instance->retrieve->entry( $entryID );

				// Create a token from the raw entry data array.
				$token = md5( json_encode( $data ) );

				// Compare the token submitted via POST to $token. If they don't match; something is wrong. Bail with error code.
				if ( $token != $_POST['cnf-token'] ) self::response( -3 );

				// Lastly validate the ajax nonce.
				check_ajax_referer( 'cnf-add_edit_entry-' . $token, 'ajaxnonce' );

				$update = TRUE;

			} else {

				check_ajax_referer( 'cnf-add_new_entry', 'ajaxnonce' );
			}

			$result = $update ? cnEntry_Action::update( $entryID, $_POST ) : cnEntry_Action::add( $_POST );

			/*
			 * If the entry was added successfully, send the email notifications accordingly.
			 */
			if ( $result !== FALSE && ! $update ) {

				$entry = new cnEntry();
				$entry->set( $result );

				// Get the preferred email address if one was set by the submitter.
				$emailAddresses = $entry->getEmailAddresses( array( 'preferred' => TRUE), TRUE, TRUE );

				if ( ! empty( $emailAddresses ) ) {

					$sendTo = isset( $emailAddresses[0]->address ) ? $emailAddresses[0]->address : '';
				}

				// If no preferred email address was set, grab all email address and then set the first email address to send the auto-reply to.
				if ( empty( $sendTo ) ) {

					$emailAddresses = $entry->getEmailAddresses( array(), TRUE, TRUE );

					if ( ! empty( $emailAddresses ) ) {

						if ( isset( $emailAddresses[0]->address ) ) $sendTo = $emailAddresses[0]->address;

					}
				}

				// If an email address was supplied preferred or otherwise, send the auto-reply as applicable.
				if ( ! empty( $sendTo ) ) {

					if ( cnSettingsAPI::get( 'connections_form', 'email_notifications', 'auto_reply' ) ) {

						$email = new cnEmail();

						// Set email to be sent as HTML.
						$email->HTML();

						// Set from whom.
						$email->from(
							cnSettingsAPI::get( 'connections_form', 'email_notifications', 'auto_reply_from_email' ),
							cnSettingsAPI::get( 'connections_form', 'email_notifications', 'auto_reply_from_name' )
							);

						// Set to whom.
						$email->to(
							$sendTo,
							$entry->getName()
							);

						// Set the subject.
						$email->subject( cnSettingsAPI::get( 'connections_form', 'email_notifications', 'auto_reply_subject' ) );

						// Do any `cn_entry` shortcodes that might be present in the message body.
						$content = cnSettingsAPI::get( 'connections_form', 'email_notifications', 'auto_reply_message' );
						$message = cnEntry_Shortcode::process( $entry, $content );

						// Set the message.
						$email->message( $message );

						// Set the email template to be used.
						cnEmail_Template::template( 'default' );

						$email->send();
					}
				}

				/*
				 * If the current user is logged in, check to see they have the capability to add an
				 * entry moderated or not, send the email submission notice and response code accordingly.
				 */
				if ( is_user_logged_in() ) {

					if ( current_user_can('connections_add_entry') ) {

						/*
						 * If the current user can add an entry without moderation, no need to send
						 * the email submission notice. End, report success.
						 *
						 * 1 == Success, unmoderated.
						 */
						self::response( 1 );

					} else {

						if ( cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice' ) ) {

							$email = new cnEmail();

							// Set email to be sent as HTML.
							$email->HTML();

							// Set from whom.
							$email->from(
								cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_to' ),
								get_bloginfo('name')
								);

							// Set to whom.
							$email->to(
								cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_to' ),
								get_bloginfo('name')
								);

							// Set the subject.
							$email->subject( cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_subject' ) );

							// Do any `cn_entry` shortcodes that might be present in the message body.
							$content = cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_message' );
							$message = cnEntry_Shortcode::process( $entry, $content );

							// Set the message.
							$email->message( $message );

							// Set the email template to be used.
							cnEmail_Template::template( 'default' );

							$email->send();
						}

						/*
						 * End, report success.
						 *
						 * 2 == Success, moderated.
						 */
						self::response( 2 );

					}

				} else {

					if ( cnSettingsAPI::get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice' ) ) {

						$email = new cnEmail();

						// Set email to be sent as HTML.
						$email->HTML();

						// Set from whom.
						$email->from(
							cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_to' ),
							get_bloginfo('name')
							);

						// Set to whom.
						$email->to(
							cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_to' ),
							get_bloginfo('name')
							);

						// Set the subject.
						$email->subject( cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_subject' ) );

						// Do any `cn_entry` shortcodes that might be present in the message body.
						$content = cnSettingsAPI::get( 'connections_form', 'email_notifications', 'submission_notice_message' );
						$message = cnEntry_Shortcode::process( $entry, $content );

						// Set the message.
						$email->message( $message );

						// Set the email template to be used.
						cnEmail_Template::template( 'default' );

						$email->send();
					}

					/*
					 * End, report success.
					 *
					 * 2 == Success, moderated.
					 */
					self::response( 2 );

				}

			} elseif ( $result !== FALSE && $update ) {

				/*
				 * End, report success.
				 *
				 * 3 == Success, entry updated.
				 */
				self::response( 3 );

			} else {

				/*
				 * End, report fail.
				 *
				 * -2 == Failed.
				 */
				self::response( -2 );

			}

		}

		/**
		 * The file upload makes use of an iframe for browsers like IE and Opera,
		 * which do not yet support XMLHTTPRequest file uploads.
		 *
		 * Iframe based uploads require a Content-type of text/plain or text/html for the JSON response -
		 * they will show an undesired download dialog if the iframe response is set to application/json.
		 * So we'll set the `Content-Type` header to `text/plain` that way `jQuery.parseJSON()` can be used
		 * to parse the response code.
		 *
		 * Use of the Accept header to offer different content types for the file upload response.
		 *
		 * @access public
		 * @since  2.0
		 * @uses   wp_send_json()
		 * @uses   get_option()
		 * @uses   wp_die()
		 * @param  int    $response The resonse code.
		 * @return string           The JSON encoded responce code.
		 */
		public static function response( $response ) {

			if ( isset($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'application/json') !== FALSE ) {

				wp_send_json( $message );

			} else {

				@header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );

				echo json_encode( $response );

				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

					wp_die();

				} else {

					die;
				}

			}

		}

		/**
		 * Returns the entry data if `cn-entry-slug` is set.
		 *
		 * @access public
		 * @since 2.0
		 *
		 * @return mixed array | bool The entry data array if `cn-entry-slug` is set or (bool) FALSE.
		 */
		public static function setEntry() {

			if ( get_query_var( 'cn-entry-slug' ) ) {

				// Grab an instance of the Connections object.
				$instance = Connections_Directory();

				return $instance->retrieve->entry( get_query_var( 'cn-entry-slug' ) );

			} else {

				return FALSE;
			}
		}

		/**
		 * Add the settings option to the List Actions options.
		 *
		 * @access private
		 * @since  2.0
		 * @param  array  $actions The actions attributes array.
		 * @return array
		 */
		public static function listActionsOption( $actions ) {

			$actions['submit'] = __( 'When this option is enabled a "Submit Entry" link will be displayed. When a user clicks this link they will be taken to a page where they can submit their entry to the directory.', 'connections' );

			return $actions;
		}

		public static function entryActionsOption( $actions ) {

			$actions['edit'] = __( 'When this option is enabled a "Edit Entry" link will be displayed. When a user clicks this link they will be taken to a page where they can edit entry.', 'connections' );

			return $actions;
		}

		/**
		 * The callback that is run to output the "Submit Entry" link.
		 * The action being run is cn_list_action-{$slug} called from
		 * cnTemplatePart::listActions(). $slug is the array key set
		 * in the $actions array in self::listActionsOption().
		 *
		 * @access private
		 * @since  2.0
		 * @param  array  $atts The $atts array from cnTemplatePart::listActions().
		 *
		 * @return void
		 */
		public static function listAction( $atts ) {

			if ( is_user_logged_in() || cnSettingsAPI::get( 'connections_form', 'login', 'required' ) !== "1" ) {

				cnURL::permalink( array( 'type' => 'submit', 'text' => __( 'Submit Entry', 'connections_form' ), 'return' => FALSE ) );
			}
		}

		/**
		 * The callback that is run to output the "Edit Entry" link.
		 * The action being run is cn_entry_action-{$slug} called from
		 * cnTemplatePart::entryActions(). $slug is the array key set
		 * in the $actions array in self::entryActionsOption().
		 *
		 * The "Edit Entry" link should only be displayed if the user is logged in
		 * and they have the `connections_manage` capability and either the
		 * `connections_edit_entry` or `connections_edit_entry_moderated` capability.
		 *
		 * @access private
		 * @since  2.0
		 * @param  array  $atts		The $atts array from cnTemplatePart::entryActions().
		 * @param  object $entry 	An instance of the cnEntry object.
		 *
		 * @return void
		 */
		public static function entryAction( $atts, $entry ) {

			if ( is_user_logged_in() &&
				current_user_can( 'connections_manage' ) &&
				( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
				) {

				cnURL::permalink( array( 'type' => 'edit', 'slug' => $entry->getSlug(), 'text' => __( 'Edit Entry', 'connections_form' ), 'return' => FALSE ) );
			}
		}

		/**
		 * Echos the output returned by self::shortcode().
		 *
		 * @access public
		 * @since  2.0
		 * @param  array  $atts    The shortcode attributes.
		 * @param  string $content
		 *
		 * @return string          HTML from self::shortcode().
		 */
		public static function form( $atts, $content ) {

			echo self::shortcode( $atts, $content );
		}

		/**
		 * Renders the form.
		 *
		 * @access public
		 * @since unknown
		 * @uses is_user_logged_in()
		 * @uses wptexturize()
		 * @uses wpautop()
		 * @uses make_clickable()
		 * @uses do_shortcode()
		 * @uses shortcode_atts()
		 * @uses apply_filters()
		 * @uses get_query_var()
		 * @uses wp_create_nonce()
		 * @param  array  $atts    The shortcode atts array.
		 * @param  string $content The shortcode content.
		 * @param  string $tag     The shortcode tag
		 *
		 * @return string          HTML form.
		 */
		public static function shortcode( $atts, $content = '', $tag = 'connections_form' ) {

			if ( ! is_user_logged_in() && cnSettingsAPI::get( 'connections_form', 'login', 'required' ) ) {

				$message = cnSettingsAPI::get( 'connections_form', 'login', 'message' );

				// Format and texturize the message.
				$message = wptexturize( wpautop( $message ) );

				// Make any links and such clickable.
				$message = make_clickable( $message );

				// Apply the shortcodes.
				$message = do_shortcode( $message );

				return $message;
			}

			$convert  = new cnFormatting();
			$settings = cnSettingsAPI::get( 'connections_form', 'meta', 'data' );
			// Exclude the following core metaboxes:
			$exclude  = array(
				'visibility',
				'submitdiv',
				'metabox-meta',
				);
			$data     = self::setEntry();
			$entry    = $data ? new cnEntry( $data ) : new cnEntry();
			$out      = '';

			$atts = shortcode_atts(
				array(
					'default_type' => cnSettingsAPI::get( 'connections_form', 'meta', 'default_type' ),
					'select_type'  => cnSettingsAPI::get( 'connections_form', 'meta', 'select_type' ),
					'visibility'   => FALSE,
					'photo'        => TRUE,
					'logo'         => TRUE,
					'address'      => TRUE,
					'phone'        => TRUE,
					'email'        => TRUE,
					'messenger'    => TRUE,
					'social'       => TRUE,
					'link'         => TRUE,
					'dates'        => TRUE,
					'category'     => TRUE,
					'bio'          => TRUE,
					'notes'        => TRUE,
				), $atts );

			$atts = apply_filters( 'cnf_shortcode_atts', $atts );

			/*
			 * Convert some of the $atts values in the array to boolean.
			 */
			$convert->toBoolean( $atts['select_type'] );
			$convert->toBoolean( $atts['visibility'] );
			$convert->toBoolean( $atts['photo'] );
			$convert->toBoolean( $atts['logo'] );
			$convert->toBoolean( $atts['address'] );
			$convert->toBoolean( $atts['phone'] );
			$convert->toBoolean( $atts['email'] );
			$convert->toBoolean( $atts['messenger'] );
			$convert->toBoolean( $atts['social'] );
			$convert->toBoolean( $atts['link'] );
			$convert->toBoolean( $atts['dates'] );
			$convert->toBoolean( $atts['category'] );
			$convert->toBoolean( $atts['rte'] );
			$convert->toBoolean( $atts['bio'] );
			$convert->toBoolean( $atts['notes'] );
			// $out .= var_dump($atts);

			// Exclude metaboxes based on shortcode atts. This is primarily for legacy support
			// because in versions prior to 2.0 the only option was to use shortcode options to
			// disable the output of a metabox.
			if ( ! $atts['photo'] ) $exclude[]     = 'metabox-image';
			if ( ! $atts['logo'] ) $exclude[]      = 'metabox-logo';
			if ( ! $atts['address'] ) $exclude[]   = 'metabox-address';
			if ( ! $atts['phone'] ) $exclude[]     = 'metabox-phone';
			if ( ! $atts['email'] ) $exclude[]     = 'metabox-email';
			if ( ! $atts['messenger'] ) $exclude[] = 'metabox-messenger';
			if ( ! $atts['social'] ) $exclude[]    = 'metabox-social-media';
			if ( ! $atts['link'] ) $exclude[]      = 'metabox-links';
			if ( ! $atts['dates'] ) $exclude[]     = 'metabox-dates';
			if ( ! $atts['bio'] ) $exclude[]       = 'metabox-bio';
			if ( ! $atts['notes'] ) $exclude[]     = 'metabox-note';
			if ( ! $atts['category'] ) $exclude[]  = 'categorydiv';

			// The name metabox needs to be added here so the shortcode $atts can be passed to the callback.
			cnMetaboxAPI::add(
				array(
					'id'       => 'name',
					'title'    => __( 'Name', 'connections_form' ),
					'context'  => 'normal',
					'priority' => 'high',
					'callback' => array( __CLASS__, 'nameMetabox' ),
					'pages'    => array( 'public' ),
					'atts'     => $atts,
				)
			);

			// Permitted entry types
			$permittedTypes = array( 'individual' , 'organization' );

			// Enforce permitted entry type
			if ( ! in_array( $atts['default_type'], $permittedTypes ) ) $atts['default_type'] = 'individual';

			$out .= '<div id="cn-form-container">' . PHP_EOL;

				$out .= '<div id="cn-form-ajax-response"><ul></ul></div>' . PHP_EOL;

					$out .= '<form id="cn-form" method="post" enctype="multipart/form-data">' . PHP_EOL;

					ob_start();

					// Render the Name metabox.
					// cnMetabox_Render::metaboxes( array( 'id' => 'name' ), $entry );

					// If an entry is being edited, the order should include all metaboxes and inactive metaboxes should be hidden instead.
					// This is because any metabox ID not included in `order` means that it is excluded.
					$order = ( get_query_var( 'cn-process' ) == 'edit' ) ? $settings['order'] : $settings['active'];

					// If an entry is being editted, the metaboxes need to be hidden (like in the admin)
					// rather than being excluded. Excluded metaboxes could cause data loss when editing an entry.
					$hide = ( get_query_var( 'cn-process' ) == 'edit' ) ? array_diff( $settings['order'], $settings['active'] ) : array();

					// Render the active metaboxes in the user defined order.
					cnMetabox_Render::metaboxes( array( 'exclude' => $exclude, 'order' => $order, 'hide' => $hide ), $entry );

					// The Visibility metabox will only be shown when editing an entry.
					// Because I "just know" a small subset of users will want this for anonymous submissions,
					// check to see if it was enabled via the shortcode.
					if ( get_query_var( 'cn-process' ) == 'edit' || $atts['visibility'] ) {

						cnMetabox_Render::metaboxes( array( 'id' => 'visibility' ), $entry );
					}

					$out .= ob_get_clean();

					// Hidden Field -- 'action' required to trigger the registered ajax action.
					$out .= '<input type="hidden" name="action" value="cnf-submission" />';

					if ( get_query_var( 'cn-process' ) == 'edit' ) {

						// Lets create a token from the raw entry data array to be used to compare
						// before updating an entry. Just in case someone gets the idea to change the
						// `entry_id` value before submitting the form to try to overwrite another entry.
						$token = md5( json_encode( $data ) );

						$out .= '<input type="hidden" name="entry_id" value="' . $entry->getId() . '" />';
						$out .= '<input type="hidden" name="cnf-token" value="' . $token . '" />';

						// Use the token to create an entry specific ajax nonce that must pass validation
						// before an entry will be modified.
						$out .= '<input type="hidden" name="ajaxnonce" value="' . wp_create_nonce( 'cnf-add_edit_entry-' . $token ) . '" />';

					} else {

						$out .= '<input type="hidden" name="ajaxnonce" value="' . wp_create_nonce( 'cnf-add_new_entry' ) . '" />';
					}

					$out .= '<p class="cn-add"><input class="cn-button-shell cn-button green" id="cn-form-submit-new" type="submit" name="save" value="' . __('Submit' , 'connections_form' ) . '" /></p>' . PHP_EOL;

				$out .= '</form>';
			$out .= '</div>';

			return $out;
		}

		/**
		 * The custom category select metabox.
		 *
		 * @access  private
		 * @since  2.0
		 * @param  object $entry   An instance of the cnEntry object.
		 * @param  array  $metabox The registered metabox options array.
		 *
		 * @return string          HTML select drop down for categories.
		 */
		public static function categoryMetabox( $entry, $metabox ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			// The query var needs set so the category select will have the categories assigned to the entry set as selected.
			if ( $id = $entry->getId() ) {

				set_query_var( 'cn-cat', $instance->term->getTermRelationships( $entry->getId() ) );
			}

			$out = cnTemplatePart::category( array(
				'name'   => 'entry_category',
				'style'  => array( 'width' => '100%' ),
				'type'   => 'multiselect',
				'return' => TRUE,
				)
			);

			$out .=  PHP_EOL . '<div class="cn-clear"></div>';

			echo $out;
		}

		/**
		 * The custom entry visibilty metabox.
		 *
		 * @access  private
		 * @since  2.0
		 * @param  object $entry   An instance of the cnEntry object.
		 * @param  array  $metabox The registered metabox options array.
		 *
		 * @return string          The entry visibility radio group.
		 */
		public static function visibilityMetabox( $entry, $metabox ) {

			$visibility = $entry->getVisibility() ? $entry->getVisibility() : 'public';

			cnHTML::radio(
				array(
					'display' => 'inline',
					'id'      => 'visibility',
					'options' => array(
						'public'   => __( 'Public', 'connections' ),
						'private'  => __( 'Private', 'connections' ),
						// 'unlisted' => __( 'Unlisted', 'connections' ),
						),
					),
				$visibility
				);

		}

		/**
		 * Callback used to render the "Name" metabox.
		 *
		 * @access private
		 * @since 2.0
		 * @param  object $entry   An instance of the cnEntry object.
		 * @param  array  $metabox The metabox attributes array.
		 *
		 * @return void
		 */
		public static function nameMetabox( $entry, $metabox ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			// This array will store field group IDs as the fields are registered.
			// This array will be checked for an existing ID before rendering
			// a field to prevent multiple field group IDs from being rendered.
			$groupIDs = array();

			// This array will store field IDs as the fields are registered.
			// This array will be checked for an existing ID before rendering
			// a field to prevent multiple field IDs from being rendered.
			$fieldIDs = array();

			$out = '';

			$defaults = array(
				// Define the entry type so the correct fields will be rendered. If an entry type is all registered entry types, render all fields assuming this is new entry.
				'type'  => $entry->getEntryType() ? $entry->getEntryType() : array( 'individual', 'organization' ),
				// The entry type to which the meta fields are being registered.
				'individual' => array(
					// The entry type field meta. Contains the arrays that define the field groups and their respective fields.
					'meta'   => array(
						// This key is the field group ID and it must be unique. Duplicates will be discarded.
						'name' => array(
							// Whether or not to render the field group.
							'show'  => TRUE,
							// The fields within the field group.
							'field' => array(
								// This key is the field ID.
								'prefix' => array(
									// Each field must have an unique ID. Duplicates will be discarded.
									'id'        => 'honorific_prefix',
									// Whether or not to render the field.
									'show'      => TRUE,
									// The field label if supplied.
									'label'     => __( 'Prefix' , 'connections' ),
									// Whether or not the field is required. If it is required 'class="required"' will be added to the field.
									// This will be used by jQuery Validate.
									'required'  => FALSE,
									// The field type.
									'type'      => 'text',
									// The field value.
									'value'     => strlen( $entry->getHonorificPrefix() ) > 0 ? $entry->getHonorificPrefix() : '',
									'before'    => '<span id="cn-name-prefix">',
									'after'     => '</span>',
									),
								'first' => array(
									'id'        => 'first_name',
									'show'      => TRUE,
									'label'     => __( 'First Name' , 'connections' ),
									'required'  => TRUE,
									'type'      => 'text',
									'value'     => strlen( $entry->getFirstName() ) > 0 ? $entry->getFirstName() : '',
									'before'    => '<span id="cn-name-first">',
									'after'     => '</span>',
									),
								'middle' => array(
									'id'        => 'middle_name',
									'show'      => TRUE,
									'label'     => __( 'Middle Name' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getMiddleName() ) > 0 ? $entry->getMiddleName() : '',
									'before'    => '<span id="cn-name-middle">',
									'after'     => '</span>',
									),
								'last' => array(
									'id'        => 'last_name',
									'show'      => TRUE,
									'label'     => __( 'Last Name' , 'connections' ),
									'required'  => TRUE,
									'type'      => 'text',
									'value'     => strlen( $entry->getLastName() ) > 0 ? $entry->getLastName() : '',
									'before'    => '<span id="cn-name-last">',
									'after'     => '</span>',
									),
								'suffix' => array(
									'id'        => 'honorific_suffix',
									'show'      => TRUE,
									'label'     => __( 'Suffix' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getHonorificSuffix() ) > 0 ? $entry->getHonorificSuffix() : '',
									'before'    => '<span id="cn-name-suffix">',
									'after'     => '</span>',
									),
								),
							),
						'title' => array(
							'show'  => TRUE,
							'field' => array(
								'title' => array(
									'id'        => 'title',
									'show'      => TRUE,
									'label'     => __( 'Title' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getTitle() ) > 0 ? $entry->getTitle() : '',
									),
								),
							),
						'organization' => array(
							'show'  => TRUE,
							'field' => array(
								'organization' => array(
									'id'        => 'organization',
									'show'      => TRUE,
									'label'     => __( 'Organization' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getOrganization() ) > 0 ? $entry->getOrganization() : '',
									),
								),
							),
						'department' => array(
							'show'  => TRUE,
							'field' => array(
								'department' => array(
									'id'        => 'department',
									'show'      => TRUE,
									'label'     => __( 'Department' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getDepartment() ) > 0 ? $entry->getDepartment() : '',
									),
								),
							),
						),
					),
				'organization' => array(
					'meta' => array(
						'organization' => array(
							'show'  => TRUE,
							'field' => array(
								'organization' => array(
									'id'        => 'organization',
									'show'      => TRUE,
									'label'     => __( 'Organization' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getOrganization() ) > 0 ? $entry->getOrganization() : '',
									),
								),
							),
						'department' => array(
							'show'  => TRUE,
							'field' => array(
								'department' => array(
									'id'        => 'department',
									'show'      => TRUE,
									'label'     => __( 'Department' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getDepartment() ) > 0 ? $entry->getDepartment() : '',
									),
								),
							),
						'contact' => array(
							'show'  => TRUE,
							'field' => array(
								'contact_first_name' => array(
									'id'        => 'contact_first_name',
									'show'      => TRUE,
									'label'     => __( 'Contact First Name' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getContactFirstName() ) > 0 ? $entry->getContactFirstName() : '',
									'before'    => '<span class="cn-half-width" id="cn-contact-first-name">',
									'after'     => '</span>',
									),
								'contact_last_name' => array(
									'id'        => 'contact_last_name',
									'show'      => TRUE,
									'label'     => __( 'Contact Last Name' , 'connections' ),
									'required'  => FALSE,
									'type'      => 'text',
									'value'     => strlen( $entry->getContactLastName() ) > 0 ? $entry->getContactLastName() : '',
									'before'    => '<span class="cn-half-width" id="cn-contact-last-name">',
									'after'     => '</span>',
									),
								),
							),
						),
					),
				);

			$atts = wp_parse_args( apply_filters( 'cn_metabox_name_atts', $metabox['atts'] ), $defaults );

			if ( $atts['select_type'] ) {

				cnHTML::field(
					array(
						'type'     => 'radio',
						// 'class'    => $field['class'],
						'id'       => 'entry_type',
						// 'style'    => $field['style'],
						'options'  => array(
							'individual'   => __( 'Individual' , 'connections' ),
							'organization' => __( 'Organization' , 'connections' ),
							),
						'required' => TRUE,
						'before'   => '<div id="cn-entry-type"><span id="cn-entry-type-label">' . __( 'I am an' , 'connections_form' ) . '</span>',
						'after'    => '</div>',
						// 'return'   => TRUE,
					),
					$atts['default_type']
				);

			} else {

				// Hidden Field -- For the default entry type if the user selectable radio is disabled.
				echo '<input type="hidden" name="entry_type" value="' . $atts['default_type'] . '" />' . PHP_EOL;
			}

			foreach ( (array) $atts['type'] as $entryType ) {

				if ( array_key_exists( $entryType, $atts ) ) {

					if ( isset( $atts[ $entryType ]['callback'] ) ) {

						call_user_func( $atts[ $entryType ]['callback'], $entry, $atts[ $entryType ]['meta'] );
						continue;
					}

					/*
					 * Dump the output in a var that way it can more more easily broke up and filters added later.
					 */
					$out = '';

					foreach ( $atts[ $entryType ]['meta'] as $type => $meta ) {

						if ( in_array( $type, $groupIDs ) ) {

							continue;

						} else {

							$groupIDs[] = $type;
						}

						// $out .= '<div class="cn-metabox" id="cn-metabox-section-' . $type . '">' . PHP_EOL;
						$out .= '<div id="cn-metabox-section-' . $type . '">' . PHP_EOL;

						if ( $meta['show'] == TRUE ) {

							foreach( $meta['field'] as $field ) {

								if ( in_array( $field['id'], $fieldIDs ) ) {

									continue;

								} else {

									$fieldIDs[] = $field['id'];
								}

								if ( $field['show'] ) {

									$defaults = array(
										'type'     => '',
										'class'    => array(),
										'id'       => '',
										'style'    => array(),
										'options'  => array(),
										'value'    => '',
										'required' => FALSE,
										'label'    => '',
										'before'   => '',
										'after'    => '',
										'return'   => TRUE,
										);

									$field = wp_parse_args( $field, $defaults );

									$out .= cnHTML::field(
										array(
											'type'     => $field['type'],
											'class'    => $field['class'],
											'id'       => $field['id'],
											'style'    => $field['style'],
											'options'  => $field['options'],
											'required' => $field['required'],
											'label'    => $field['label'],
											'before'   => $field['before'],
											'after'    => $field['after'],
											'return'   => TRUE,
										),
										$field['value']
									);
								}
							}
						}

						$out .= '</div>' . PHP_EOL;
					}

					echo $out;
				}

			}
		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 2.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Form() {

			if ( class_exists('connectionsLoad') ) {

					return Connections_Form::instance();

			} else {

					add_action(
						'admin_notices',
						 create_function(
							'',
							'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Form.</p></div>\';'
							)
					);

					return FALSE;
			}
	}

	/**
	 * We'll load the extension on `plugins_loaded` so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Form' );
}
