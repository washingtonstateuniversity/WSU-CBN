<?php
/*
Plugin Name: Connections Market - Template
Plugin URI: http://www.connections-pro.com
Description: Connections Market - Template
Version: 3.0.1
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

if ( ! class_exists( 'CN_Market' ) ) {

	class CN_Market {

		public function __construct() {

			$atts = array(
				'class'       => 'CNT_Market',
				'name'        => 'Market',
				'type'        => 'all',
				'version'     => '3.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Features a category select sidebar, Bio/Note and Google Map expandable trays.',
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png',
				);

			cnTemplateFactory::register( $atts );
		}

	}

	class CNT_Market {

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
			$template->part( array( 'tag' => 'js', 'type' => 'action', 'callback' => array( __CLASS__, 'enqueueJS' ) ) );

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

			wp_enqueue_style( 'cnt_' . self::$template->getSlug() . '-css', self::$template->getURL() . "market$min.css", array(), self::$template->getVersion() );

			// Only print this CSS inline if displaying a single entry.
			if ( get_query_var( 'cn-entry-slug' ) )
				wp_enqueue_style( 'cnt_' . self::$template->getSlug() . '_card_single-css', self::$template->getURL() . 'card-single.css', array( 'cnt_' . self::$template->getSlug() . '-css' ), self::$template->getVersion() );

		}

		/**
		 * Enqueue the template's JS file.
		 *
		 * @access private
		 * @since 3.0
		 * @uses wp_enqueue_script()
		 * @return (void)
		 */
		public static function enqueueJS() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'cnt_' . self::$template->getSlug() . '-js' , self::$template->getURL() . "market$min.js", array( 'jquery-chosen-min' ), self::$template->getVersion(), TRUE );
			wp_enqueue_script( 'jquery-gomap-min' );
		}


		/**
		 * Initiate the permitted template shortcode options and load the default values.
		 *
		 * @access private
		 * @since 3.0
		 * @param  (array)  $permittedAtts The shortcode $atts array.
		 * @return (array)
		 */
		public function initShortcodeAtts( $permittedAtts = array() ) {
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
			$permittedAtts['enable_category_by_root_parent']  = FALSE;

			$permittedAtts['enable_map']                      = TRUE;
			$permittedAtts['enable_bio']                      = TRUE;
			$permittedAtts['enable_bio_head']                 = TRUE;
			$permittedAtts['enable_note']                     = TRUE;
			$permittedAtts['enable_note_head']                = TRUE;

			$permittedAtts['show_title']                      = TRUE;
			$permittedAtts['show_org']                        = TRUE;
			$permittedAtts['show_contact_name']               = TRUE;
			$permittedAtts['show_family']                     = TRUE;
			$permittedAtts['show_addresses']                  = TRUE;
			$permittedAtts['show_phone_numbers']              = TRUE;
			$permittedAtts['show_email']                      = TRUE;
			$permittedAtts['show_im']                         = TRUE;
			$permittedAtts['show_social_media']               = TRUE;
			$permittedAtts['show_dates']                      = TRUE;
			$permittedAtts['show_links']                      = TRUE;

			$permittedAtts['address_types']                   = NULL;
			$permittedAtts['phone_types']                     = NULL;
			$permittedAtts['email_types']                     = NULL;

			$permittedAtts['image']                           = 'logo';
			$permittedAtts['image_width']                     = NULL;
			$permittedAtts['image_height']                    = NULL;
			$permittedAtts['image_fallback']                  = 'block';
			$permittedAtts['tray_image']                      = 'photo';
			$permittedAtts['tray_image_width']                = NULL;
			$permittedAtts['tray_image_height']               = NULL;
			$permittedAtts['tray_image_fallback']             = 'none';

			$permittedAtts['map_type']                        = 'm';
			$permittedAtts['map_zoom']                        = 13;
			$permittedAtts['map_frame_height']                = 400;

			$permittedAtts['card_width']                      = 440;
			$permittedAtts['category_width']                  = 175;

			$permittedAtts['str_select']                      = 'Show All Categories';
			$permittedAtts['str_image']                       = 'No Logo Available';
			$permittedAtts['str_tray_image']                  = 'No Photo Available';
			$permittedAtts['str_map_show']                    = 'Show Map';
			$permittedAtts['str_map_hide']                    = 'Close Map';
			$permittedAtts['str_bio_head']                    = 'Biography';
			$permittedAtts['str_bio_show']                    = 'Show Bio';
			$permittedAtts['str_bio_hide']                    = 'Close Bio';
			$permittedAtts['str_note_head']                   = 'Notes';
			$permittedAtts['str_note_show']                   = 'Show Notes';
			$permittedAtts['str_note_hide']                   = 'Close Notes';
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

			// Because the shortcode option values are treated as strings some of the values have to converted to boolean.
			$convert->toBoolean( $atts['enable_search'] );
			$convert->toBoolean( $atts['enable_pagination'] );
			$convert->toBoolean( $atts['enable_category_select'] );
			$convert->toBoolean( $atts['show_empty_categories'] );
			$convert->toBoolean( $atts['show_category_count'] );
			$convert->toBoolean( $atts['enable_category_by_root_parent'] );
			$convert->toBoolean( $atts['enable_map'] );
			$convert->toBoolean( $atts['enable_bio'] );
			$convert->toBoolean( $atts['enable_bio_head']);
			$convert->toBoolean( $atts['enable_note'] );
			$convert->toBoolean( $atts['enable_note_head'] );

			$convert->toBoolean( $atts['show_title'] );
			$convert->toBoolean( $atts['show_org'] );
			$convert->toBoolean( $atts['show_contact_name'] );
			$convert->toBoolean( $atts['show_family'] );
			$convert->toBoolean( $atts['show_addresses'] );
			$convert->toBoolean( $atts['show_phone_numbers'] );
			$convert->toBoolean( $atts['show_email'] );
			$convert->toBoolean( $atts['show_im'] );
			$convert->toBoolean( $atts['show_social_media'] );
			$convert->toBoolean( $atts['show_dates'] );
			$convert->toBoolean( $atts['show_links'] );

			$convert->toBoolean( $atts['link'] );

			$atts['category_width'] = $atts['enable_category_select'] ? $atts['category_width'] : 0;
			$atts['width'] = $atts['card_width']  + $atts['category_width'] + 35;

			// If displaying a single entry, use the full width.
			if ( get_query_var( 'cn-entry-slug' ) ) {
				$atts['width'] = NULL;
				$atts['map_frame_width'] = NULL;
			} else {
				$atts['map_frame_width'] = $atts['card_width'] - 14;
			}

			// If displaying a signle entry, no need to display category select, search and pagination.
			if ( get_query_var( 'cn-entry-slug' ) ) {
				$atts['enable_search']          = FALSE;
				$atts['enable_pagination']      = FALSE;
				$atts['enable_category_select'] = FALSE;
			}

			add_filter( 'cn_phone_number' , array( __CLASS__, 'phoneLabels') );
			add_filter( 'cn_email_address' , array( __CLASS__, 'emailLabels') );
			add_filter( 'cn_address' , array( __CLASS__, 'addressLabels') );

			// Start the form.
			add_action( 'cn_action_list_before-' . self::$template->getSlug() , array( __CLASS__, 'formOpen'), -1 );

			// If search is enabled, add the appropiate filters.
			if ( $atts['enable_search'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'limitList'), 10 );
				add_action( 'cn_action_list_before-' . self::$template->getSlug() , array( __CLASS__, 'searchForm') , 11 );
			}

			// If pagination is enabled add the appropiate filters.
			if ( $atts['enable_pagination'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'limitList'), 10 );
				add_action( 'cn_action_list_' . $atts['pagination_position'] . '-' . self::$template->getSlug() , array( __CLASS__, 'listPages'), 3 );
			}

			// If the category select/filter feature is enabled, add the appropiate filters.
			if ( $atts['enable_category_select'] ) {
				add_filter( 'cn_list_retrieve_atts-' . self::$template->getSlug() , array( __CLASS__, 'setCategory') );
				add_action( 'cn_action_list_before' . '-' . self::$template->getSlug() , array( __CLASS__, 'categorySelect') , 1 );
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

		/**
		 * Limit the returned results.
		 *
		 * @access private
		 * @since 3.0
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
		 * @since 3.0
		 * @return (void)
		 */
		public static function formOpen() {
		    global $post, $wp_rewrite;

			$permalink = get_permalink();

			if ( $wp_rewrite->using_permalinks() ) {

				echo '<form class="cn-form" id="cn-cat-select" action="" method="get">';
				if ( is_front_page() ) echo '<input type="hidden" name="page_id" value="' . $post->ID .'">';

			} else {

				echo '<form class="cn-form" id="cn-cat-select" method="get">';
				echo '<input type="hidden" name="' . ( is_page() ? 'page_id' : 'p' ) . '" value="' . $post->ID .'">';
			}
		}

		/**
		 * Echo the form ending.
		 *
		 * @access private
		 * @since 3.0
		 * @return (void)
		 */
		public static function formClose() {
		    echo '</form>';
		}

		/**
		 * Output the search input fields.
		 *
		 * @access private
		 * @since 3.0
		 * @return (void)
		 */
		public static function searchForm() {

			cnTemplatePart::search();
		}

		/**
		 * Output the pagination control.
		 *
		 * @access private
		 * @since 3.0
		 * @return (void)
		 */
		public static function listPages() {

			cnTemplatePart::pagination( array( 'limit' => self::$atts['page_limit'] ) );

		}

		/**
		 * Outputs the category select list.
		 *
		 * @access private
		 * @since 3.0
		 * @return (void)
		 */
		public static function categorySelect() {
			global $connections;

			$selected = get_query_var('cn-cat') ? esc_attr( $_GET['cn-cat'] ) : '';
			$level    = 0;
			$out      = '';

			$categories = $connections->retrieve->categories();

			$out .= '<ul style="width: ' . self::$atts['category_width'] . 'px">';
			$out .= '<li><label><input type="radio" name="cn-cat" value="" onChange="this.form.submit();" onClick="this.form.submit();" />' . esc_attr( self::$atts['str_select'] ) . '</label></li>';

			foreach ( $categories as $key => $category ) {

				if ( self::$atts['enable_category_by_root_parent'] ) {

					// if ( $connections->limitCategoryTree === TRUE ) {

						if ( ! is_array( self::$atts['category'] ) ) {

							// Trim the space characters if present.
							self::$atts['category'] = str_replace( ' ', '', self::$atts['category'] );

							// Convert to array.
							self::$atts['category'] = explode( ',', self::$atts['category'] );
						}

						if ( ! in_array( $category->term_id, self::$atts['category'] ) ) continue;
					// }
				}

				$out .= self::buildOptionRowHTML( $category, $level, $selected );
			}

			$out .= '</ul>';

			echo $out;
		}

		/**
		 * Returns the options for the category select list.
		 *
		 * @author Steven A. Zahm
		 * @version 3.0
		 * @param object $category
		 * @param integer $level
		 * @param integer $selected
		 * @return string
		 */
		private function buildOptionRowHTML( $category, $level, $selected ) {
			$selectString = NULL;
			$out = '';
			$class = '';

			$pad = str_repeat( '&emsp; ', max (0, $level) );

			if ( $selected == $category->term_id ) {
				$class = ' class="cn-cat-selected" ';
				$selectString = ' CHECKED ';
			}

			$count = self::$atts['show_category_count'] ? ' (' . $category->count . ')' : '';

			if ( ( self::$atts['show_empty_categories'] && empty( $category->count ) ) || ( self::$atts['show_empty_categories'] || ! empty($category->count) ) || ! empty( $category->children ) )  $out .= '<li' . $class . '><label><input type="radio" name="cn-cat" style="margin-left: ' . $level . 'em;" value="' . $category->term_id . '" onChange="this.form.submit();" onClick="this.form.submit();"' . $selectString . ' />' . /*$pad .*/ $category->name . $count . '</label></li>';

			if ( ! empty($category->children) ) {

				foreach ( $category->children as $child ) {
					++$level;
					$out .= self::buildOptionRowHTML( $child, $level, $selected );
					--$level;
				}

			}

			return $out;

		}

		/**
		 * Alters the shortcode attribute values before the query is processed.
		 *
		 * @access private
		 * @since 3.0
		 * @param  (array)  $atts The shortcode $atts array.
		 * @return (array)
		 */
		public function setCategory( $atts ) {

			if ( get_query_var('cn-cat') ) $atts['category'] = get_query_var('cn-cat');

			return $atts;
		}

		public function excerptLength( $length ) {
			return absint( self::$atts['excerpt_length'] );
		}

	}

	/**
	 * Start up the template class.
	 *
	 * @access public
	 * @since 3.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Market() {

		if ( class_exists('connectionsLoad') ) {

			new CN_Market();

		} else {

			add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Market.</p></div>\';'
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
	add_action( 'plugins_loaded', 'Connections_Market', 11 );
}