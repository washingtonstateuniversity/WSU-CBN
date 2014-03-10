<?php
/*
Plugin Name: Connections Excerpt Plus - Template
Plugin URI: http://www.connections-pro.com
Description: Connections Excerpt Plus - Template
Version: 1.0.2
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

if ( ! class_exists( 'CN_Excerpt_Plus' ) ) {

	class CN_Excerpt_Plus {

		public function __construct() {

			$atts = array(
				'class'       => 'CNT_Excerpt_Plus',
				'name'        => 'Excerpt Plus',
				'type'        => 'all',
				'version'     => '1.0.2',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Shows the Entry\'s name and bio excerpt with a read more link which when clicked will reveal the remainder of the bio.'
				);

			cnTemplateFactory::register( $atts );
		}

	}

	class CNT_Excerpt_Plus {

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
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueueCSS' ) );

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

			wp_enqueue_style( 'cnt_' . self::$template->getSlug() . '-css', self::$url . "excerpt-plus$min.css", array(), self::$template->getVersion() );
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

			wp_enqueue_script( 'cnt_' . self::$template->getSlug() . '-js' , self::$url . "excerpt-plus$min.js", array( 'jquery-chosen-min' ), self::$template->getVersion(), TRUE );
		}


		/**
		 * Initiate the permitted template shortcode options and load the default values.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $permittedAtts The shortcode $atts array.
		 * @return (array)
		 */
		public static function initShortcodeAtts( $permittedAtts = array() ) {
			global $connections;

			$addressLabel = $connections->options->getDefaultAddressValues();
			$phoneLabel   = $connections->options->getDefaultPhoneNumberValues();
			$emailLabel   = $connections->options->getDefaultEmailValues();

			$permittedAtts['enable_search']                   = TRUE;

			$permittedAtts['enable_pagination']               = TRUE;
			$permittedAtts['page_limit']                      = 20;
			$permittedAtts['pagination_position']             = 'after';

			$permittedAtts['enable_category_select']          = TRUE;
			$permittedAtts['show_empty_categories']           = TRUE;
			$permittedAtts['show_category_count']             = FALSE;
			$permittedAtts['category_select_position']        = 'before';
			$permittedAtts['enable_category_by_root_parent']  = FALSE;
			$permittedAtts['enable_category_multi_select']    = FALSE;
			$permittedAtts['enable_category_group_by_parent'] = FALSE;

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

			$permittedAtts['str_select']                      = 'Select Category';
			$permittedAtts['str_select_all']                  = 'Show All Categories';
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
			$convert->toBoolean( $atts['enable_search'] );
			$convert->toBoolean( $atts['enable_pagination'] );
			$convert->toBoolean( $atts['enable_category_select'] );
			$convert->toBoolean( $atts['show_empty_categories'] );
			$convert->toBoolean( $atts['show_category_count'] );
			$convert->toBoolean( $atts['enable_category_by_root_parent'] );
			$convert->toBoolean( $atts['enable_category_multi_select'] );
			$convert->toBoolean( $atts['enable_category_group_by_parent'] );
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

			// If displaying a signle entry, no need to display category select, search and pagination.
			if ( get_query_var( 'cn-entry-slug' ) ) {
				$atts['enable_search']          = FALSE;
				$atts['enable_pagination']      = FALSE;
				$atts['enable_category_select'] = FALSE;
			}

			add_filter( 'cn_phone_number' , array( __CLASS__, 'phoneLabels') );
			add_filter( 'cn_email_address' , array( __CLASS__, 'emailLabels') );
			add_filter( 'cn_address' , array( __CLASS__, 'addressLabels') );

			add_filter( 'cn_excerpt_length', array( __CLASS__, 'excerptLength') );

			// Start the form.
			add_action( 'cn_action_list_before-' . self::$template->getSlug() , array( __CLASS__, 'formOpen'), -1 );

			// If search is enabled, add the appropiate filters.
			if ( $atts['enable_search'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'limitList'), 10 );
				add_action( 'cn_action_list_before-' . self::$template->getSlug() , array( __CLASS__, 'searchForm') , 1 );
			}

			// If pagination is enabled add the appropiate filters.
			if ( $atts['enable_pagination'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'limitList'), 10 );
				add_action( 'cn_action_list_' . $atts['pagination_position'] . '-' . self::$template->getSlug() , array( __CLASS__, 'listPages') );
			}

			// If the category select/filter feature is enabled, add the appropiate filters.
			if ( $atts['enable_category_select'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'setCategory') );
				add_action( 'cn_action_list_' . $atts['category_select_position'] . '-' . self::$template->getSlug() , array( __CLASS__, 'categorySelect') , 5 );
			}

			// Close the form
			add_action( 'cn_action_list_after-' . self::$template->getSlug() , array( __CLASS__, 'formClose'), 11 );

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
		 * Limit the returned results.
		 *
		 * @access private
		 * @since 1.0
		 * @param (array) $atts The shortcode $atts array.
		 * @return (array)
		 */
		public static function limitList( $atts ) {

			// $atts['limit'] = $this->pageLimit; // Page Limit
			$atts['limit'] = empty( $atts['limit'] ) ? $atts['page_limit'] : $atts['limit'];

			return $atts;
		}

		/**
		 * Echo the form beginning.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		 */
		public static function formOpen() {
		    global $post, $wp_rewrite;

			$permalink = get_permalink();

			if ( $wp_rewrite->using_permalinks() ) {

				echo '<form class="cn-form" action="" method="get">';
				if ( is_front_page() ) echo '<input type="hidden" name="page_id" value="' . $post->ID .'">';

			} else {

				echo '<form class="cn-form" method="get">';
				echo '<input type="hidden" name="' . ( is_page() ? 'page_id' : 'p' ) . '" value="' . $post->ID .'">';
			}
		}

		/**
		 * Echo the form ending.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		 */
		public static function formClose() {
		    echo '</form>';
		}

		/**
		 * Output the search input fields.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		 */
		public static function searchForm() {

			cnTemplatePart::search();
		}

		/**
		 * Output the pagination control.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		 */
		public static function listPages() {

			cnTemplatePart::pagination( array( 'limit' => self::$atts['page_limit'] ) );

		}

		/**
		 * Outputs the category select list.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		 */
		public static function categorySelect() {

			$atts = array(
				'default'    => self::$atts['str_select'],
				'select_all' => self::$atts['str_select_all'],
				'type'       => self::$atts['enable_category_multi_select'] ? 'multiselect' : 'select',
				'group'      => self::$atts['enable_category_group_by_parent'],
				'show_count' => self::$atts['show_category_count'],
				'show_empty' => self::$atts['show_empty_categories'],
				'parent_id'  => self::$atts['enable_category_by_root_parent'] ? self::$atts['category'] : array(),
				);

			cnTemplatePart::category( $atts );
		}

		/**
		 * Alters the shortcode attribute values before the query is processed.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $atts The shortcode $atts array.
		 * @return (array)
		 */
		public static function setCategory( $atts ) {

			if ( $atts['enable_category_multi_select'] ) {

				if ( get_query_var('cn-cat') ) $atts['category_in'] = get_query_var('cn-cat');

			} else {

				if ( get_query_var('cn-cat') ) $atts['category'] = get_query_var('cn-cat');
			}

			return $atts;
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
	function Connections_Excerpt_Plus() {

		if ( class_exists('connectionsLoad') ) {

			new CN_Excerpt_Plus();

		} else {

			add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Slim.</p></div>\';'
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
	add_action( 'plugins_loaded', 'Connections_Excerpt_Plus', 11 );
}