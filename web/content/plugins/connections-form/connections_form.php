<?php
/*
Plugin Name: Connections Form
Plugin URI: http://www.connections-pro.com
Description: Connections Form
Version: 1.0.2
Author: Steven A. Zahm
Author URI: http://www.connections-pro.com
Text Domain: connections_form

Copyright 2010  Steven A. Zahm  (email : shazahm1@hotmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'connectionsFormLoad' ) ) {

	class connectionsFormLoad {
		public $options;
		public $settings;
		private $demo = FALSE;

		public function __construct() {
			$this->loadConstants();

			//register_activation_hook( dirname(__FILE__) . '/connections_form.php', array( $this, 'activate' ) );
			//register_deactivation_hook( dirname(__FILE__) . '/connections_form.php', array( $this, 'deactivate' ) );

			// Start this plug-in once all other plugins are fully loaded
			add_action( 'plugins_loaded', array( $this , 'start' ) );
		}

		public function start() {

			if ( class_exists( 'connectionsLoad' ) ) {
				load_plugin_textdomain( 'connections_form' , false , CNFM_DIR_NAME . '/lang' );
				
				require_once(dirname( __FILE__ ) . '/includes/class.default-values.php');//temp correct later
				require_once(dirname( __FILE__ ) . '/includes/class.form-parts.php');//temp correct later
				
				$this->settings = cnSettingsAPI::getInstance();

				/*
				 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
				 * Init the registered settings.
				 * NOTE: The init method must be run after registering the tabs, sections and fields.
				 */
				add_filter( 'cn_register_settings_tabs' , array( $this, 'registerSettingsTab' ) );
				add_filter( 'cn_register_settings_sections' , array( $this, 'registerSettingsSections' ) );
				add_filter( 'cn_register_settings_fields' , array( $this, 'registerSettingsFields' ) );
				$this->settings->init();

				//add_action( 'admin_init' , array( $this, 'adminInit' ) );
				add_action( 'init' , array( $this, 'init' ) );

			} else {

				add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order to use Form.</p></div>\';'
					)
				);

			}
		}

		private function loadConstants() {

			define( 'CNFM_CURRENT_VERSION', '1.0.2' );

			define( 'CNFM_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNFM_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNFM_BASE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNFM_BASE_URL', plugin_dir_url( __FILE__ ) );
		}

		public function adminInit() {

		}

		public function init() {

			
			if ( ! $this->demo ) {
				add_action( 'wp_ajax_cn-form-new-submit' , array( $this, 'newSubmission' ) );
				add_action( 'wp_ajax_nopriv_cn-form-new-submit' , array( $this, 'newSubmission' ) );
			}

			add_shortcode( 'connections_form', array( $this, 'shortcode') );

			add_action( 'wp_enqueue_scripts', array( $this, 'loadScripts' ) );
			add_action( 'wp_print_styles', array( $this, 'loadStyles' ) );
		}

		/**
		 * Called when activating Connections Form via the activation hook.
		 * @return void
		 */
		public function activate() {

		}

		/**
		 * Called when deactivating Connections Form via the deactivation hook.
		 * @return void
		 */
		public function deactivate() {

		}

		/**
		 * Called when running the wp_enqueue_scripts action.
		 *
		 * @return null
		 */
		public function loadScripts() {
			global $connections;
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			if ( ! is_admin() ) {
				if ( $connections->options->getGoogleMapsAPI()  ) {
					if ( ! is_ssl() ) wp_enqueue_script( 'cn-google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
					if ( is_ssl() ) wp_enqueue_script( 'cn-google-maps-api', 'https://maps.googleapis.com/maps/api/js?sensor=false', array( 'jquery' ), CN_CURRENT_VERSION, $connections->options->getJavaScriptFooter() );
		
		
					wp_enqueue_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' , 'cn-google-maps-api' ), '1.3.2', $connections->options->getJavaScriptFooter() );
					wp_enqueue_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'cn-google-maps-api' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
				} else {
					wp_enqueue_script( 'jquery-gomap-min', CN_URL . "assets/js/jquery.gomap-1.3.2$min.js", array( 'jquery' ), '1.3.2', $connections->options->getJavaScriptFooter() );
					wp_enqueue_script( 'jquery-markerclusterer-min', CN_URL . "assets/js/jquery.markerclusterer$min.js", array( 'jquery' , 'jquery-gomap-min' ), '2.0.15', $connections->options->getJavaScriptFooter() );
				}
				wp_enqueue_script( 'cn-form-ui-user' , CNFM_BASE_URL . 'js/cn-form-user.js', array('jquery' , 'jquery-form') , CNFM_CURRENT_VERSION , TRUE );

				$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
				$attr = array( 'ajaxurl' => admin_url( 'admin-ajax.php' , $protocol ) , 'debug' => $this->demo );

				// Localize the Submission strings.
				$attr['strSubmitting'] = __('Please wait, your submission is being processed.' , 'connections_form' );
				$attr['strSubmitted'] = __('Submission received.' , 'connections_form' );
				$attr['strSubmittedMsg'] = __('We will review and approve your submission soon.' , 'connections_form' );

				wp_localize_script( 'cn-form-ui-user' , 'cn_form' , $attr );

				wp_enqueue_script( 'jquery-validate' , CNFM_BASE_URL . '/js/jquery.validate.min.js', array('jquery' , 'jquery-form') , '1.9.0' , TRUE );
				wp_enqueue_script( 'jquery-chosen-min' );
				
				
				
				
				
			}
		}

		/**
		 * Called when running the wp_print_styles action.
		 *
		 * @return null
		 */
		public function loadStyles() {
			if ( ! is_admin() ) wp_enqueue_style('cn-form-user', CNFM_BASE_URL . '/css/cn-form-user.css', array(), CNFM_CURRENT_VERSION);
		}

		/**
		 * Add the Form settings tab on the Connections : Settings admin page.
		 */
		public function registerSettingsTab( $tabs ) {
			global $connections;

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
				'tab'       => 'form' ,
				'id'        => 'connections_form_login' ,
				'position'  => 10 ,
				'title'     => __( 'Require Login' , 'connections_form' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			$sections[] = array(
				'tab'       => 'form' ,
				'id'        => 'connections_form_email_notifications' ,
				'position'  => 20 ,
				'title'     => __( 'Email Notifications' , 'connections_form' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			$sections[] = array(
				'tab'       => 'form' ,
				'id'        => 'connections_form_preferences' ,
				'position'  => 20 ,
				'title'     => __( 'Form Preferences' , 'connections_form' ) ,
				'callback'  => '' ,
				'page_hook' => $settings );
			return $sections;
		}

		public function registerSettingsFields( $fields ) {
			$current_user = wp_get_current_user();

			$settings = 'connections_page_connections_settings';

			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'required',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_login',
				'title'     => __('Login Required', 'connections_form'),
				'desc'      => __('Require registered users to login before showing the entry submission form.', 'connections_form'),
				'help'      => __('Check this option if you wish to only allow registered users to submit entries for your review and approval.', 'connections_form'),
				'type'      => 'checkbox',
				'default'   => 0
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'message',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_login',
				'title'     => __('Message', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'rte',
				'default'   => __('Please login in order to submit your entry to our directory.', 'connections_form')
			);
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




			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_show_countries',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Choose Courtries to display in address', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'multiselect',
				'options'   => cnDefaultValues::getCountriesCodeToName(),
				'default'   => 'US'
			);


			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_show_countries',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Choose Courtries to display in address', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'multiselect',
				'options'   => cnDefaultValues::getCountriesCodeToName(),
				'default'   => 'US'
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_countries_display_code',
				'position'  => 35,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Display Countries Code over name', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 0
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_regions_display_code',
				'position'  => 35,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Display Region Codes over name', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 0
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_open_block',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Choose Blocks to open by default', 'connections_form'),
				'desc'      => '',
				'help'      => '',
				'type'      => 'multiselect',
				'options'   => array('address'=>__('Phyiscal addresses', 'connections_form'),
									'phone'=>__('Phone numbers', 'connections_form'),
									'email'=>__('Email addresses', 'connections_form'),
									'messenger'=>__('Messengers', 'connections_form'),
									'social'=>__('Social', 'connections_form'),
									'link'=>__('Links', 'connections_form')
									),
				'default'   => 'null'
			);
			$fields[] = array(
				'plugin_id' => 'connections_form',
				'id'        => 'form_preference_use_blocks',
				'position'  => 50,
				'page_hook' => $settings,
				'tab'       => 'form',
				'section'   => 'connections_form_preferences',
				'title'     => __('Choose Blocks to Use ', 'connections_form'),
				'desc'      => __('Any registered blocks choosen will show up in the order selected', 'connections_form'),
				'help'      => __('Use the handle on the right to drag and reorder blocks', 'connections_form'),
				'type'      => 'multiselect',
				'options'   => cnfmFormParts::getRegisteredBlocks(),
				'default'   => 'null'
			);



//


			return $fields;
		}

		public function newSubmission() {
			global $connections;

			$form = new cnFormObjects();
			$sendTo ='';
			$entryAdded = FALSE;

			check_admin_referer( $form->getNonce('add_entry'), '_cn_wpnonce' );

			/*
			 * @todo: Create array of valid fields and strip any fields that shouldn't be present.
			 */
			include_once ( CN_PATH . '/includes/inc.processes.php' );
			$entryAdded = processEntry( $_POST , 'add' );
			//print_r($_POST);

			/*
			 * If the entry was added successfully, send the email notifications accrodingly.
			 */
			if ( $entryAdded ) {

				/*
				 * If the user provided an email address, send an auto reply response email.
				 *
				 * Grab the last insert id created when inserting a new entry and setup a new cnEntry object.
				 */
				$id = $connections->lastInsertID;

				$entry = new cnEntry();
				$entry->set( $id );

				// Get the preferred email address if one was set by the submitter.
				$emailAddresses = $entry->getEmailAddresses( array( 'preferred' => TRUE), TRUE, TRUE );

				if ( ! empty( $emailAddresses ) ) {

					if ( isset( $emailAddresses[0]->address ) ) $sendTo = $emailAddresses[0]->address;

				}

				// If no preferred email address was set, grab all email address and then set the first email address to send the auto-reply to.
				if ( empty( $sendTo ) ) {

					$emailAddresses = $entry->getEmailAddresses( array(), TRUE, TRUE );

					if ( ! empty($emailAddresses ) ) {

						if ( isset( $emailAddresses[0]->address ) ) $sendTo = $emailAddresses[0]->address;

					}
				}


				// Set the email format header to text/html
				// Add the filter here so it is applied to both emails.
				add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );


				// If an email address was supplied preferred or otherwise, send the auto-reply as applicable.
				if ( ! empty( $sendTo ) ) {

					if ( $connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'auto_reply' ) ) {

						// Set the email From email
						$fromEmail = create_function( '', 'global $connections; return $connections->settings->get( "connections_form" , "connections_form_email_notifications" , "auto_reply_from_email" );' );
						add_filter( 'wp_mail_from', $fromEmail );

						// Set the email From name
						$fromName = create_function( '', 'global $connections; return $connections->settings->get( "connections_form" , "connections_form_email_notifications" , "auto_reply_from_name" );' );
						add_filter( 'wp_mail_from_name', $fromName );

						wp_mail(
							$sendTo ,
							$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'auto_reply_subject' ) ,
							$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'auto_reply_message' )
						);

						remove_filter( 'wp_mail_from', $fromEmail );
						remove_filter( 'wp_mail_from_name', $fromName );

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
						die(1);

					} else {

						if ( $connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice' ) ) {

							// Set the email From email
							$fromEmail = create_function( '', 'global $connections; return $connections->settings->get( "connections_form" , "connections_form_email_notifications" , "submission_notice_to" );' );
							add_filter( 'wp_mail_from', $fromEmail );

							// Set the email From name
							$fromName = create_function( '', 'return get_bloginfo("name");' );
							add_filter( 'wp_mail_from_name', $fromName );

							wp_mail(
								$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_to' ) ,
								$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_subject' ) ,
								$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_message' )
							);

							remove_filter( 'wp_mail_from', $fromEmail );
							remove_filter( 'wp_mail_from_name', $fromName );

						}

						/*
						 * End, report success.
						 *
						 * 2 == Success, moderated.
						 */
						die(2);

					}

				} else {

					if ( $connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice' ) ) {

						// Set the email From email
						$fromEmail = create_function( '', 'global $connections; return $connections->settings->get( "connections_form" , "connections_form_email_notifications" , "submission_notice_to" );' );
						add_filter( 'wp_mail_from', $fromEmail );

						// Set the email From name
						$fromName = create_function( '', 'return get_bloginfo("name");' );
						add_filter( 'wp_mail_from_name', $fromName );

						wp_mail(
							$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_to' ) ,
							$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_subject' ) ,
							$connections->settings->get( 'connections_form' , 'connections_form_email_notifications' , 'submission_notice_message' )
						);

						remove_filter( 'wp_mail_from', $fromEmail );
						remove_filter( 'wp_mail_from_name', $fromName );

					}

					/*
					 * End, report success.
					 *
					 * 2 == Success, moderated.
					 */
					die(2);

				}

			} else {

				/*
				 * End, report fail.
				 *
				 * -1 == Failed.
				 */
				die(-1);

			}

		}





		/**
		 * @todo: Add honeypot fields for bots.
		 */
		public function shortcode( $atts , $content = NULL ) {
			global $connections;

			if ( ! is_user_logged_in() && $connections->settings->get( 'connections_form' , 'connections_form_login' , 'required' ) ) {

				$message = $connections->settings->get( 'connections_form' , 'connections_form_login' , 'message' );

				// Format and texturize the message.
				$message = wptexturize( wpautop( $message ) );

				// Make any links and such clickable.
				$message = make_clickable( $message );

				// Apply the shortcodes.
				$message = do_shortcode( $message );

				return $message;
			}

			$date = new cnDate();
			$form = new cnFormObjects();
			$convert = new cnFormatting();
			$format =& $convert;
			$entry = new cnEntry();
			$out = '';

			$atts = shortcode_atts(
				array(
					'default_type'     => 'individual',
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
					'use_blocks'       => $connections->settings->get( 'connections_form' , 'connections_form_preferences' , 'form_preference_use_blocks' ),
					'open_blocks'      => $connections->settings->get( 'connections_form' , 'connections_form_preferences' , 'form_preference_open_block' ),
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

			// Permitted entry types
			$permittedTypes = array( 'individual' , 'organization' );

			// Enforce permitted entry type
			if ( ! in_array($atts['default_type'], $permittedTypes) ) $atts['default_type'] = 'individual';

			$out .= '<div id="cn-form-container">' . "\n";

				$out .= '<div id="cn-form-ajax-response"><ul></ul></div>' . "\n";
	
				$out .= '<form id="cn-form" method="POST" enctype="multipart/form-data">' . "\n";
	
					//( $entry->getVisibility() ) ? $visibility = $entry->getVisibility() : $visibility = 'unlisted';
	
					//Loop over all the blocks and add it to the output string

					if(!empty($atts['use_blocks'])){
						foreach($atts['use_blocks'] as $code){
							//do_action( 'cnfm_block_creation_before-'.$code, $entry, $atts);
							$blockStr = apply_filters( 'cnfm_block_creation_before-'.$code, "");
							$blockStr .= cnfmFormParts::getFormBlock($code,$entry, $atts);
							$blockStr = apply_filters( 'cnfm_block_creation_after-'.$code, $blockStr);
							$out .= $blockStr;
							$already_used[]='metabox-'.$code;
						} 
					}else{
						$out.="<h2>".__('No blocks were choosen' , 'connections_form' )."</h2>";	
					}
	
					// Hidden Field -- 'action' required to trigger the registered action.
					$out .= '<input type="hidden" name="action" value="cn-form-new-submit" />';

					ob_start();

					
					
					cnMetabox_Render::metaboxes( array( 'exclude' => array_merge($already_used,array('leveled', 'last-emailed','submitdiv', 'categorydiv', 'metabox-meta', 'metabox-logo', 'metabox-messenger','metabox-social-media','metabox-note','metabox-date' )) ), $entry );
					$form->tokenField('add_entry');
					$out .= ob_get_contents();
					ob_end_clean();
/**/
		
					// Hidden Field -- set the default entry visibilty to unlisted.
					$out .= '<input id="visibility" type="hidden" name="visibility" value="unlisted">';
		
					$out .=  '<p class="cn-add"><input class="cn-button-shell cn-button green" id="cn-form-submit-new" type="submit" name="save" value="' . __('Submit' , 'connections_form' ) . '" /></p>' . "\n";
	
				$out .= '</form>';
			$out .= '</div>';

			return $out;
		}


	}

	/*
	 * Initiate the plug-in.
	 */
	global $connectionsFormLoad;
	$connectionsFormLoad = new connectionsFormLoad();
}

?>