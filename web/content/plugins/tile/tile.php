<?php
/*
Plugin Name: Connections Tile - Template
Plugin URI: http://www.connections-pro.com
Description: Connections Tile - Template
Version: 3.0
Author: Steven A. Zahm
Author URI: http://www.connections-pro.com
Text Domain: connections_slim

Copyright 2013  Steven A. Zahm  (email : shazahm1@hotmail.com)

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

if ( ! class_exists( 'CN_Tile' ) ) {

	class CN_Tile {

		public function __construct() {

			$atts = array(
				'class'       => 'CNT_Tile',
				'name'        => 'Tile ',
				'type'        => 'all',
				'version'     => '3.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Business card style with fixed width and height which will float to create columns.',
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png'
				);

			cnTemplateFactory::register( $atts );
		}

	}

	class CNT_Tile {

		/**
		 * Stores a copy of the shortcode $atts for use throughout the class.
		 *
		 * @access private
		 * @since 3.0
		 * @var (array)
		 */
		private static $atts;

		/**
		 * Stores an initialized instance of cnTemplate.
		 *
		 * @access private
		 * @since 3.0
		 * @var (object)
		 */
		private static $template;

		/**
		 * Setup the template.
		 *
		 * @access public
		 * @since 3.0
		 * @param (object) $template An initialized instance of the cnTemplate class.
		 * @return (void)
		 */
		public function __construct( $template ) {

			self::$template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'file', 'path' => $template->getPath() . 'card.php' ) );
			$template->part( array( 'tag' => 'card_single', 'type' => 'file', 'path' => $template->getPath() . 'card-single.php' ) );
			// $template->part( array( 'tag' => 'js', 'type' => 'action', 'callback' => array( __CLASS__, 'enqueueJS' ) ) );

			if ( ! is_admin() ) {

				// Enqueue the frontend scripts and CSS.
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueCSS' ) );

				// Update the permitted shortcode attribute the user may use and override the template defaults as needed.
				add_filter( 'cn_list_atts_permitted-' . $template->getSlug() , array( __CLASS__, 'initShortcodeAtts') );
				add_filter( 'cn_list_atts-' . $template->getSlug() , array( __CLASS__, 'initTemplateOptions') );
			}
		}

		/**
		 * Enqueue the template's CSS file.
		 *
		 * @access private
		 * @since 3.0
		 * @uses wp_enqueue_style()
		 * @return (void)
		 */
		public static function enqueueCSS() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'cnt_' . self::$template->getSlug() . '-css', self::$template->getURL() . "tile$min.css", array( 'connections-user' ), self::$template->getVersion() );
		}

		/**
		 * Enqueue the template's JS file.
		 *
		 * @access private
		 * @since 3.0
		 * @uses wp_enqueue_script()
		 * @return (void)
		 */
		// public static function enqueueJS() {

		// 	// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
		// 	$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		// 	wp_enqueue_script( 'cnt_' . self::$template->getSlug() . '-js' , self::$template->getURL() . "tile$min.js", array( 'jquery-chosen-min' ), self::$template->getVersion(), TRUE );
		// }


		/**
		 * Initiate the permitted template shortcode options and load the default values.
		 *
		 * @access private
		 * @since 3.0
		 * @param  (array)  $permittedAtts The shortcode $atts array.
		 * @return (array)
		 */
		public static function initShortcodeAtts( $permittedAtts = array() ) {
			global $connections;

			$addressLabel = $connections->options->getDefaultAddressValues();
			$phoneLabel   = $connections->options->getDefaultPhoneNumberValues();
			$emailLabel   = $connections->options->getDefaultEmailValues();

			$permittedAtts['card_width']                      = 240;
			$permittedAtts['card_height']                     = 150;

			$permittedAtts['enable_bio']                      = TRUE;
			$permittedAtts['enable_bio_head']                 = TRUE;
			$permittedAtts['enable_note']                     = TRUE;
			$permittedAtts['enable_note_head']                = TRUE;
			$permittedAtts['enable_website_link']             = TRUE;

			$permittedAtts['show_image']                      = TRUE;
			$permittedAtts['show_title']                      = TRUE;
			$permittedAtts['show_org']                        = TRUE;
			$permittedAtts['show_contact_name']               = TRUE;
			$permittedAtts['show_family']                     = FALSE;
			$permittedAtts['show_addresses']                  = FALSE;
			$permittedAtts['show_phone_numbers']              = TRUE;
			$permittedAtts['show_email']                      = TRUE;
			$permittedAtts['show_im']                         = FALSE;
			$permittedAtts['show_social_media']               = FALSE;
			$permittedAtts['show_dates']                      = FALSE;
			$permittedAtts['show_links']                      = FALSE;

			$permittedAtts['address_types']                   = NULL;
			$permittedAtts['phone_types']                     = NULL;
			$permittedAtts['email_types']                     = NULL;

			$permittedAtts['image']                           = 'photo';
			$permittedAtts['image_width']                     = 100;
			$permittedAtts['image_height']                    = 130;
			$permittedAtts['image_fallback']                  = 'block';

			// $permittedAtts['str_select']                      = 'Select Category';
			// $permittedAtts['str_select_all']                  = 'Show All Categories';
			$permittedAtts['str_image']                       = 'No Photo Available';
			$permittedAtts['str_bio_head']                    = 'Biography';
			$permittedAtts['str_note_head']                   = 'Notes';
			$permittedAtts['str_contact']                     = 'Contact';
			$permittedAtts['str_home_addr']                   = $addressLabel['home'];
			$permittedAtts['str_work_addr']                   = $addressLabel['work'];
			$permittedAtts['str_school_addr']                 = $addressLabel['school'];
			$permittedAtts['str_other_addr']                  = $addressLabel['other'];
			$permittedAtts['str_home_phone']                  = $phoneLabel['homephone'];
			$permittedAtts['str_home_fax']                    = $phoneLabel['homefax'];
			$permittedAtts['str_cell_phone']                  = $phoneLabel['cellphone'];
			$permittedAtts['str_work_phone']                  = $phoneLabel['workphone'];
			$permittedAtts['str_work_fax']                    = $phoneLabel['workfax'];
			$permittedAtts['str_personal_email']              = $emailLabel['personal'];
			$permittedAtts['str_work_email']                  = $emailLabel['work'];
			$permittedAtts['str_visit_website']               = 'Visit Website';

			$permittedAtts['name_format']                     = '%prefix% %first% %middle% %last% %suffix%';
			$permittedAtts['contact_name_format']             = '%label%: %first% %last%';
			$permittedAtts['addr_format']                     = '%label% %line1% %line2% %line3% %city% %state%  %zipcode% %country%';
			$permittedAtts['email_format']                    = '%label%%separator% %address%';
			$permittedAtts['phone_format']                    = '%label%%separator% %number%';
			$permittedAtts['link_format']                     = '%label%%separator% %title%';
			$permittedAtts['date_format']                     = '%label%%separator% %date%';

			// Set this option so we can define template functionality: should we expand details or link to the single entry page.
			$permittedAtts['link']                            = cnSettingsAPI::get( 'connections', 'connections_link', 'name' );

			return $permittedAtts;
		}

		/**
		 * Initiate the template options using the user supplied shortcode option values.
		 *
		 * @access private
		 * @since 3.0
		 * @param  (array)  $atts The shortcode $atts array.
		 * @return (array)
		 */
		public static function initTemplateOptions( $atts ) {
			$convert = new cnFormatting();

			$convert->toBoolean( $atts['enable_website_link'] );
			$convert->toBoolean( $atts['enable_bio'] );
			$convert->toBoolean( $atts['enable_bio_head']);
			$convert->toBoolean( $atts['enable_note'] );
			$convert->toBoolean( $atts['enable_note_head'] );

			$convert->toBoolean( $atts['show_title'] );
			$convert->toBoolean( $atts['show_org'] );
			$convert->toBoolean( $atts['show_family'] );
			$convert->toBoolean( $atts['show_addresses'] );
			$convert->toBoolean( $atts['show_phone_numbers'] );
			$convert->toBoolean( $atts['show_email'] );
			$convert->toBoolean( $atts['show_im'] );
			$convert->toBoolean( $atts['show_social_media'] );
			$convert->toBoolean( $atts['show_dates'] );
			$convert->toBoolean( $atts['show_links'] );

			$convert->toBoolean( $atts['link'] );

			add_filter( 'cn_phone_number' , array( __CLASS__, 'phoneLabels') );
			add_filter( 'cn_email_address' , array( __CLASS__, 'emailLabels') );
			add_filter( 'cn_address' , array( __CLASS__, 'addressLabels') );

			self::$atts = $atts;

			return $atts;
		}

		/**
		 * Alter the Address Labels.
		 *
		 * @access private
		 * @since 3.0
		 * @param (object) $data
		 * @return (object)
		 */
		public static function addressLabels( $data ) {

			switch ( $data->type ) {

				case 'home':
					$data->name = self::$atts['str_home_addr'];
					break;
				case 'work':
					$data->name = self::$atts['str_work_addr'];
					break;
				case 'school':
					$data->name = self::$atts['str_school_addr'];
					break;
				case 'other':
					$data->name = self::$atts['str_other_addr'];
					break;
			}

			return $data;
		}

		/**
		 * Alter the Phone Labels.
		 *
		 * @access private
		 * @since 3.0
		 * @param (object) $data
		 * @return (object)
		 */
		public static function phoneLabels( $data ) {

			switch ( $data->type ) {

				case 'homephone':
					$data->name = self::$atts['str_home_phone'];
					break;
				case 'homefax':
					$data->name = self::$atts['str_home_fax'];
					break;
				case 'cellphone':
					$data->name = self::$atts['str_cell_phone'];
					break;
				case 'workphone':
					$data->name = self::$atts['str_work_phone'];
					break;
				case 'workfax':
					$data->name = self::$atts['str_work_fax'];
					break;
			}

			return $data;
		}

		/**
		 * Alter the Email Labels.
		 *
		 * @access private
		 * @since 3.0
		 * @param (object) $data
		 * @return (object)
		 */
		public static function emailLabels( $data ) {

			switch ( $data->type ) {

				case 'personal':
					$data->name = self::$atts['str_personal_email'];
					break;
				case 'work':
					$data->name = self::$atts['str_work_email'];
					break;

				default:
					$data->name = 'Email';
				break;
			}

			return $data;
		}

	}

	/**
	 * Start up the template class.
	 *
	 * @access public
	 * @since 3.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Tile() {

		if ( class_exists('connectionsLoad') ) {

			new CN_Tile();

		} else {

			add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Tile.</p></div>\';'
					)
			);

			return FALSE;
		}
	}

	/**
	 * Start the template.
	 *
	 * Since Connections loads at default priority 10, and this template is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Tile', 11 );
}