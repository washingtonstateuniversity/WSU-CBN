<?php
/*
Plugin Name: Connections CSV
Plugin URI: http://connections-pro.com/connections-pro/connections-csv-import-pro-module/
Description: Adds the ability to Import CSV files.
Version: 1.0.4
Author: Steven A. Zahm
Author URI: http://connections-pro.com

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



This plugin uses:

Class: parseCSV v0.4.3 beta
	http://code.google.com/p/parsecsv-for-php/

	Fully conforms to the specifications lined out on wikipedia:
	 - http://en.wikipedia.org/wiki/Comma-separated_values

	Based on the concept of Ming Hong Ng's CsvFileParser class:
	 - http://minghong.blogspot.com/2006/07/csv-parser-for-php.html

	Copyright (c) 2007 Jim Myhrberg (jim@zydev.info).

*/

if ( ! class_exists('Connections_CSV_Import') ) {

	class Connections_CSV_Import {

		public static $options;

		public function __construct() {

			self::loadConstants();
			self::loadDependencies();
			self::initOptions();;

			register_activation_hook( dirname(__FILE__) . '/connections_csv.php', array( __CLASS__, 'activate' ) );
			// register_deactivation_hook( dirname(__FILE__) . '/connections_csv.php', array( __CLASS__, 'deactivate' ) );

			if ( is_admin() ) {

				// The AJAX import handler.
				add_action( 'wp_ajax_cncsv_import' , array( __CLASS__, 'import' ) );

				add_action( 'admin_print_styles', array( __CLASS__, 'loadAdminStyles' ) );
				// add_action( 'admin_print_scripts', array(&$this, 'loadAdminScripts') );
				add_filter( 'cn_submenu', array( __CLASS__, 'addMenu' ) );

				do_action('cncsv_processes');
			}
		}

		private static function loadConstants() {

			define( 'CNCSV_CURRENT_VERSION', '1.0.4' );
			define( 'CNCSV_BASE_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNCSV_BASE_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNCSV_BASE_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) );

			define( 'CNCSV_UPLOAD_PATH', CNCSV_BASE_PATH . '/' . 'csv_files' );
		}

		private static function loadDependencies() {

			require_once( CNCSV_BASE_PATH . '/includes/class.options.php' );
			require_once( CNCSV_BASE_PATH . '/includes/class.entry-import-csv.php' );

			// if ( file_exists(CNCSV_BASE_PATH . '/includes/inc.export.php') )
			// {
			// 	require_once(CNCSV_BASE_PATH . '/includes/inc.export.php');
			// }
		}

		/**
		 * During install this will initiate the options. During upgrades, previously set options
		 * will be left intact but will set any new options not available in previous versions.
		 * @return
		 */
		private static function initOptions() {

			self::$options = new connectionsCSVOptions();
		}

		/**
		 * Called when activating Connections Pro via the activation hook.
		 * @return null
		 */
		public static function activate() {

			$this->options->setVersion(CNCSV_CURRENT_VERSION);
		}

		/**
		 * Called when deactivating Connections Pro via the deactivation hook.
		 * @return null
		 */
		public static function deactivate() {

			//$this->options->removeDefaultCapabilities();
		}

		/**
		 * Adds the menu as a sub item of Connections.
		 *
		 * @access  private
		 * @since  unkown
		 * @param array $menu
		 * @return array
		 */
		public static function addMenu( $menu ) {

			$menu[70] = array(
				'hook'       => 'csv',
				'page_title' => 'Connections : Import CSV',
				'menu_title' => 'Import CSV',
				'capability' => 'connections_add_entry',
				'menu_slug'  => 'connections_csv',
				'function'   => array( __CLASS__, 'showPage')
				);

			return $menu;
		}

		/**
		 * Enqueues the CSS on the import admin page only.
		 *
		 * @access private
		 * @since  unknown
		 * @return void
		 */
		public static function loadAdminStyles() {

			if ( ! isset( $_GET['page'] ) ) return;

			/*
			 * Load styles only on the Connections plug-in admin pages.
			 */
			if ( $_GET['page'] == 'connections_csv' ) {

				wp_enqueue_style( 'cncsv_admin_css', CNCSV_BASE_URL . '/assets/css/cncsv-admin.css', array( 'cn-admin' ), CNCSV_CURRENT_VERSION );
			}
		}

		/**
		 * Enqueues the JavaScript on the import admin page only.
		 *
		 * @access private
		 * @since  unknown
		 * @return void
		 */
		public static function loadAdminScripts() {

			if ( ! isset( $_GET['page'] ) ) return;

			if ( $_GET['page'] == 'connections_csv' ) {

				wp_enqueue_script( 'cncsv_admin_js', CNCSV_BASE_URL . '/assets/js/cncsv-admin.js', array('jquery'), CNCSV_CURRENT_VERSION );
			}
		}

		/**
		 * Renders the admin page.
		 *
		 * @access  private
		 * @since  unknown
		 * @return void
		 */
		public static function showPage() {

			if ( ! isset( $_GET['page'] ) ) return;

			switch ( $_GET['page'] ) {

				case 'connections_csv':

					include_once ( dirname (__FILE__) . '/includes/admin/pages/csv.php' );
					connectionsCSVImportPage();
					break;
			}

		}

		/**
		 * JavaScript added to the import page footer.
		 *
		 * @access  private
		 * @since  1.0
		 * @return string
		 */
		public static function ajax() {

			$nonce = wp_create_nonce( 'cncsv-nonce-import' );

			?>

			<script type="text/javascript" >

				jQuery(document).ready( function($) {

					// var pull;
					// var pullInterval = 10000; // Ten seconds (10000).
					var maxValue;

					// Init the progress bar.
					$('#cncsv-import-progress').progressbar( {
						value: false
					});

					$('#cncsv-progress-label').text( 'Please wait...' );

					/**
					 * Inits an import and updates the onscreen status.
					 *
					 * @return string
					 */
					function ImportProgress() {

						var result = $.post( ajaxurl,
							{
								action: 'cncsv_import',
								cncsv_nonce: <?php echo '"' . $nonce . '"'; ?>,
							});

						result.always( function( data ) {

							if ( data == 1 ) {

								$('#cncsv-import-progress').progressbar( {

									complete: function() {
											$('#cncsv-progress-label').text( 'Import Complete!' );
										}
								});

							} else if ( data == -1 ) {

								$('#cncsv-import-progress').progressbar( {

									complete: function() {
											$('#cncsv-progress-label').text( 'Import Error!' );
										}
								});

							} else {

								// $('#cncsv-import-results').empty().append( data ); // Use only for debugging.

								result   = $.parseJSON( data );
								maxValue = parseInt( result.count );

								// Update the progress bar.
								$('#cncsv-import-progress').progressbar( {

									value: Math.floor( parseInt( result.offset ) * 100 / maxValue ),
									change: function() {
											$('#cncsv-progress-label').text( $('#cncsv-import-progress').progressbar( "value" ) + "%" );
										}
								});

								// Import the next batch.
								ImportProgress();
							}

						});
					}

					// Start the import process; importing the intial batch.
					ImportProgress();

					// Batch import the remaining CSV rows on every pollInterval.
					// pull = window.setInterval( function () {

					// 	ImportProgress();

					// }, pullInterval );

				});

			</script>

			<?php
		}

		/**
		 * Processes the import in batches.
		 * This is an admin ajax callback.
		 *
		 * @access  private
		 * @since  1.0
		 * @return string JSON encoded responce.
		 */
		public static function import() {

			// Check the nonce.
			check_ajax_referer( 'cncsv-nonce-import', 'cncsv_nonce' );

			$options = get_option( 'cncsv_import_options' );

			if ( $options ) {

				// Always make sure the limit is set to 100
				// that way there should not be import issues
				// with shared hosts.
				$options['limit'] = 100;

				// If the process is locked, send back the $option array for the AJAX handler.
				if ( get_option( 'cncsv_import_lock' ) ) {

					echo json_encode( $options );
					die();
				}

				// If the current offset is less than the row count, import the next set of rows.
				if ( $options['offset'] < $options['count'] ) {

					// Process lock so there is only one import thread going
					// so shared hosts are not brought to their knees.
					update_option( 'cncsv_import_lock', TRUE );

					// Setup the import options.
					$csv = new cnCSV( $options );

					// Import the CSV file.
					$result = $csv->import( $options['map'] );

					// Increase the offset by the limit value so we import the next set of rows.
					$options['offset'] = $options['offset'] + $options['limit'];

					// Save the options to the WP options table for later use.
					update_option( 'cncsv_import_options', $options );

					// Delete the process lock so the next batch can be imported.
					delete_option( 'cncsv_import_lock' );

					// Send back the $option array for the AJAX handler.
					echo json_encode( $options );
					die();

				} else {

					// Delete the options.
					delete_option( 'cncsv_import_options' );

					// Send the complete code.
					echo '1';
					die();
				}

			}

			// Should not get here, send the error code.
			echo '-1';
			die();
		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_CSV_Import() {

			if ( class_exists('connectionsLoad') ) {

					return new Connections_CSV_Import();

			} else {

					add_action(
							'admin_notices',
							 create_function(
									 '',
									'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections CSV Import.</p></div>\';'
									)
					);

					return FALSE;
			}
	}

	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_CSV_Import', 11 );

}
