<?php
/*
Plugin Name: Connections SiteShot
Plugin URI: http://connections-pro.com/
Description: Adds a shortcode option to premium templates to show a site shot of a website.
Version: 1.0.2
Author: Steven A. Zahm
Author URI: http://connections-pro.com/
Text Domain: connections_ss
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2009  Steven A. Zahm  (email : helpdesk@connections-pro.com)
*/

if ( ! class_exists( 'CN_SiteShot' ) ) {

	class CN_SiteShot {

		/**
		* @var (object) Instance of this class.
		*/
		private static $instance;

		/**
		 * A dummy constructor to prevent class from being loaded more than once.
		 *
		 * @access private
		 * @since 1.0
		 * @see CN_SiteShot::instance()
		 * @see CN_SiteShot();
		 */
		private function __construct() { /* Do nothing here */ }

		/**
		 * Insures that only one instance exists at any one time.
		 *
		 * @access public
		 * @since 1.0
		 * @return object CN_SiteShot
		 */
		public static function getInstance() {

			if ( ! isset( self::$instance ) ) {

				self::$instance = new self;
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Initiate the plugin.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function init() {

			// Update the permitted shortcode attribute the user may use and override the template defaults as needed.
			// add_filter( 'cn_list_atts_permitted' , array( __CLASS__, 'initShortcodeAtts') );
			add_filter( 'cn_list_atts' , array( __CLASS__, 'initTemplateOptions'), 20 ); // Set priority 20 so template filters run first.

			add_filter( 'cn_output_image', array( __CLASS__, 'siteshot' ), 10, 3 );

			add_shortcode( 'siteshot', array( __CLASS__, 'shortcode' ) );

			/*
			 * Register the TinyMCE button and plugin only if the current user can edit pages/posts and has the RTE enabled.
			 */
			if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option('rich_editing') == 'true' ) {

				add_filter( 'mce_buttons', array( __CLASS__, 'addTinyMCEButton' ) );
				add_filter( 'mce_external_plugins', array( __CLASS__, 'registerTinyMCEPlugin' ) );
			}

		}

		/**
		 * Initiate the permitted shortcode option and load the default values.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $permittedAtts The shortcode $atts array.
		 * @return (array)
		 */
		public function initShortcodeAtts( $permittedAtts = array() ) {

			return $permittedAtts;
		}

		/**
		 * Initiate the options using the user supplied shortcode option values.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array)  $atts The shortcode $atts array.
		 * @return (array)
		 */
		public static function initTemplateOptions( $atts ) {

			if ( empty( $atts['image_width'] ) ) $atts['image_width'] = 225;
			if ( empty( $atts['image_height'] ) ) $atts['image_height'] = 150;

			if ( isset( $atts['image'] ) &&  $atts['image'] == 'siteshot' ) {

				$atts['str_image'] = __( 'No Site Shot Available', 'connections_ss' );
				// $atts['image_fallback'] = 'none';
			}


			return $atts;
		}

		/**
		 * Hooks into cnOutput::getImage() to add support for SiteShot.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (string) $out   The HTML output of cnOutput::getImage().
		 * @param  (array)  $atts  The $atts passed from cnOutput::getImage().
		 * @param  (object) $entry An instance of cnOutput.
		 * @return (string)
		 */
		public static function siteshot( $out, $atts, $entry ) {

			if ( $atts['image'] == 'siteshot' ) {

				/*
				 * Create the link for the image if one was assigned.
				 */
				$links = $entry->getLinks( array( 'type' => 'website' ) );

				if ( empty( $links ) ) return $out;

				$link = $links[0];

				// Create the query the WordPress for the webshot to be displayed.
				$imageURI = 'http://s.wordpress.com/mshots/v1/' . urlencode( $link->url ) . '?w=' . $atts['width'];
				$image    = '<img class="screenshot" alt="' . esc_attr( $link->url ) . '" style="width: ' . $atts['width'] . 'px" src="' . $imageURI . '" />';

				$out = '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image">';

				$out .= sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>',
					esc_url( $link->url ),
					empty( $link->target ) ? '' : ' target="' . $link->target . '"',
					empty( $link->followString ) ? '' : ' rel="' . $link->followString . '"',
					$image
				);

				$out .= '</span></span>';

			}

			return $out;
		}

		/**
		 * The SiteShot shortcode.
		 *
		 * @access public
		 * @since 1.0
		 * @param  (array)  $atts
		 * @param  (string) $content
		 * @param  (string) $tag
		 * @return (string)
		 */
		public static function shortcode( $atts, $content = '', $tag = 'connections_ss' ) {
			$html = '';

			$defaults = array(
				'alt'   => '',
				'title' => '',
				'url'   => 'http://connections-pro.com',
				'width' => 600
				);

			$atts = shortcode_atts( $defaults , $atts, $tag ) ;

			if ( empty( $atts['url'] ) ) return $html;

			$url   = esc_url( $atts['url'] );
			$alt   = empty( $atts['alt'] ) ? $url : esc_attr( $atts['alt'] );
			$title = empty( $atts['title'] ) ? $url : esc_attr( $atts['title'] );
			$width = absint( $atts['width'] );

			$imageURI = sprintf( 'http://s.wordpress.com/mshots/v1/%1$s?w=%2$d', urlencode( $url ), $width );
			$image    = sprintf( '<img src="%1$s" alt="%2$s" title="%3$s" width="%4$d"/>', $imageURI, $alt, $title, $width );
			$html     = sprintF( '<div class="siteshot"><a href="%1$s">%2$s</a></div>', $url, $image );

			return $html;
		}

		/**
		 * Add the SiteShot button to TincyMCE.
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array) $buttons
		 * @return (array)
		 */
		public static function addTinyMCEButton( $buttons ) {

			array_push( $buttons, "|", "siteshot" );

			return $buttons;
		}

		/**
		 * Register the SiteShot plugin for TincyMCE
		 *
		 * @access private
		 * @since 1.0
		 * @param  (array) $plugin_array
		 * @return (array)
		 */
		public static function registerTinyMCEPlugin( $plugin_array ) {

			$plugin_array['siteshot'] = plugins_url( 'assets/js/siteshot.js' , __FILE__ );

			return $plugin_array;
		}

	}

	/**
	 * Start up the class.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed object | bool
	 */
	function Connections_SS() {

		if ( class_exists('connectionsLoad') ) {

			return CN_SiteShot::getInstance();

		} else {

			add_action(
				'admin_notices',
				 create_function(
					'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections SiteShot.</p></div>\';'
					)
			);

			return FALSE;
		}
	}

	/**
	 * Start the plugin.
	 *
	 * Since Connections loads at default priority 10, and this add-on is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_SS', 11 );
}