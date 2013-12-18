<?php
/*
Plugin Name: Connections ROT13
Plugin URI: 
Description: 
Version: 0.1
Author: 
Author URI: 

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

if (!class_exists('connectionsExpSearchLoad')) {
	class connectionsExpSearchLoad {
		
		public function __construct() {
			//if ( !is_admin() ) add_action( 'plugins_loaded', array(&$this, 'start') );
			//if ( !is_admin() ) add_action( 'wp_print_scripts', array(&$this, 'loadScripts') );
		}
		
		public function start() {
			define('CNROT13_CURRENT_VERSION', '0.1');
			define('CNROT13_ANCHOR_PATTERN', '/(<a.*?mailto)(.*?)(<\/a>)/i');
			define('CNROT13_EMAIL_PATTERN', '/(<a.*?mailto:.*?>)(.*?)(<\/a>)/i');
			
			define('CN_ROT13_BASE_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__)));
			define('CN_ROT13_BASE_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__)));
			
			//add_filter('cn_email_address', array(&$this, 'applyROT13'));
			//add_filter('cn_output_email_addresses', array(&$this, 'outputROT13'));
			add_filter('cn_list_atts_permitted', array(__CLASS__, 'media_upload_callback'));
			
		}
		
		
		/**
		 * Loads the Connections javascripts on the WordPress frontend.
		 */
		public function loadScripts() {

		}

	}
	
	/*
	 * Checks for PHP 5 or greater as required by Connections Pro and display an error message
	 * rather that havinh PHP thru an error.
	 */
	if (version_compare(PHP_VERSION, '5.0.0', '>')) {
		/*
		 * Initiate the plug-in.
		 */
		global $connectionsExpSearch;
		$connectionsExpSearch = new connectionsExpSearchLoad();
	} else {
		add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>Connections ROT13 requires at least PHP5. You are using version: ' . PHP_VERSION . '</strong></p></div>\';') );
	}
	
}