<?php 
/**
 * Plugin Name: Connections Toolbar
 * Plugin URI: http://connections-pro.com/
 * Description: This plugin adds useful admin links and resources for the Connections Business Directory plugin to the WordPress Admin Bar.
 * Version: 1.0
 * Author: Steven A. Zahm
 * Author URI: http://connections-pro.com/
 * License: GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: connections-toolbar
 * Domain Path: /languages/
 *
 * Copyright (c) 2012-2013 ZAHMit.design
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
 if ( ! class_exists( 'CN_Toolbar' ) ) {

	class CN_Toolbar {

		/**
		* @var (object) Instance of this class.
		*/
		private static $instance;

		/**
		* @var (bool) Init the plugin.
		*/
		private static $init = TRUE;

		/**
		 * A dummy constructor to prevent class from being loaded more than once.
		 *
		 * @access private
		 * @since 1.0
		 * @see CN_Toolbar::instance()
		 * @see CN_Toolbar();
		 */
		private function __construct() { /* Do nothing here */ }

		/**
		 * Insures that only one instance exists at any one time.
		 *
		 * @access public
		 * @since 1.0
		 * @return [mixed] (bool) | (object) CN_Toolbar
		 */
		public static function getInstance() {

			// Quick check to see if Connections is loaded.
			add_action( 'admin_init', array( __CLASS__, 'check' ) );

			if ( ! isset( self::$instance ) && self::$init ) {

				self::$instance = new self;
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Check to ensure Connections is active.
		 * If not, deactivate the plugin.
		 *
		 * NOTE: This seems to have to be fired hooked into the
		 * `admin_init` action hook otherwise the functions are
		 * not yet available.
		 *
		 * @access private
		 * @since 1.0
		 * @return (bool)
		 */
		public static function check() {

			if ( ! class_exists( 'connectionsLoad' ) ) {

				add_action(
					'admin_notices',
					 create_function(
						'',
						'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Toolbar; deactivating...</p></div>\';'
						)
				);

				$plugin = plugin_basename( __FILE__ );
				// if ( is_admin() ) $plugin_data = get_plugin_data( __FILE__, FALSE );

				if( is_plugin_active( $plugin ) )
					deactivate_plugins( $plugin );

				self::$init = FALSE;

				// wp_redirect( add_query_arg( array( 'deactivate' => 'true' ), admin_url( 'plugins.php' ) ) );
				return FALSE;
			}

			return TRUE;

		}

		/**
		 * Initiate the plugin.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private static function init() {

			self::defineConstants();

			/*
			 * Load translation. NOTE: This should be ran on the init action hook because
			 * function calls for translatable strings, like __() or _e(), execute before
			 * the language files are loaded will not be loaded.
			 *
			 * NOTE: Any portion of the plugin w/ translatable strings should be bound to the init action hook or later.
			 */
			add_action( 'init', array( __CLASS__ , 'loadTextdomain' ) );

			/*
			 * Add the toolbar and menu items.
			 */
			add_action( 'admin_bar_menu', array( __CLASS__, 'toolbar' ), 99 );

			/*
			 * Add the styles to the page head.
			 */
			add_action( 'wp_head', array( __CLASS__, 'css' ) );
			add_action( 'admin_head', array( __CLASS__, 'css' ) );
		}
		
		/**
		 * Define the core constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return (void)
		*/
		private static function defineConstants() {

			/*
			 * Version Constants
			 */
			define( 'CNTB_CURRENT_VERSION', '1.0' );

			/*
			 * Core Constants
			 */
			define( 'CNTB_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNTB_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNTB_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNTB_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Load the plugin translation.
		 *
		 * NOTE: Translations ship with the core Connections plugin so by default
		 * the translations will be loaded from the Connections plugin languages folder
		 * unless a custom translation exists in the WP_LANG/connections folder.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since 1.0
		 * @uses apply_filters()
		 * @uses get_locale()
		 * @uses load_textdomain()
		 * @uses load_plugin_textdomain()
		 * @return (void)
		 */
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections-toolbar';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_toolbar_lang_dir', CNTB_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_toolbar_wp_lang_dir',
				WP_LANG_DIR . '/connections/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}
		
		public static function toolbar( $admin_bar ) {

			// Bail if the user is not an admin that can manage options.
			if ( ! current_user_can( 'manage_options' ) ) return;

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar',
				'title' => __( 'Connections', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_dashboard' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'class' => 'icon-connections',
					'title' => _x( 'Connections Dashboard', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-dashboard',
				'parent' => 'cn-toolbar',
				'title' => __( 'Dashboard', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_dashboard' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Dashboard', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-manage',
				'parent' => 'cn-toolbar',
				'title' => __( 'Manage', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_manage' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Manage', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-manage-filter-approved',
				'parent' => 'cn-toolbar-manage',
				'title' => __( 'Filter: Approved', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_manage' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show Only Approved Entries', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-manage-filter-pending',
				'parent' => 'cn-toolbar-manage',
				'title' => __( 'Filter: Pending', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_manage' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show Entries Awaiting Moderation', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-manage-add-entry',
				'parent' => 'cn-toolbar-manage',
				'title' => __( 'Add New Entry', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_add' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Add New Entry', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-manage-categories',
				'parent' => 'cn-toolbar-manage',
				'title' => __( 'Categories', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_categories' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Manage Categories', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates',
				'parent' => 'cn-toolbar',
				'title' => __( 'Templates', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Manage Templates', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates-filter-individual',
				'parent' => 'cn-toolbar-templates',
				'title' => __( 'Filter: Individual', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates', 'type' => 'individual' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show the "Individual" Template Type', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates-filter-organization',
				'parent' => 'cn-toolbar-templates',
				'title' => __( 'Filter: Organization', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates', 'type' => 'organization' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show the "Organization" Template Type', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates-filter-family',
				'parent' => 'cn-toolbar-templates',
				'title' => __( 'Filter: Family', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates', 'type' => 'family' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show the "Family" Template Type', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates-filter-anniversary',
				'parent' => 'cn-toolbar-templates',
				'title' => __( 'Filter: Anniversary', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates', 'type' => 'anniversary' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show the "Anniversary" Template Type', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));
			
			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-templates-filter-birthday',
				'parent' => 'cn-toolbar-templates',
				'title' => __( 'Filter: Birthday', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_templates', 'type' => 'birthday' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Show the "Birthday" Template Type', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node ( array(
				'id' => 'cn-toolbar-templates-secondary-group',
				'parent' => 'cn-toolbar-templates',
				'group' => TRUE,
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-purchase-templates',
				'parent' => 'cn-toolbar-templates-secondary-group',
				'title' => __( 'Get More', 'connections-toolbar' ),
				'href'  => esc_url_raw ( 'http://connections-pro.com/templates/' ),
				'meta'  => array(
					'title' => _x( 'Purchase Premium Templates', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings',
				'parent' => 'cn-toolbar',
				'title' => __( 'Settings', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-general',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'General', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'General Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-display',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'Display', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'display' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Display Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-images',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'Images', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'images' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Images Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-search',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'Search', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'search' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Search Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-seo',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'SEO', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'seo' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'SEO Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-roles',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'Roles', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_roles' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Roles and Capabilities', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-settings-advanced',
				'parent' => 'cn-toolbar-settings',
				'title' => __( 'Advanced', 'connections-toolbar' ),
				'href'  => add_query_arg( array( 'page' => 'connections_settings', 'tab' => 'advanced' ) , admin_url() . 'admin.php' ),
				'meta'  => array(
					'title' => _x( 'Advanced Settings', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
				),
			));

			$admin_bar->add_node ( array(
				'id' => 'cn-toolbar-add-ons-group',
				'parent' => 'cn-toolbar',
				'group' => TRUE,
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-add-ons',
				'parent' => 'cn-toolbar-add-ons-group',
				'title' => __( 'Extensions &amp; Templates', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/' ),
				'meta'  => array(
					'title' => _x( 'Extensions &amp; Extensions', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-add-on-extensions',
				'parent' => 'cn-toolbar-add-ons',
				'title' => __( 'Extensions', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/extensions/' ),
				'meta'  => array(
					'title' => _x( 'Purchase Extensions', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-add-on-templates',
				'parent' => 'cn-toolbar-add-ons',
				'title' => __( 'Templates', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/templates/' ),
				'meta'  => array(
					'title' => _x( 'Purchase Premium Templates', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node ( array(
				'id' => 'cn-toolbar-support-group',
				'parent' => 'cn-toolbar',
				'group' => TRUE,
				'meta' => array(
					'class' => 'ab-sub-secondary',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forums',
				'parent' => 'cn-toolbar-support-group',
				'title' => __( 'Support Forums', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/' ),
				'meta'  => array(
					'title' => _x( 'Support Forums', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-feature-requests',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Feature Requests', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/feature-requests/' ),
				'meta'  => array(
					'title' => _x( 'Feature Requests', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-pre-sales',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Pre Sales Questions', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/presale-questions/' ),
				'meta'  => array(
					'title' => _x( 'Pre Sales Questions', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-general',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'General', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/general-support/' ),
				'meta'  => array(
					'title' => _x( 'General', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-extension',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Extensions', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/extension/' ),
				'meta'  => array(
					'title' => _x( 'Extensions', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-template',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Templates', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/template/' ),
				'meta'  => array(
					'title' => _x( 'Templates', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-plugin-conflicts',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Plugin Conflicts', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/plugin-conflicts/' ),
				'meta'  => array(
					'title' => _x( 'Plugin Conflicts', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-forum-theme-conflicts',
				'parent' => 'cn-toolbar-support-forums',
				'title' => __( 'Theme Conflicts', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/support/forum/theme-conflicts/' ),
				'meta'  => array(
					'title' => _x( 'Theme Conflicts', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation',
				'parent' => 'cn-toolbar-support-group',
				'title' => __( 'Documentation', 'connections-toolbar' ),
				'href'  => FALSE,
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation-faqs',
				'parent' => 'cn-toolbar-support-documentation',
				'title' => __( 'FAQs', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/faq/' ),
				'meta'  => array(
					'title' => _x( 'Frequently Asked Questions', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation-quicktips',
				'parent' => 'cn-toolbar-support-documentation',
				'title' => __( 'QuickTips', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/quicktips/' ),
				'meta'  => array(
					'title' => _x( 'QuickTips', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation-shortcodes',
				'parent' => 'cn-toolbar-support-documentation',
				'title' => __( 'Shortcodes', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/documentation/connections/shortcodes/' ),
				'meta'  => array(
					'title' => _x( 'Shortcodes', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation-translation',
				'parent' => 'cn-toolbar-support-documentation',
				'title' => __( 'Translation', 'connections-toolbar' ),
				'href'  => esc_url_raw( 'http://connections-pro.com/documentation/connections/translation/' ),
				'meta'  => array(
					'title' => _x( 'Translation', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));

			$strSearch = __( 'Search', 'connections-toolbar' );

			/* Disable this for now as it causes PHP errors on the site. reason is unknown at the moment. */
			/*$admin_bar->add_node( array(
				'id'    => 'cn-toolbar-support-documentation-search',
				'parent' => 'cn-toolbar-support-group',
				'title' => '
					<form method="get" action="http://connections-pro.com/" class=" " target="_blank">
					<input type="text" placeholder="' . $strSearch . '" onblur="this.value=(this.value==\'\') ? \'' . $strSearch . '\' : this.value;" onfocus="this.value=(this.value==\'' . $strSearch . '\') ? \'\' : this.value;" value="' . $strSearch . '" name="s" value="" class="text cn-toolbar-search-input" />
					<input type="hidden" name="post_type[]" value="documentation" />
					<input type="hidden" name="post_type[]" value="faqs" />
					<input type="submit" value="' . __( 'GO', 'connections-toolbar' ) . '" class="cn-toolbar-search-submit"  /></form>',
				'href'  => FALSE,
				'meta'  => array(
					'title' => _x( 'Search the documentation.', 'This is a tooltip shown on mouse hover.', 'connections-toolbar' ),
					'target' => '_blank',
				),
			));*/

			/*
			 * Rather than create a bunch of hooks or filters
			 * to allow adding/removing nodes; provide a action
			 * passing $menu_bar that way one knows the core
			 * toolbar nodes have been added.
			 */
			do_action( 'cn_admin_bar_menu', $admin_bar );
		}

		public static function css() {

			// No styles if admin bar is disabled or user is not logged in.
			if ( ! is_admin_bar_showing() || ! is_user_logged_in() ) {
				return;
			}

			?>
<style type="text/css">
	#wpadminbar.nojs .ab-top-menu > li.menupop.icon-connections:hover > .ab-item,
	#wpadminbar .ab-top-menu > li.menupop.icon-connections.hover > .ab-item,
	#wpadminbar.nojs .ab-top-menu > li.menupop.icon-connections > .ab-item,
	#wpadminbar .ab-top-menu > li.menupop.icon-connections > .ab-item {
		background-image: url(<?php echo esc_url_raw( plugins_url( 'connections/assets/images/menu.png' ) ); ?>);
		background-repeat: no-repeat;
		background-position: 0.85em 50%;
		padding-left: 30px;
	}
	#wpadminbar .cn-toolbar-search-input {
		width: 140px;
	}
	#wp-admin-bar-ddw-edd-eddsupportsections .ab-item,
	#wp-admin-bar-ddw-edd-edddocsquick .ab-item,
	#wp-admin-bar-ddw-edd-edddocssections .ab-item,
	#wpadminbar .cn-toolbar-search-input,
	#wpadminbar .cn-toolbar-search-submit {
		color: #21759b !important;
		text-shadow: none;
	}
	#wpadminbar .cn-toolbar-search-input,
	#wpadminbar .cn-toolbar-search-submit {
		background-color: #fff;
		height: 18px;
		line-height: 18px;
		padding: 1px 4px;
	}
	#wpadminbar .cn-toolbar-search-submit {
		-webkit-border-radius: 11px;
		   -moz-border-radius: 11px;
				border-radius: 11px;
		font-size: 0.67em;
		margin: 0 0 0 2px;
	}
</style>
			<?php
		}
	}

	/**
	 * Start up the class.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed object | bool
	 */
	function Connections_Toolbar() {

		return CN_Toolbar::getInstance();
	}

	/**
	 * Start the plugin.
	 *
	 * Connections loads at default priority 10, this add-on is dependent on Connections,
	 * and other add-ons; load at priority 10.1 that we'll want to be able to hook into the toolbar,
	 * we'll load with priority 10.1 so we know Connections and its other add-ons will be loaded 
	 * and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Toolbar', 10.1 );
}