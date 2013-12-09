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

			if ( ! is_admin() ) {

				wp_enqueue_script( 'cn-form-ui-user' , CNFM_BASE_URL . '/js/cn-form-user.js', array('jquery' , 'jquery-form') , CNFM_CURRENT_VERSION , TRUE );

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

			return $sections;
		}

		public function registerSettingsFields( $fields )
		{
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
			( $entry->getEntryType() ) ? $type = $entry->getEntryType() : $type = $atts['default_type'];


			/*
			 * Name Field
			 */
			$out .= '<div id="metabox-name" class="postbox">' . "\n";
				$out .= '<h3 class="hndle">';

					if ( $atts['select_type'] )
					{
						$out .= '<span>' . __('I am an' , 'connections_form' ) . ':</span>' . "\n";
						$out .= $form->buildRadio("entry_type","entry_type",array( __('Individual' , 'connections_form' ) => 'individual' , __('Organization' , 'connections_form' ) => 'organization' ), $type);
					}
					else
					{
						if ( ! empty( $atts['str_contact_name'] ) ) $out .= '<span>' . $atts['str_contact_name'] . '</span>' . "\n";

						// Hidden Field -- For the default entry type if the user selectable radio is disabled.
						$out .= '<input type="hidden" name="entry_type" value="' . $type . '" />' . "\n";
					}

				$out .= '</h3>' . "\n";
				$out .= '<div class="cnf-inside">' . "\n";

					$out .= '<div class="form-field" id="cn-name">' . "\n";

								$out .= '<div id="honorific-prefix" class="cn-float-left"><label>' . __('Prefix' , 'connections_form' ) . ': <input type="text" name="honorific_prefix" value="' . $entry->getHonorificPrefix() . '"></label></div>' . "\n";
								$out .= '<div id="first-name" class="cn-float-left"><label>' . __('First Name' , 'connections_form' ) . ': <input class="required" type="text" name="first_name" value="' . $entry->getFirstName() . '"></label></div>' . "\n";
								$out .= '<div id="middle-name" class="cn-float-left"><label>' . __('Middle Name' , 'connections_form' ) . ': <input type="text" name="middle_name" value="' . $entry->getMiddleName() . '"></label></div>' . "\n";
								$out .= '<div id="last-name" class="cn-float-left"><label>' . __('Last Name' , 'connections_form' ) . ': <input class="required" type="text" name="last_name" value="' . $entry->getLastName() . '"></label></div>' . "\n";
								$out .= '<div id="honorific-suffix" class="cn-float-left"><label>' . __('Suffix' , 'connections_form' ) . ': <input type="text" name="honorific_suffix" value="' . $entry->getHonorificSuffix() . '"></label></div>' . "\n";
								$out .= '<div class="cn-clear"></div>' . "\n";
								$out .= '<div id="title"><label>' . __('Title' , 'connections_form' ) . ': <input type="text" name="title" value="' . $entry->getTitle() . '"></label></div>' . "\n";

					$out .= '</div>' . "\n";

					$out .= '<div class="form-field" id="cn-org-unit">';

								$out .= '<label>' . __('Organization' , 'connections_form' ) . ': <input class="required" type="text" name="organization" value="' . $entry->getOrganization() . '"></label>' . "\n";

								$out .= '<label>' . __('Department' , 'connections_form' ) . ': <input type="text" name="department" value="' . $entry->getDepartment() . '"></label>' . "\n";

								$out .= '<div id="cn-contact-name">' . "\n";
									$out .= '<div class="cn-float-left cn-half-width" id="contact-first-name"><label>' . __('Contact First Name' , 'connections_form' ) . ': <input type="text" name="contact_first_name" value="' . $entry->getContactFirstName() . '"></label></div>';
									$out .= '<div class="cn-float-left cn-half-width" id="contact-last-name"><label>' . __('Contact Last Name' , 'connections_form' ) . ': <input type="text" name="contact_last_name" value="' . $entry->getContactLastName() . '"></label></div>';

									$out .= '<div class="cn-clear"></div>' . "\n";
								$out .= '</div>' . "\n";

					$out .= '</div>' . "\n";
					$out .=  '<div class="cn-clear"></div>' . "\n";

				$out .=  '</div>' . "\n"; /* END --> .cnf-inside */
			$out .=  '</div>' . "\n"; /* END --> .postbox */

			/*
			 * Image Field
			 */
			if ( $atts['photo'] || $atts['logo'] )
			{
				$out .= '<div id="metabox-image" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Image' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
						$out .= '<div class="form-field">';

							if ( $atts['photo'] )
							{
								if ( $entry->getImageLinked() )
								{
									( $entry->getImageDisplay() ) ? $selected = 'show' : $selected = 'hidden';

									$imgOptions = $form->buildRadio('imgOptions', 'imgOptionID_', array( __('Display' , 'connections_form' ) => 'show' , __('Not Displayed' , 'connections_form' ) => 'hidden' , __('Remove' , 'connections_form' ) => 'remove' ) , $selected );
									$out .= '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getImageNameProfile() . '" /> <br /> <span class="radio_group">' . $imgOptions . '</span></div> <br />';
								}

								$out .= '<div class="clear"></div>';
								$out .= '<label for="original_image">' . __('Select Photo' , 'connections_form' ) . ': <input type="file" value="" name="original_image" size="25" /></label>';
							}

							if ( $atts['logo'] )
							{
								if ( $entry->getLogoLinked() )
								{
									( $entry->getLogoDisplay() ) ? $selected = 'show' : $selected = 'hidden';

									$logoOptions = $form->buildRadio('logoOptions', 'logoOptionID_', array( __('Display' , 'connections_form' ) => 'show' , __('Not Displayed' , 'connections_form' ) => 'hidden' , __('Remove' , 'connections_form' ) => 'remove' ), $selected);
									$out .= '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getLogoName() . '" /> <br /> <span class="radio_group">' . $logoOptions . '</span></div> <br />';
								}

								$out .= '<div class="clear"></div>';
								$out .= '<label for="original_logo">' . __('Select Logo' , 'connections_form' ) . ': <input type="file" value="" name="original_logo" size="25" /></label>';
							}

						$out .= '</div>';
					$out .= '<div class="cn-clear"></div>';
					$out .= '</div>';
				$out .= '</div>';
			}


			/*
			 * Address Field
			 */
			if ( $atts{'address'} )
			{
				$out .= '<div id="metabox-address" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Address Type' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="addresses">' . "\n";

						// --> Start template <-- \\
						$out .=  '<textarea id="address-template" style="display: none;">' . "\n";

							$out .= '<div class="widget-top">' . "\n";
								$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

								$out .= '<div class="widget-title"><h4>' . "\n";
									$out .= __('Address Type' , 'connections_form' ) . ': ' . $form->buildSelect('address[::FIELD::][type]', $connections->options->getDefaultAddressValues() ) . "\n";
									$out .= '<label><input type="radio" name="address[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
									//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('address[::FIELD::][visibility]', 'address_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
									$out .= '<input type="hidden" name="address[::FIELD::][visibility]" value="public">';
								$out .= '</h4></div>'  . "\n";

							$out .= '</div>' . "\n";

							$out .= '<div class="widget-inside">';

								$out .= '<div class="address-local">';
									$out .= '<div class="address-line">';
										$out .=  '<label for="address">' . __('Address Line 1' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][line_1]" value="">';
									$out .=  '</div>';

									$out .= '<div class="address-line">';
										$out .=  '<label for="address">' . __('Address Line 2' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][line_2]" value="">';
									$out .=  '</div>';

									$out .= '<div class="address-line">';
										$out .=  '<label for="address">' . __('Address Line 3' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][line_3]" value="">';
									$out .=  '</div>';

								$out .=  '</div>';

								$out .= '<div class="address-region">';
									$out .=  '<div class="address-city cn-float-left">';
										$out .=  '<label for="address">' . __('City' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][city]" value="">';
									$out .=  '</div>';
									$out .=  '<div class="address-state cn-float-left">';
										$out .=  '<label for="address">' . __('State' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][state]" value="">';
									$out .=  '</div>';
									$out .=  '<div class="address-zipcode cn-float-left">';
										$out .=  '<label for="address">' . __('Zipcode' , 'connections_form' ) . ':</label>';
										$out .=  '<input type="text" name="address[::FIELD::][zipcode]" value="">';
									$out .=  '</div>';
								$out .=  '</div>';

								$out .= '<div class="address-country">';
									$out .=  '<label for="address">' . __('Country' , 'connections_form' ) . '</label>';
									$out .=  '<input type="text" name="address[::FIELD::][country]" value="">';
								$out .=  '</div>';

								$out .= '<div class="address-geo">';
									$out .=  '<div class="address-latitude cn-float-left">';
										$out .=  '<label for="latitude">' . __('Latitude' , 'connections_form' ) . '</label>';
										$out .=  '<input type="text" name="address[::FIELD::][latitude]" value="">';
									$out .=  '</div>';
									$out .=  '<div class="address-longitude cn-float-left">';
										$out .=  '<label for="longitude">' . __('Longitude' , 'connections_form' ) . '</label>';
										$out .=  '<input type="text" name="address[::FIELD::][longitude]" value="">';
									$out .=  '</div>';
								$out .=  '</div>';

								$out .=  '<div class="cn-clear"></div>';
								$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="address" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

							$out .=  '</div>' . "\n";

						$out .=  '</textarea>' . "\n";
						// --> End template <-- \\


						$addresses = $entry->getAddresses( array(), FALSE );

						if ( ! empty($addresses) )
						{
							foreach ( $addresses as $address )
							{
								//$token = $form->token( $entry->getId() );
								$selectName = 'address['  . $token . '][type]';
								( $address->preferred ) ? $preferredAddress = 'CHECKED' : $preferredAddress = '';

								$out .= '<div class="widget address" id="address_row_'  . $token . '">' . "\n";
									$out .= '<div class="widget-top">' . "\n";
										$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

										$out .= '<div class="widget-title"><h4>' . "\n";
											$out .= 'Address Type: ' . $form->buildSelect($selectName, $connections->options->getDefaultAddressValues(), $address->type) . "\n";
											$out .= '<label><input type="radio" name="address[preferred]" value="' . $token . '" ' . $preferredAddress . '> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
											//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('address[' . $token . '][visibility]', 'address_visibility_'  . $token . $this->visibiltyOptions, $address->visibility) . '</span>' . "\n";
											$out .= '<input type="hidden" name="address[' . $token . '][visibility]" value="public">';
										$out .= '</h4></div>'  . "\n";

									$out .= '</div>' . "\n";

									$out .= '<div class="widget-inside">' . "\n";

										$out .= '<div class="address-local">' . "\n";
											$out .= '<div class="address-line">' . "\n";
												$out .=  '<label for="address">' . __('Address Line 1' , 'connections_form' ) . ':</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][line_1]" value="' . $address->line_1 . '">' . "\n";
											$out .= '</div>' . "\n";

											$out .= '<div class="address-line">' . "\n";
												$out .=  '<label for="address">' . __('Address Line 2' , 'connections_form' ) . ':</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][line_2]" value="' . $address->line_2 . '">' . "\n";
											$out .= '</div>' . "\n";

											$out .= '<div class="address-line">' . "\n";
												$out .=  '<label for="address">' . __('Address Line 3' , 'connections_form' ) . ':</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][line_3]" value="' . $address->line_3 . '">' . "\n";
											$out .= '</div>' . "\n";
										$out .= '</div>' . "\n";

										$out .= '<div class="address-region">' . "\n";
											$out .=  '<div class="address-city cn-float-left">' . "\n";
												$out .=  '<label for="address">' . __('City' , 'connections_form' ) . ':</label>';
												$out .=  '<input type="text" name="address[' . $token . '][city]" value="' . $address->city . '">' . "\n";
											$out .=  '</div>' . "\n";
											$out .=  '<div class="address-state cn-float-left">' . "\n";
												$out .=  '<label for="address">' . __('State' , 'connections_form' ) . ':</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][state]" value="' . $address->state . '">' . "\n";
											$out .=  '</div>' . "\n";
											$out .=  '<div class="address-zipcode cn-float-left">' . "\n";
												$out .=  '<label for="address">' . __('Zipcode' , 'connections_form' ) . ':</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][zipcode]" value="' . $address->zipcode . '">' . "\n";
											$out .=  '</div>' . "\n";
										$out .=  '</div>' . "\n";

										$out .= '<div class="address-country">' . "\n";
											$out .=  '<label for="address">' . __('Country' , 'connections_form' ) . '</label>' . "\n";
											$out .=  '<input type="text" name="address[' . $token . '][country]" value="' . $address->country . '">' . "\n";
										$out .=  '</div>' . "\n";

										$out .= '<div class="address-geo">' . "\n";
											$out .=  '<div class="address-latitude cn-float-left">' . "\n";
												$out .=  '<label for="latitude">' . __('Latitude' , 'connections_form' ) . '</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][latitude]" value="' . $address->latitude . '">' . "\n";
											$out .=  '</div>' . "\n";
											$out .=  '<div class="address-longitude cn-float-left">' . "\n";
												$out .=  '<label for="longitude">' . __('Longitude' , 'connections_form' ) . '</label>' . "\n";
												$out .=  '<input type="text" name="address[' . $token . '][longitude]" value="' . $address->longitude . '">' . "\n";
											$out .=  '</div>' . "\n";
										$out .=  '</div>' . "\n";

										$out .=  '<input type="hidden" name="address[' . $token . '][id]" value="' . $address->id . '">' . "\n";

										$out .=  '<div class="cn-clear"></div>' . "\n";

										$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="address" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

									$out .=  '</div>' . "\n";
								$out .=  '</div>' . "\n";

							}
						}

					$out .=  '</div>' . "\n";

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="address" data-container="addresses">' . __('Add Address' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}

			/*
			 * Phone Field
			 */
			if ( $atts['phone'] )
			{
				$out .= '<div id="metabox-phone" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Phone Numbers' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="phone-numbers">';

						// --> Start template <-- \\
						$out .=  '<textarea id="phone-template" style="display: none">';

							$out .= '<div class="widget-top">' . "\n";
								$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

								$out .= '<div class="widget-title"><h4>' . "\n";
									$out .= __('Phone Type' , 'connections_form' ) . ': ' . $form->buildSelect('phone[::FIELD::][type]', $connections->options->getDefaultPhoneNumberValues() ) . "\n";
									$out .= '<label><input type="radio" name="phone[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
									//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('phone[::FIELD::][visibility]', 'phone_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
									$out .= '<input type="hidden" name="phone[::FIELD::][visibility]" value="public">';
								$out .= '</h4></div>'  . "\n";

							$out .= '</div>' . "\n";

							$out .= '<div class="widget-inside">' . "\n";

								$out .=  '<label>' . __('Phone Number' , 'connections_form' ) . '</label><input type="text" name="phone[::FIELD::][number]" value="" style="width: 30%"/>' . "\n";
								$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="phone" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

							$out .= '</div>' . "\n";

						$out .=  '</textarea>';
						// --> End template <-- \\

						$phoneNumbers = $entry->getPhoneNumbers( array(), FALSE );

						if ( ! empty($phoneNumbers) )
						{
							foreach ($phoneNumbers as $phone)
							{
								//$token = $form->token( $entry->getId() );
								$selectName = 'phone['  . $token . '][type]';
								( $phone->preferred ) ? $preferredPhone = 'CHECKED' : $preferredPhone = '';

								$out .= '<div class="widget phone" id="phone-row-'  . $token . '">' . "\n";
									$out .= '<div class="widget-top">' . "\n";
										$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

										$out .= '<div class="widget-title"><h4>' . "\n";
											$out .= __('Phone Type' , 'connections_form' ) . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultPhoneNumberValues(), $phone->type) . "\n";
											$out .= '<label><input type="radio" name="phone[preferred]" value="' . $token . '" ' . $preferredPhone . '> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
											//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('phone[' . $token . '][visibility]', 'phone_visibility_'  . $token . $this->visibiltyOptions, $phone->visibility) . '</span>' . "\n";
											$out .= '<input type="hidden" name="phone[' . $token . '][visibility]" value="public">';
										$out .= '</h4></div>'  . "\n";

									$out .= '</div>' . "\n";

									$out .= '<div class="widget-inside">' . "\n";

										$out .=  '<label>' . __('Phone Number' , 'connections_form' ) . '</label><input type="text" name="phone[' . $token . '][number]" value="' . $phone->number . '" style="width: 30%"/>';
										$out .=  '<input type="hidden" name="phone[' . $token . '][id]" value="' . $phone->id . '">' . "\n";
										$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="phone" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

									$out .= '</div>' . "\n";
								$out .= '</div>' . "\n";
							}
						}

					$out .=  '</div>';

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="phone" data-container="phone-numbers">' . __('Add Phone Number' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Email Field
			 */
			if ( $atts['email'] )
			{
				$out .= '<div id="metabox-email" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Email' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="email-addresses">';

						// --> Start template <-- \\
						$out .=  '<textarea id="email-template" style="display: none">';

							$out .= '<div class="widget-top">' . "\n";
								$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

								$out .= '<div class="widget-title"><h4>' . "\n";
									$out .= __('Email Type' , 'connections_form' ) . ': ' . $form->buildSelect('email[::FIELD::][type]', $connections->options->getDefaultEmailValues() ) . "\n";
									$out .= '<label><input type="radio" name="email[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
									//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('email[::FIELD::][visibility]', 'email_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
									$out .= '<input type="hidden" name="email[::FIELD::][visibility]" value="public">';
								$out .= '</h4></div>'  . "\n";

							$out .= '</div>' . "\n";

							$out .= '<div class="widget-inside">' . "\n";

								$out .=  '<label>' . __('Email Address' , 'connections_form' ) . '</label><input type="text" name="email[::FIELD::][address]" value="" style="width: 30%"/>' . "\n";
								$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="email" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

							$out .= '</div>' . "\n";

						$out .=  '</textarea>';
						// --> End template <-- \\

						$emailAddresses = $entry->getEmailAddresses( array(), FALSE );

						if ( ! empty($emailAddresses) )
						{

							foreach ($emailAddresses as $email)
							{
								//$token = $form->token( $entry->getId() );
								$selectName = 'email['  . $token . '][type]';
								( $email->preferred ) ? $preferredEmail = 'CHECKED' : $preferredEmail = '';

								$out .= '<div class="widget email" id="email-row-'  . $token . '">' . "\n";
									$out .= '<div class="widget-top">' . "\n";
										$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

										$out .= '<div class="widget-title"><h4>' . "\n";
											$out .= __('Email Type' , 'connections_form' ) . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultEmailValues(), $email->type) . "\n";
											$out .= '<label><input type="radio" name="email[preferred]" value="' . $token . '" ' . $preferredEmail . '> ' . __('Preferred' , 'connections_form' ) .'</label>' . "\n";
											//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('email[' . $token . '][visibility]', 'email_visibility_'  . $token . $this->visibiltyOptions, $email->visibility) . '</span>' . "\n";
											$out .= '<input type="hidden" name="email[' . $token . '][visibility]" value="public">';
										$out .= '</h4></div>'  . "\n";

									$out .= '</div>' . "\n";

									$out .= '<div class="widget-inside">' . "\n";

										$out .=  '<label>' . __('Email Address' , 'connections_form' ) . '</label><input type="text" name="email[' . $token . '][address]" value="' . $email->address . '" style="width: 30%"/>';
										$out .=  '<input type="hidden" name="email[' . $token . '][id]" value="' . $email->id . '">' . "\n";
										$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="email" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

									$out .= '</div>' . "\n";
								$out .= '</div>' . "\n";
							}

						}

					$out .=  '</div>';

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="email" data-container="email-addresses">' . __('Add Email Address' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Messenger Field
			 */
			if ( $atts['messenger'] )
			{
				$out .= '<div id="metabox-messenger" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Messenger' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="im-ids">';

					// --> Start template.  <-- \\
					$out .=  '<textarea id="im-template" style="display: none">';

						$out .= '<div class="widget-top">' . "\n";
							$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

							$out .= '<div class="widget-title"><h4>' . "\n";
								$out .= __('IM Type' , 'connections_form' ) . ': ' . $form->buildSelect('im[::FIELD::][type]', $connections->options->getDefaultIMValues() ) . "\n";
								$out .= '<label><input type="radio" name="im[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) .'</label>' . "\n";
								//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('im[::FIELD::][visibility]', 'im_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
								$out .= '<input type="hidden" name="im[::FIELD::][visibility]" value="public">';
							$out .= '</h4></div>'  . "\n";

						$out .= '</div>' . "\n";

						$out .= '<div class="widget-inside">' . "\n";

							$out .=  '<label>' . __('IM Network ID' , 'connections_form' ) . '</label><input type="text" name="im[::FIELD::][id]" value="" style="width: 30%"/>' . "\n";
							$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="im" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

						$out .= '</div>' . "\n";

					$out .=  '</textarea>';
					// --> End template. <-- \\

					$imIDs = $entry->getIm( array(), FALSE );

					if ( ! empty($imIDs) )
					{
						foreach ($imIDs as $network)
						{
							//$token = $form->token( $entry->getId() );
							$selectName = 'im['  . $token . '][type]';
							( $network->preferred ) ? $preferredIM = 'CHECKED' : $preferredIM = '';

							$out .= '<div class="widget im" id="im-row-'  . $token . '">' . "\n";
								$out .= '<div class="widget-top">' . "\n";
									$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

									$out .= '<div class="widget-title"><h4>' . "\n";
										$out .= __('IM Type' , 'connections_form' ) . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultIMValues(), $network->type) . "\n";
										$out .= '<label><input type="radio" name="im[preferred]" value="' . $token . '" ' . $preferredIM . '> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
										//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('im[' . $token . '][visibility]', 'im_visibility_'  . $token . $this->visibiltyOptions, $network->visibility) . '</span>' . "\n";
										$out .= '<input type="hidden" name="im[' . $token . '][visibility]" value="public">';
									$out .= '</h4></div>'  . "\n";

								$out .= '</div>' . "\n";

								$out .= '<div class="widget-inside">' . "\n";

									$out .=  '<label>' . __('IM Network ID' , 'connections_form' ) . '</label><input type="text" name="im[' . $token . '][id]" value="' . $network->id . '" style="width: 30%"/>';
									$out .=  '<input type="hidden" name="im[' . $token . '][uid]" value="' . $network->uid . '">' . "\n";
									$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="im" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

								$out .= '</div>' . "\n";
							$out .= '</div>' . "\n";
						}

					}

					$out .=  '</div>';

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="im" data-container="im-ids">' . __('Add Messenger ID' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Social Media Field
			 */
			if ( $atts['social'] )
			{
				$out .= '<div id="metabox-social" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Social Media' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="social-media">';

					// --> Start template <-- \\
					$out .=  '<textarea id="social-template" style="display: none">';

						$out .= '<div class="widget-top">' . "\n";
							$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

							$out .= '<div class="widget-title"><h4>' . "\n";
								$out .= __('Social Network' , 'connections_form' ) . ': ' . $form->buildSelect('social[::FIELD::][type]', $connections->options->getDefaultSocialMediaValues() ) . "\n";
								$out .= '<label><input type="radio" name="social[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
								//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('social[::FIELD::][visibility]', 'social_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
								$out .= '<input type="hidden" name="social[::FIELD::][visibility]" value="public">';
							$out .= '</h4></div>'  . "\n";

						$out .= '</div>' . "\n";

						$out .= '<div class="widget-inside">' . "\n";

							$out .=  '<label>' . __('URL' , 'connections_form' ) . '</label><input type="text" name="social[::FIELD::][url]" value="http://" style="width: 30%"/>' . "\n";
							$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="social" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

						$out .= '</div>' . "\n";

					$out .=  '</textarea>';
					// --> End template <-- \\

					$socialNetworks = $entry->getSocialMedia( array(), FALSE );

					if ( ! empty($socialNetworks) )
					{
						foreach ($socialNetworks as $network)
						{
							//$token = $form->token( $entry->getId() );
							$selectName = 'social['  . $token . '][type]';
							( $network->preferred ) ? $preferredNetwork = 'CHECKED' : $preferredNetwork = '';

							$out .= '<div class="widget social" id="social-row-'  . $token . '">' . "\n";
								$out .= '<div class="widget-top">' . "\n";
									$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

									$out .= '<div class="widget-title"><h4>' . "\n";
										$out .= __('Social Network' , 'connections_form' ) . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultSocialMediaValues(), $network->type) . "\n";
										$out .= '<label><input type="radio" name="social[preferred]" value="' . $token . '" ' . $preferredNetwork . '> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
										//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('social[' . $token . '][visibility]', 'social_visibility_'  . $token . $this->visibiltyOptions, $network->visibility) . '</span>' . "\n";
										$out .= '<input type="hidden" name="social[' . $token . '][visibility]" value="public">';
									$out .= '</h4></div>'  . "\n";

								$out .= '</div>' . "\n";

								$out .= '<div class="widget-inside">' . "\n";

									$out .=  '<label>' . __('URL' , 'connections_form' ) . '</label><input type="text" name="social[' . $token . '][url]" value="' . $network->url . '" style="width: 30%"/>';
									$out .=  '<input type="hidden" name="social[' . $token . '][id]" value="' . $network->id . '">' . "\n";
									$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="social" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

								$out .= '</div>' . "\n";
							$out .= '</div>' . "\n";
						}

					}

					$out .=  '</div>';

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="social" data-container="social-media">' . __('Add Social Media ID' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Links Field
			 */
			if ( $atts['link'] )
			{
				$out .= '<div id="metabox-link" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . __('Links' , 'connections_form' ) . '</span></h3>';
					$out .= '<div class="cnf-inside">';
					$out .=  '<div class="widgets-sortables ui-sortable form-field" id="links">';

					// --> Start template <-- \\
					$out .=  '<textarea id="link-template" style="display: none">';

						$out .= '<div class="widget-top">' . "\n";
							$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

							$out .= '<div class="widget-title"><h4>' . "\n";
								$out .= __('Type' , 'connections_form' ) . ': ' . $form->buildSelect('link[::FIELD::][type]', $connections->options->getDefaultLinkValues() ) . "\n";
								$out .= '<label><input type="radio" name="link[preferred]" value="::FIELD::"> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
								//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('link[::FIELD::][visibility]', 'website_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
								$out .= '<input type="hidden" name="link[::FIELD::][visibility]" value="public">';
							$out .= '</h4></div>'  . "\n";

						$out .= '</div>' . "\n";

						$out .= '<div class="widget-inside">' . "\n";

							$out .= '<div>' . "\n";
								$out .=  '<label>' . __('Title' , 'connections_form' ) . '</label><input type="text" name="link[::FIELD::][title]" value="" style="width: 30%"/>' . "\n";
								$out .=  '<label>' . __('URL' , 'connections_form' ) . '</label><input type="text" name="link[::FIELD::][url]" value="http://" style="width: 30%"/>' . "\n";
							$out .= '</div>' . "\n";

							$out .= '<div>' . "\n";
								//$out .= '<span class="target">Target: ' . $form->buildSelect('link[::FIELD::][target]', array( 'new' => 'New Window', 'same' => 'Same Window' ), 'same' ) . '</span>' . "\n";
								//$out .= '<span class="follow">' . $form->buildSelect('link[::FIELD::][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), 'nofollow' ) . '</span>' . "\n";
								$out .= '<input type="hidden" name="link[::FIELD::][target]" value="new">';
								$out .= '<input type="hidden" name="link[::FIELD::][follow]" value="nofollow">';
							$out .= '</div>' . "\n";

							if ( $atts['photo'] || $atts['logo'] )
							{
								$out .= '<div>' . "\n";
									if ( $atts['photo'] ) $out .= '<label><input type="radio" name="link[image]" value="::FIELD::"> ' . __('Assign link to the image.' , 'connections_form' ) . '</label>' . "\n";
									if ( $atts['logo'] ) $out .= '<label><input type="radio" name="link[logo]" value="::FIELD::"> ' . __('Assign link to the logo.' , 'connections_form' ) . '</label>' . "\n";
								$out .= '</div>' . "\n";
							}

							$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="link" data-token="::FIELD::">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

						$out .= '</div>' . "\n";

					$out .=  '</textarea>';
					// --> End template <-- \\

					$links = $entry->getLinks( array(), FALSE );

					if ( ! empty($links) )
					{

						foreach ( $links as $link )
						{
							$selectName = 'link['  . $token . '][type]';
							( $link->preferred ) ? $preferredLink = 'CHECKED' : $preferredLink = '';
							( $link->image ) ? $imageLink = 'CHECKED' : $imageLink = '';
							( $link->logo ) ? $logoLink = 'CHECKED' : $logoLink = '';

							$out .= '<div class="widget link" id="link-row-'  . $token . '">' . "\n";
								$out .= '<div class="widget-top">' . "\n";
									$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";

									$out .= '<div class="widget-title"><h4>' . "\n";
										$out .= __('Type' , 'connections_form' ) . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultLinkValues(), $link->type) . "\n";
										$out .= '<label><input type="radio" name="link[preferred]" value="' . $token . '" ' . $preferredLink . '> ' . __('Preferred' , 'connections_form' ) . '</label>' . "\n";
										//$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('link[' . $token . '][visibility]', 'link_visibility_'  . $token . $this->visibiltyOptions, $link->visibility ) . '</span>' . "\n";
										$out .= '<input type="hidden" name="link[' . $token . '][visibility]" value="public">';
									$out .= '</h4></div>'  . "\n";

								$out .= '</div>' . "\n";

								$out .= '<div class="widget-inside">' . "\n";

									$out .= '<div>' . "\n";
										$out .=  '<label>' . __('Title' , 'connections_form' ) . '</label><input type="text" name="link[' . $token . '][title]" value="' . $link->title . '" style="width: 30%"/>' . "\n";
										$out .=  '<label>' . __('URL' , 'connections_form' ) . '</label><input type="text" name="link[' . $token . '][url]" value="' . $link->url . '" style="width: 30%"/>';
									$out .= '</div>' . "\n";

									$out .= '<div>' . "\n";
										//$out .= '<span class="target">Target: ' . $form->buildSelect('link[' . $token . '][target]', array( 'new' => 'New Window', 'same'  => 'Same Window' ), $link->target ) . '</span>' . "\n";
										//$out .= '<span class="follow">' . $form->buildSelect('link[' . $token . '][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), $link->followString ) . '</span>' . "\n";
										$out .= '<input type="hidden" name="link[::FIELD::][target]" value="new">';
										$out .= '<input type="hidden" name="link[::FIELD::][follow]" value="nofollow">';
									$out .= '</div>' . "\n";

									if ( $atts['photo'] || $atts['logo'] )
									{
										$out .= '<div>' . "\n";
											if ( $atts['photo'] ) $out .= '<label><input type="radio" name="link[image]" value="' . $token . '" ' . $imageLink . '> ' . __('Assign link to the image.' , 'connections_form' ) . '</label>' . "\n";
											if ( $atts['logo'] ) $out .= '<label><input type="radio" name="link[logo]" value="' . $token . '" ' . $logoLink . '> ' . __('Assign link to the logo.' , 'connections_form' ) . '</label>' . "\n";
										$out .= '</div>' . "\n";
									}

									$out .=  '<input type="hidden" name="link[' . $token . '][id]" value="' . $link->id . '">' . "\n";
									$out .=  '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="link" data-token="' . $token . '">' . __('Remove' , 'connections_form' ) . '</a></span></p>' . "\n";

								$out .= '</div>' . "\n";
							$out .= '</div>' . "\n";
						}

					}

					$out .=  '</div>';

					$out .=  '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="link" data-container="links">' . __('Add Link' , 'connections_form' ) . '</a></span></p>' . "\n";

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Date Field
			 */
			if ( $atts['anniversary'] || $atts['birthday'] )
			{
				$out .= '<div id="metabox-note" class="postbox">';
				$out .= '<h3 class="hndle"><span>' . __('Dates' , 'connections_form' ) . '</span></h3>';
				$out .= '<div class="cnf-inside">';

				if ( $atts['birthday'] )
				{
					// Birthday Field
					$out .= '<div class="form-field celebrate">
								<span class="selectbox">' . __('Birthday' , 'connections_form' ) . ': ' . $form->buildSelect('birthday_month',$date->months,$date->getMonth($entry->getBirthday())) . '</span>
								<span class="selectbox">' . $form->buildSelect('birthday_day',$date->days,$date->getDay($entry->getBirthday())) . '</span>
							</div>';
					$out .= '<div class="form-field celebrate-disabled"><p>' . __('Field not available for this entry type.' , 'connections_form' ) . '</p></div>';
				}

				if ( $atts['anniversary'] )
				{
					// Anniversary Field
					$out .= '<div class="form-field celebrate">
								<span class="selectbox">' . __('Anniversary' , 'connections_form' ) . ': ' . $form->buildSelect('anniversary_month',$date->months,$date->getMonth($entry->getAnniversary())) . '</span>
								<span class="selectbox">' . $form->buildSelect('anniversary_day',$date->days,$date->getDay($entry->getAnniversary())) . '</span>
							</div>';
					$out .= '<div class="form-field celebrate-disabled"><p>' . __('Field not available for this entry type.' , 'connections_form' ) . '</p></div>';
				}

				$out .=  '<div class="cn-clear"></div>';
				$out .=  '</div>';
			$out .=  '</div>';
			}


			/*
			 * Category Field
			 */
			if ( $atts['category'] )
			{
				$out .= '<div id="metabox-category" class="postbox">';
				$out .= '<h3 class="hndle"><span>' . __('Category' , 'connections_form' ) . '</span></h3>';
				$out .= '<div class="cnf-inside">';

					global $connections;
					$level = 0;
					$selected = 0;

					$categories = $connections->retrieve->categories();

					$out .= "\n" . '<select class="cn-cat-select" id="cn-category" name="entry_category[]" multiple="true" data-placeholder="' . __('Select Categories' , 'connections_form' ) . '" style="width:100%;">';

					$out .= "\n" . '<option value=""></option>';

					foreach ( $categories as $key => $category )
					{
						$out .= $this->buildOptionRowHTML($category, $level, $selected);
					}

					$out .= '</select>' . "\n";


					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Bio Field
			 */
			if ( $atts['bio'] )
			{
				$out .= '<div id="metabox-bio" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . $atts['str_bio'] . '</span></h3>';
					$out .= '<div class="cnf-inside">';

					if ( $atts['rte'] )
					{
						ob_start();
						wp_editor(	$entry->getBio(),
									'cn-form-bio',
									array
									(
										'media_buttons' => FALSE,
										'wpautop' => TRUE,
										'textarea_name' => 'bio',
										'tinymce' =>	array
														(
															'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
															'theme_advanced_buttons2' => '',
															'inline_styles' => TRUE,
															'relative_urls' => FALSE,
															'remove_linebreaks' => FALSE,
															'plugins' => 'inlinepopups,spellchecker,tabfocus,paste'
														),
										'quicktags' =>	array
														(
															'buttons' => 'strong,em,ul,ol,li,close',
														)
									)
								 );
						$out .= ob_get_contents();
						ob_end_clean();
					}
					else
					{
						$out .= '<textarea rows="20" cols="40" name="bio" id="cn-form-bio"></textarea>';
					}

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			/*
			 * Notes Field
			 */
			if ( $atts['notes'] )
			{
				$out .= '<div id="metabox-note" class="postbox">';
					$out .= '<h3 class="hndle"><span>' . $atts['str_notes'] . '</span></h3>';
					$out .= '<div class="cnf-inside">';

					if ( $atts['rte'] )
					{
						ob_start();
						wp_editor(	$entry->getNotes(),
									'cn-form-notes',
									array
									(
										'media_buttons' => FALSE,
										'wpautop' => TRUE,
										'textarea_name' => 'notes',
										'tinymce' =>	array
														(
															'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
															'theme_advanced_buttons2' => '',
															'inline_styles' => TRUE,
															'relative_urls' => FALSE,
															'remove_linebreaks' => FALSE,
															'plugins' => 'inlinepopups,spellchecker,tabfocus,paste'
														),
										'quicktags' =>	array
														(
															'buttons' => 'strong,em,ul,ol,li,close',
														)

									)
								 );
						$out .= ob_get_contents();
						ob_end_clean();
					}
					else
					{
						$out .= '<textarea rows="20" cols="40" name="notes" id="cn-form-notes"></textarea>';
					}

					$out .=  '<div class="cn-clear"></div>';
					$out .=  '</div>';
				$out .=  '</div>';
			}


			// Hidden Field -- 'action' required to trigger the registered action.
			$out .= '<input type="hidden" name="action" value="cn-form-new-submit" />';

			// Hidden Field -- to create a WP nonce to be used to validate before processing the form submission.
			ob_start();
			$form->tokenField('add_entry');
			$out .= ob_get_contents();
			ob_end_clean();

			// Hidden Field -- set the default entry visibilty to unlisted.
			$out .= '<input id="visibility" type="hidden" name="visibility" value="unlisted">';

			$out .=  '<p class="cn-add"><input class="cn-button-shell cn-button green" id="cn-form-submit-new" type="submit" name="save" value="' . __('Submit' , 'connections_form' ) . '" /></p>' . "\n";

			$out .= '</form>';
			$out .= '</div>';

			return $out;
		}

		/**
		 * Returns the options for the category select list.
		 *
		 * @author Steven A. Zahm
		 * @version 1.0
		 * @param object $category
		 * @param integer $level
		 * @param integer $selected
		 * @return string
		 */
		private function buildOptionRowHTML($category, $level, $selected)
		{
			$selectString = NULL;
			$out = '';

			//$pad = str_repeat('&emsp; ', max(0, $level));
			if ($selected == $category->term_id) $selectString = ' SELECTED ';

			//($this->showCategoryCount) ? $count = ' (' . $category->count . ')' : $count = '';

			//if ( ($this->showEmptyCategories && empty($category->count)) || ($this->showEmptyCategories || !empty($category->count)) || !empty($category->children) ) $out .= '<option style="margin-left: ' . $level . 'em;" value="' . $category->term_id . '"' . $selectString . '>' . /*$pad .*/ $category->name . $count . '</option>' . "\n";
			$out .= '<option style="margin-left: ' . $level . 'em;" value="' . $category->term_id . '"' . $selectString . '>' . $category->name . '</option>' . "\n";

			if ( ! empty($category->children) )
			{
				foreach ( $category->children as $child )
				{
					++$level;
					$out .= $this->buildOptionRowHTML($child, $level, $selected);
					--$level;
				}

			}

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