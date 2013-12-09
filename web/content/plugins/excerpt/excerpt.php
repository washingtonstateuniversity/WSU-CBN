<?php
/*
Plugin Name: Connections Excerpt - Template
Plugin URI: http://www.connections-pro.com
Description: Connections Excerpt - Template
Version: 2.0.1
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

if ( ! class_exists( 'CN_Excerpt' ) ) {

	class CN_Excerpt {

		public function __construct() {

			$atts = array(
				'class'       => 'CNT_Excerpt',
				'name'        => 'Excerpt',
				'type'        => 'all',
				'version'     => '2.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Shows the Entry\'s name and bio excerpt with a read more link which when clicked will reveal the remainder of the bio.'
				);

			cnTemplateFactory::register( $atts );
		}

	}

	class CNT_Excerpt {

		/**
		 * Stores a copy of the shortcode $atts for use throughout the class.
		 *
		 * @access private
		 * @since 2.0
		 * @var (array)
		 */
		private static $atts;

		/**
		 * Stores an initialized instance of cnTemplate.
		 *
		 * @access private
		 * @since 2.0
		 * @var (object)
		 */
		private static $template;

		/**
		 * The URL to the template files.
		 *
		 * @access private
		 * @since 2.0
		 * @var (string)
		 */
		private static $url;

		/**
		 * Setup the template.
		 *
		 * @access public
		 * @since 2.0
		 * @param (object) $template An initialized instance of the cnTemplate class.
		 * @return (void)
		 */
		public function __construct( $template ) {

			self::$template = $template;

			// $this->dirName = plugin_basename( dirname( __FILE__ ) );
			// $this->baseName = plugin_basename( __FILE__ );
			$path = plugin_dir_path( __FILE__ );
			self::$url = plugin_dir_url( __FILE__ );

			$template->part( array( 'tag' => 'card', 'type' => 'file', 'path' => $path . 'card.php' ) );
			$template->part( array( 'tag' => 'card_single', 'type' => 'file', 'path' => $path . 'card-single.php' ) );
			$template->part( array( 'tag' => 'js', 'type' => 'action', 'callback' => array( __CLASS__, 'enqueueJS' ) ) );

			if ( ! is_admin() ) {

				// Enqueue the frontend scripts and CSS.
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueCSS' ) );

				//Update the permitted shortcode attribute the user may use and override the template defaults as needed.
				add_filter( 'cn_list_atts_permitted-' . $template->getSlug() , array( __CLASS__, 'initShortcodeAtts') );
				add_filter( 'cn_list_atts-' . $template->getSlug() , array( __CLASS__, 'initTemplateOptions') );
			}
		}

		/**
		 * Enqueue the template's CSS file.
		 *
		 * @access private
		 * @since 1.0
		 * @uses wp_enqueue_style()
		 * @return (void)
		 */
		public static function enqueueCSS() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'cnt_' . self::$template->getSlug() . '-css', self::$url . "excerpt$min.css", array(), self::$template->getVersion() );
		}

		/**
		 * Enqueue the template's JS file.
		 *
		 * @access private
		 * @since 1.0
		 * @uses wp_enqueue_script()
		 * @return (void)
		 */
		public static function enqueueJS() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'cnt_' . self::$template->getSlug() . '-js' , self::$url . "excerpt$min.js", array( 'jquery-chosen-min' ), self::$template->getVersion(), TRUE );
		}


		/**
		 * Initiate the permitted template shortcode options and load the default values.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $permittedAtts The shortcode $atts array.
		 * @return (array)
		 */
		public function initShortcodeAtts( $permittedAtts = array() ) {
			global $connections;

			$addressLabel = $connections->options->getDefaultAddressValues();
			$phoneLabel   = $connections->options->getDefaultPhoneNumberValues();
			$emailLabel   = $connections->options->getDefaultEmailValues();

			$permittedAtts['excerpt_length']                  = 55;

			$permittedAtts['enable_bio']                      = TRUE;
			$permittedAtts['enable_bio_head']                 = FALSE;
			$permittedAtts['enable_note']                     = FALSE;
			$permittedAtts['enable_note_head']                = FALSE;

			$permittedAtts['show_title']                      = FALSE;
			$permittedAtts['show_org']                        = FALSE;
			$permittedAtts['show_contact_name']               = FALSE;
			$permittedAtts['show_family']                     = FALSE;
			$permittedAtts['show_addresses']                  = FALSE;
			$permittedAtts['show_phone_numbers']              = FALSE;
			$permittedAtts['show_email']                      = FALSE;
			$permittedAtts['show_im']                         = FALSE;
			$permittedAtts['show_social_media']               = FALSE;
			$permittedAtts['show_dates']                      = FALSE;
			$permittedAtts['show_links']                      = FALSE;

			$permittedAtts['address_types']                   = NULL;
			$permittedAtts['phone_types']                     = NULL;
			$permittedAtts['email_types']                     = NULL;

			$permittedAtts['image']                           = 'photo';
			$permittedAtts['image_fallback']                  = 'block';

			$permittedAtts['str_image']                       = 'No Photo Available';
			$permittedAtts['str_read_more']                   = 'Read More';
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

			$permittedAtts['name_format']                     = '%prefix% %first% %middle% %last% %suffix%';
			$permittedAtts['contact_name_format']             = '%label%: %first% %last%';
			$permittedAtts['addr_format']                     = '%label% %line1% %line2% %line3% %city% %state%  %zipcode% %country%';
			$permittedAtts['email_format']                    = '%label%%separator% %address%';
			$permittedAtts['phone_format']                    = '%label%%separator% %number%';
			$permittedAtts['link_format']                     = '%label%%separator% %title%';
			$permittedAtts['date_format']                     = '%label%%separator% %date%';

			$permittedAtts['color']                           = '#00508D';

			// Set this option so we can define template functionality: should we expand details or link to the single entry page.
			$permittedAtts['link']                            = cnSettingsAPI::get( 'connections', 'connections_link', 'name' );

			return $permittedAtts;
		}

		/**
		 * Initiate the template options using the user supplied shortcode option values.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $atts The shortcode $atts array.
		 * @return (array)
		 */
		public static function initTemplateOptions( $atts ) {
			$convert = new cnFormatting();

			// Because the shortcode option values are treated as strings some of the values have to converted to boolean.
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

			add_filter( 'cn_excerpt_length', array( __CLASS__, 'excerptLength') );

			// Store a copy of the shortcode $atts to be used in other class methods.
			self::$atts = $atts;

			return $atts;
		}

		/**
		 * Alter the Address Labels.
		 *
		 * @access private
		 * @since 1.0
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
		 * @since 1.0
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
		 * @since 1.0
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

		/**
		 * The excerpt length.
		 *
		 * @access private
		 * @since 1.0.2
		 * @param  (int)  $atts The number of words the excerpt.
		 * @return (int)
		 */
		public static function excerptLength( $length ) {

			return absint( self::$atts['excerpt_length'] );
		}

	}

	/**
	 * Start up the template class.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Excerpt() {

		if ( class_exists('connectionsLoad') ) {

			new CN_Excerpt();

		} else {

			add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Excerpt.</p></div>\';'
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
	add_action( 'plugins_loaded', 'Connections_Excerpt', 11 );
}
