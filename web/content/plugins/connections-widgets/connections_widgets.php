<?php
/*
Plugin Name: Connections Widgets
Plugin URI: http://www.connections-pro.com
Description: Connections Widgets
Version: 1.2
Author: Steven A. Zahm
Author URI: http://www.connections-pro.com
Text Domain: connections_widgets
Domain Path: /languages

Copyright 2012  Steven A. Zahm  (email : shazahm1@hotmail.com)

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

if ( ! class_exists('connectionsWidgetsLoad') )
{

	class connectionsWidgetsLoad
	{
		public $settings;

		/**
		 * Initiate the plugin.
		 *
		 * @return void
		 */
		public function __construct()
		{
			$this->loadConstants();
			$this->loadDependencies();

			//register_activation_hook( dirname(__FILE__) . '/connections_widgets.php', array(&$this, 'activate') );
			//register_deactivation_hook( dirname(__FILE__) . '/connections_widgets.php', array(&$this, 'deactivate') );

			// Start this plug-in once all other plugins are fully loaded
			add_action( 'plugins_loaded', array(&$this, 'start') );
		}

		/**
		 * Start the plugin.
		 *
		 * @access private
		 * @since 1.0
		 * @uses load_plugin_textdomain
		 * @return void
		 */
		public function start() {

			if ( class_exists('connectionsLoad') ) {

				// Load the translation files.
				load_plugin_textdomain( 'connections_widgets' , FALSE , CNWD_DIR_NAME . '/languages/' );
				//$this->settings = cnSettingsAPI::getInstance();

				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetSearch" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetCategory" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetRecentlyAdded" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetRecentlyModified" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetUpcomingBirthdays" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetTodaysBirthdays" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetUpcomingAnniversaries" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "cnWidgetTodaysAnniversaries" );' ) );

				/*
				 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
				 * Init the registered settings.
				 * NOTE: The init method must be run after registering the tabs, sections and fields.
				 */
				/*add_filter( 'cn_register_settings_tabs' , 'connectionsFormLoad::registerSettingsTab' );
				add_filter( 'cn_register_settings_sections' , 'connectionsFormLoad::registerSettingsSections' );
				add_filter( 'cn_register_settings_fields' , 'connectionsFormLoad::registerSettingsFields' );
				$this->settings->init();*/

				//add_action( 'admin_init' , array(&$this, 'adminInit') );
				//add_action( 'init' , array(&$this, 'init') );
			} else {
				add_action(
				'admin_notices',
				 create_function(
				 	'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order to use its widgets.</p></div>\';'
					)
				);
			}
		}

		/**
		 * Define the constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function loadConstants()
		{
			define('CNWD_CURRENT_VERSION', '1.2');
			define('CNWD_CURRENT_DB_VERSION', '1.0');
			define('CNWD_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define('CNWD_BASE_NAME', plugin_basename( __FILE__ ) );
			define('CNWD_BASE_PATH', WP_PLUGIN_DIR . '/' . CNWD_DIR_NAME);
			define('CNWD_BASE_URL', WP_PLUGIN_URL . '/' . CNWD_DIR_NAME);
		}

		/**
		 * Load the files required for the plugin.
		 *
		 * @return void
		 */
		private function loadDependencies()
		{
			require_once('includes/class.widgets.php');
		}

		/**
		 * Initiate the admin.
		 *
		 * @return void
		 */
		public function adminInit()
		{

		}

		/**
		 * Initiate the frontend.
		 *
		 * @access private
		 * @since 1.0
		 * @uses add_action
		 * @return void
		 */
		public function init()
		{
			add_action( 'wp_print_scripts', array(&$this, 'loadScripts' ) );
			add_action( 'wp_print_styles', array(&$this, 'loadStyles' ) );
		}

		/**
		 * Called when activating Connections Widgets via the activation hook.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public function activate()
		{
			$currentDBVersion = get_option('connections_widgets_db_version');

			if ( $currentDBVersion === FALSE ) {
				add_option('connections_widgets_db_version', CNWD_CURRENT_DB_VERSION);
			} else {
				update_option('connections_widgets_db_version', CNWD_CURRENT_DB_VERSION);
			}
		}

		/**
		 * Called when deactivating Connections Widgets via the deactivation hook.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public function deactivate()
		{

		}

		/**
		 * Called when running the wp_print_scripts action.
		 *
		 * @access private
		 * @since 1.0
		 * @uses wp_enqueue_script()
		 * @uses wp_localize_script()
		 * @return void
		 */
		public function loadScripts()
		{

		}

		/**
		 * Called when running the wp_print_styles action.
		 *
		 * @access private
		 * @since 1.0
		 * @uses wp_enqueue_style()
		 * @return void
		 */
		public function loadStyles()
		{

		}

	}

	/*
	 * Initiate the plug-in.
	 */
	global $connectionsWidgets;
	$connectionsWidgets = new connectionsWidgetsLoad();
}

?>