<?php
/**
 * An Extension for the Connections plugin which adds a metabox for
 * adding the business hours of operation and a widget to display
 * them.
 *
 * @package   Connections Business Hours
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      http://connections-pro.com
 * @copyright 2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Hours
 * Plugin URI:        http://connections-pro.com
 * Description:       An Extension for the Connections plugin which adds a metabox for adding the business hours of operation and a widget to display them.
 * Version:           1.0
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * Text Domain:       connections_hours
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('Connections_Business_Hours') ) {

	class Connections_Business_Hours {

		public function __construct() {

			self::defineConstants();
			// self::loadDependencies();

			// register_activation_hook( CNBH_BASE_NAME . '/connections_hours.php', array( __CLASS__, 'activate' ) );
			// register_deactivation_hook( CNBH_BASE_NAME . '/connections_hours.php', array( __CLASS__, 'deactivate' ) );

			/*
			 * Load translation. NOTE: This should be ran on the init action hook because
			 * function calls for translatable strings, like __() or _e(), execute before
			 * the language files are loaded will not be loaded.
			 *
			 * NOTE: Any portion of the plugin w/ translatable strings should be bound to the init action hook or later.
			 */
			add_action( 'init', array( __CLASS__ , 'loadTextdomain' ) );

			if ( is_admin() ) {

				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'adminStyles' ) );
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'adminScripts') );

				// Register the metabox and fields.
				add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

				// Business Hours uses a custom field type, so let's add the action to add it.
				add_action( 'cn_meta_field-business_hours', array( __CLASS__, 'field' ), 10, 2 );
			}

			add_action( 'cn_meta_output_field-cnbh', array( __CLASS__, 'block' ), 10, 3 );
		}

		/**
		 * Define the constants.
		 *
		 * @access  private
		 * @since  1.0
		 * @return void
		 */
		private static function defineConstants() {

			define( 'CNBH_CURRENT_VERSION', '1.0' );
			define( 'CNBH_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNBH_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNBH_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNBH_URL', plugin_dir_url( __FILE__ ) );
		}

		private static function loadDependencies() {

			// require_once( CNBH_BASE_PATH . '' );
		}


		public static function activate() {


		}

		public static function deactivate() {


		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since 0.7.9
		 * @uses apply_filters()
		 * @uses get_locale()
		 * @uses load_textdomain()
		 * @uses load_plugin_textdomain()
		 * @return (void)
		 */
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_hours';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_lang_dir', CNBH_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_wp_lang_dir',
				WP_LANG_DIR . '/connections-hours/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}

		/**
		 * Enqueues the CSS on the Connections admin pages only.
		 *
		 * @access private
		 * @since  1.0
		 * @param  string $pageHook The current admin page hook.
		 * @return void
		 */
		public static function adminStyles( $pageHook ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$editorPages = array( $instance->pageHook->manage, $instance->pageHook->add );

			if ( in_array( $pageHook, $editorPages ) ) {

				wp_enqueue_style( 'cnbh-admin' , CNBH_URL . 'assets/css/cnbh-admin.css', array( 'cn-admin', 'cn-admin-jquery-ui' ) , '1.0' );
			}
		}

		/**
		 * Enqueues the JavaScript on the Connections admin pages only.
		 *
		 * @access private
		 * @since  1.0
		 * @param  string $pageHook The current admin page hook.
		 * @return void
		 */
		public static function adminScripts( $pageHook ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$editorPages = array( $instance->pageHook->manage, $instance->pageHook->add );

			if ( in_array( $pageHook, $editorPages ) ) {

				wp_enqueue_script( 'jquery-timepicker' , CNBH_URL . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ) , '1.4.3' );
				wp_enqueue_script( 'cnbh-ui-admin-js' , CNBH_URL . 'assets/js/cnbh-admin.js', array( 'jquery-timepicker' ) , '1.0', true );

				wp_localize_script( 'cnbh-ui-admin-js', 'cnbhDateTimePickerOptions', Connections_Business_Hours::dateTimePickerOptions() );
			}
		}

		public static function dateTimePickerOptions() {

			// Convert the PHP date/time format value to be
			// jQuery UI DateTimePicker compliant.
			$search  = array( 'G', 'H',  'h',  'g', 'i',  's',  'u', 'a',  'A' );
			$replace = array( 'H', 'HH', 'hh', 'h', 'mm', 'ss', 'c', 'tt', 'TT' );

			// $options['timeFormat'] = str_replace( $search, $replace, $format );

			$options = array(
				'currentText'   => __( 'Now', 'connections_hours' ),
				'closeText'     => __( 'Done', 'connections_hours' ),
				'amNames'       => array( __( 'AM', 'connections_hours' ), __( 'A', 'connections_hours' ) ),
				'pmNames'       => array( __( 'PM', 'connections_hours' ), __( 'P', 'connections_hours' ) ),
				'timeFormat'    => str_replace( $search, $replace, self::timeFormat() ),
				'timeSuffix'    => __( '', 'connections_hours' ),
				'timeOnlyTitle' => __( 'Choose Time', 'connections_hours' ),
				'timeText'      => __( 'Time', 'connections_hours' ),
				'hourText'      => __( 'Hour', 'connections_hours' ),
				'minuteText'    => __( 'Minute', 'connections_hours' ),
				'secondText'    => __( 'Second', 'connections_hours' ),
				'millisecText'  => __( 'Millisecond', 'connections_hours' ),
				'microsecText'  => __( 'Microsecond', 'connections_hours' ),
				'timezoneText'  => __( 'Time Zone', 'connections_hours' ),
				'isRTL'         => is_rtl(),
				'parse'         => 'loose',
				);

			return apply_filters( 'cnbh_timepicker_option', $options );
		}

		public static function timeFormat() {

			return apply_filters( 'cnbh_time_format', get_option('time_format') );
		}

		public static function formatTime( $value ) {

			$format = self::timeFormat();

			if ( strlen( $value ) > 0 ) {

				return date( $format, strtotime( $value ) );

			} else {

				return $value;
			}
		}

		public static function getWeekdays() {
			global $wp_locale;

			// Output the weekdays sorted by the start of the week
			// set in the WP General Settings. The array keys need to be
			// retained which is why array_shift and array push are not
			// being used.
			$weekStart = apply_filters( 'cnbh_start_of_week', get_option('start_of_week') );
			$weekday   = $wp_locale->weekday;

			for ( $i = 0; $i < $weekStart; $i++ ) {

				$day = array_slice( $weekday, 0, 1, true );
				unset( $weekday[ $i ] );

				$weekday = $weekday + $day;
			}

			return $weekday;
		}

		public static function registerMetabox( $metabox ) {

			$atts = array(
				'id'       => 'business-hours',
				'title'    => __( 'Business Hours', 'connections_hours' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
					array(
						'id'    => 'cnbh',
						'type'  => 'business_hours',
						),
					),
				);

			$metabox::add( $atts );
		}

		public static function field( $field, $value ) {

			?>

			<table name="start_of_week" id="start_of_week">
				<tbody>

					<thead>
						<th><?php _e( 'Weekday', 'connections_hours' ); ?></th>
						<td><?php _e( 'Open', 'connections_hours' ); ?></td>
						<td><?php _e( 'Close', 'connections_hours' ); ?></td>
						<td><?php _e( 'Add / Remove Period', 'connections_hours' ); ?></td>
					</thead>

					<tfoot>
						<th><?php _e( 'Weekday', 'connections_hours' ); ?></th>
						<td><?php _e( 'Open', 'connections_hours' ); ?></td>
						<td><?php _e( 'Close', 'connections_hours' ); ?></td>
						<td><?php _e( 'Add / Remove Period', 'connections_hours' ); ?></td>
					</tfoot>

					<tr id="cnbh-period" style="display: none;">
						<td>&nbsp;</td>
						<td>
							<?php

							cnHTML::field(
									array(
										'type'     => 'text',
										'class'    => '',
										'id'       => $field['id'] . '[day][period][open]',
										'required' => false,
										'label'    => '',
										'before'   => '',
										'after'    => '',
										'return'   => false,
									)
								);

							?>
						</td>
						<td>
							<?php

							cnHTML::field(
									array(
										'type'     => 'text',
										'class'    => '',
										'id'       => $field['id'] . '[day][period][close]',
										'required' => false,
										'label'    => '',
										'before'   => '',
										'after'    => '',
										'return'   => false,
									)
								);

							?>
						</td>
						<td><span class="button cnbh-remove-period">&ndash;</span><span class="button cnbh-add-period">+</span></td>
					</tr>

				<?php

					foreach ( self::getWeekdays() as $key => $day ) {

						// If there are no periods saved for the day,
						// add an empty period to prevent index not found errors.
						if ( ! isset( $value[ $key ] ) ) {

							$value[ $key ] = array(
								0 => array(
									'open'  => '',
									'close' => ''
									),
								);
						}

						foreach ( $value[ $key ] as $period => $data ) {

							$open = cnHTML::field(
										array(
											'type'     => 'text',
											'class'    => 'timepicker',
											'id'       => $field['id'] . '[' . $key . '][' . $period . '][open]',
											'required' => FALSE,
											'label'    => '',
											'before'   => '',
											'after'    => '',
											'return'   => TRUE,
										),
										self::formatTime( $data['open'] )
									);

							$close = cnHTML::field(
										array(
											'type'     => 'text',
											'class'    => 'timepicker',
											'id'       => $field['id'] . '[' . $key . '][' . $period . '][close]',
											'required' => FALSE,
											'label'    => '',
											'before'   => '',
											'after'    => '',
											'return'   => TRUE,
										),
										self::formatTime( $data['close'] )
									);

							if ( $period == 0 ) {

								// Display the "+" button only. This button should only be shown on the first period of the day.
								$buttons = sprintf( '<span class="button cnbh-remove-period" data-day="%1$d" data-period="%2$d" style="display: none;">–</span><span class="button cnbh-add-period" data-day="%1$d" data-period="%2$d">+</span>',
									$key,
									$period
									);

							} else {

								// Display both buttons. Both buttons should be shown for every period after the first.
								$buttons = sprintf( '<span class="button cnbh-remove-period" data-day="%1$d" data-period="%2$d">–</span><span class="button cnbh-add-period" data-day="%1$d" data-period="%2$d">+</span>',
									$key,
									$period
									);
							}

							printf( '<tr %1$s %2$s %3$s><th>%4$s</th><td>%5$s</td><td>%6$s</td><td>%7$s</td></tr>',
								'class="cnbh-day-' . absint( $key ) . '"',
								$period == 0 ? 'id="cnbh-day-' . absint( $key ) . '"' : '',
								$period == 0 ? 'data-count="' . absint( count( $value[ $key ] ) - 1 ) . '"' : '',
								$period == 0 ? esc_attr( $day ) : '&nbsp;',
								$open,
								$close,
								$buttons
								);
						}

					}

				?>

				</tbody>
			</table>

			<?php
		}

		public static function block( $id, $value, $atts ) {

			?>

			<table name="start_of_week" id="start_of_week">
				<tbody>

					<thead>
						<th><?php _e( 'Weekday', 'connections_hours' ); ?></th>
						<td><?php _e( 'Open', 'connections_hours' ); ?></td>
						<td><?php _e( 'Close', 'connections_hours' ); ?></td>
					</thead>

					<tfoot>
						<th><?php _e( 'Weekday', 'connections_hours' ); ?></th>
						<td><?php _e( 'Open', 'connections_hours' ); ?></td>
						<td><?php _e( 'Close', 'connections_hours' ); ?></td>
					</tfoot>

				<?php

					foreach ( self::getWeekdays() as $key => $day ) {

						// If there are no periods saved for the day,
						// add an empty period to prevent index not found errors.
						if ( ! isset( $value[ $key ] ) ) {

							$value[ $key ] = array(
								0 => array(
									'open'  => '',
									'close' => ''
									),
								);
						}

						foreach ( $value[ $key ] as $period => $data ) {

							$open = cnHTML::field(
										array(
											'type'     => 'text',
											'class'    => 'timepicker',
											'id'       => $id . '[' . $key . '][' . $period . '][open]',
											'required' => FALSE,
											'label'    => '',
											'before'   => '',
											'after'    => '',
											'return'   => TRUE,
										),
										self::formatTime( $data['open'] )
									);

							$close = cnHTML::field(
										array(
											'type'     => 'text',
											'class'    => 'timepicker',
											'id'       => $id . '[' . $key . '][' . $period . '][close]',
											'required' => FALSE,
											'label'    => '',
											'before'   => '',
											'after'    => '',
											'return'   => TRUE,
										),
										self::formatTime( $data['close'] )
									);

							printf( '<tr %1$s %2$s %3$s><th>%4$s</th><td>%5$s</td><td>%6$s</td></tr>',
								'class="cnbh-day-' . absint( $key ) . '"',
								$period == 0 ? 'id="cnbh-day-' . absint( $key ) . '"' : '',
								$period == 0 ? 'data-count="' . absint( count( $value[ $key ] ) - 1 ) . '"' : '',
								$period == 0 ? esc_attr( $day ) : '&nbsp;',
								$open,
								$close
								);
						}

					}

				?>

				</tbody>
			</table>

			<?php
		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Business_Hours() {

			if ( class_exists('connectionsLoad') ) {

					return new Connections_Business_Hours();

			} else {

					add_action(
							'admin_notices',
							 create_function(
									 '',
									'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Business Hours.</p></div>\';'
									)
					);

					return FALSE;
			}
	}

	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Business_Hours', 11 );

}
