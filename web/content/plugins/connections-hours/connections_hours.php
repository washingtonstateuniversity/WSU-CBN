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
			self::loadDependencies();

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

				// Enqueue the admin CSS and JS
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'adminStyles' ) );
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'adminScripts') );

				// Register the metabox and fields.
				add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

				// Business Hours uses a custom field type, so let's add the action to add it.
				add_action( 'cn_meta_field-business_hours', array( __CLASS__, 'field' ), 10, 2 );

				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-business_hours', array( __CLASS__, 'sanitize') );
			}

			// Enqueue the public CSS
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ), 11 );

			// Add the action that'll be run when calling $entry->getMetaBlock( 'cnbh' ) from within a template.
			add_action( 'cn_meta_output_field-cnbh', array( __CLASS__, 'block' ), 10, 3 );

			// Register the widget.
			add_action( 'widgets_init', create_function( '', 'register_widget( "cnbhHoursWidget" );' ) );
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

			require_once( CNBH_PATH . 'includes/class.widgets.php' );
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

		public static function enqueueScripts() {

			wp_enqueue_style( 'cnbh-public', CNBH_URL . 'assets/css/cnbh-public.css', array(), '1.0' );
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

			return apply_filters( 'cnbh_timepicker_options', $options );
		}

		public static function timeFormat() {

			return apply_filters( 'cnbh_time_format', get_option('time_format') );
		}

		public static function formatTime( $value, $format = NULL ) {

			$format = is_null( $format ) ? self::timeFormat() : $format;

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
				'context'  => 'normal',
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

			printf( '<p>%s</p>', __( 'To create a closed day or closed period within a day, leave both the open and close hours blank.', 'connections_hours' ) );
		}

		/**
		 * Sanitize the times as a text input using the cnSanitize class.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  array $value   The opening/closing hours.
		 *
		 * @return array
		 */
		public static function sanitize( $value ) {

			foreach ( $value as $key => $day ) {

				foreach ( $day as $period => $time ) {

					// Save all time values in 24hr format.
					$time['open']  = self::formatTime( $time['open'], 'H:i' );
					$time['close'] = self::formatTime( $time['close'], 'H:i' );

					$value[ $key ][ $period ]['open']  = cnSanitize::string( 'text', $time['open'] );
					$value[ $key ][ $period ]['close'] = cnSanitize::string( 'text', $time['close'] );

				}
			}

			return $value;
		}

		/**
		 * The output of the business hour data.
		 *
		 * Called by the cn_meta_output_field-cnbh action in cnOutput->getMetaBlock().
		 *
		 * @access  private
		 * @since  1.0
		 * @param  string $id    The field id.
		 * @param  array  $value The business hours data.
		 * @param  array  $atts  The shortcode atts array passed from the calling action.
		 *
		 * @return string
		 */
		public static function block( $id, $value, $atts ) {
			global $wp_locale;

			$defaults = array(
				'header'                => TRUE,
				'footer'                => FALSE,
				'day_name'              => 'full', // Valid options are 'full', 'abbrev' or 'initial'.
				'show_closed_day'       => TRUE,
				'show_closed_period'    => FALSE,
				'show_if_no_hours'      => FALSE,
				'show_open_status'      => TRUE,
				'highlight_open_period' => TRUE,
				'open_close_separator'  => '&ndash;',
				);

			$atts = wp_parse_args( $atts, $defaults );



			echo '<div class="cnbh-block">';

			// Whether or not to display the open status message.
			if ( $atts['show_open_status'] && self::openStatus( $value ) ) {

				printf( '<p class="cnbh-status cnbh-status-open">%s</p>' , 'We are currently open.' );

			} elseif ( $atts['show_open_status'] ) {

				printf( '<p class="cnbh-status cnbh-status-closed">%s</p>' , 'Sorry, we are currently closed.' );
			}

			?>

			<table class="cnbh">
				<tbody>

					<?php if ( $atts['header'] ) : ?>

					<thead>
						<th>&nbsp;</th>
						<th><?php _e( 'Open', 'connections_hours' ); ?></th>
						<th class="cnbh-separator">&nbsp;</th>
						<th><?php _e( 'Close', 'connections_hours' ); ?></th>
					</thead>

					<?php endif; ?>

					<?php if ( $atts['footer'] ) : ?>

					<tfoot>
						<th>&nbsp;</th>
						<th><?php _e( 'Open', 'connections_hours' ); ?></th>
						<th class="cnbh-separator">&nbsp;</th>
						<th><?php _e( 'Close', 'connections_hours' ); ?></th>
					</tfoot>

					<?php endif; ?>

					<?php

					foreach ( self::getWeekdays() as $key => $day ) {

						// Display the day as either its initial or abbreviation.
						switch ( $atts['day_name'] ) {

							case 'initial' :

								$day = $wp_locale->get_weekday_initial( $day );
								break;

							case 'abbrev' :

								$day = $wp_locale->get_weekday_abbrev( $day );
								break;
						}

						// Show the "Closed" message if there are no open and close hours recorded for the day.
						if ( $atts['show_closed_day'] && ! self::openToday( $value[ $key ] ) ) {

							printf( '<tr %1$s %2$s %3$s><th>%4$s</th><td class="cnbh-closed" colspan="3">%5$s</td></tr>',
								'class="cnbh-day-' . absint( $key ) . '"',
								'id="cnbh-day-' . absint( $key ) . '"',
								'data-count="' . absint( count( $value[ $key ] ) - 1 ) . '"',
								esc_attr( $day ),
								__( 'Closed Today', 'connections_hours' )
								);

							// Exit this loop.
							continue;
						}

						// If there are open and close hours recorded for the day, loop thru the open periods.
						foreach ( $value[ $key ] as $period => $time ) {

							// Show the "Closed" message if there are no open and close hours recorded for the period.
							if ( self::openPeriod( $time ) ) {

							printf( '<tr %1$s %2$s %3$s><th>%4$s</th><td class="cnbh-open">%5$s</td><td class="cnbh-separator">%6$s</td><td class="cnbh-close">%7$s</td></tr>',
								'class="cnbh-day-' . absint( $key ) . ( $atts['highlight_open_period'] && date( 'w', current_time( 'timestamp' ) ) == $key && self::isOpen( $time['open'], $time['close'] ) ? ' cnbh-open-period' : '' ) . '"',
								$period == 0 ? 'id="cnbh-day-' . absint( $key ) . '"' : '',
								$period == 0 ? 'data-count="' . absint( count( $value[ $key ] ) - 1 ) . '"' : '',
								$period == 0 ? esc_attr( $day ) : '&nbsp;',
								self::formatTime( $time['open'] ),
								esc_attr( $atts['open_close_separator'] ),
								self::formatTime( $time['close'] )
								);

							} elseif ( $atts['show_closed_period'] && $period > 0 ) {

								printf( '<tr %1$s %2$s %3$s><th>%4$s</th><td class="cnbh-closed" colspan="3">%5$s</td></tr>',
									'class="cnbh-day-' . absint( $key ) . '"',
									'id="cnbh-day-' . absint( $key ) . '"',
									'data-count="' . absint( count( $value[ $key ] ) - 1 ) . '"',
									$period == 0 ? esc_attr( $day ) : '&nbsp;',
									__( 'Closed Period', 'connections_hours' )
									);

							}

						}

					}

					?>

				</tbody>
			</table>

			<?php

			echo '</div>';
		}

		public static function openStatus( $value ) {

			foreach ( self::getWeekdays() as $key => $day ) {

				foreach ( $value[ $key ] as $period => $time ) {

					if ( date( 'w', current_time( 'timestamp' ) ) == $key && self::isOpen( $time['open'], $time['close'] ) ) return TRUE;

				}

			}

			return FALSE;
		}

		/**
		 * Whether or not there are any open hours during the week.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  array  $days
		 * @return boolean
		 */
		public static function hasOpenHours( $days ) {

			foreach ( $days as $key => $day ) {

				if ( self::openToday( $day ) ) return TRUE;
			}

			return FALSE;
		}

		/**
		 * Whether or not the day has any open periods.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  array $day
		 *
		 * @return bool
		 */
		private static function openToday( $day ) {

			foreach ( $day as $period => $data ) {

				if ( self::openPeriod( $data ) ) return TRUE;
			}

			return FALSE;
		}

		/**
		 * Whether or not the period is open.
		 *
		 * @access  private
		 * @since  1.0
		 * @param  array $period
		 *
		 * @return bool
		 */
		private static function openPeriod( $period ) {

			if ( empty( $period ) ) return FALSE;

			if ( ! empty( $period['open'] ) && ! empty( $period['close'] ) ) return TRUE;

			return FALSE;
		}

		// http://stackoverflow.com/a/17145145
		private static function isOpen( $t1, $t2, $tn = NULL ) {

			$tn = is_null( $tn ) ? date( 'H:i', current_time( 'timestamp' ) ) : self::formatTime( $tn, 'H:i' );

			$t1 = +str_replace( ':', '', $t1 );
			$t2 = +str_replace( ':', '', $t2 );
			$tn = +str_replace( ':', '', $tn );

			if ( $t2 >= $t1 ) {

				return $t1 <= $tn && $tn < $t2;

			} else {

				return ! ( $t2 <= $tn && $tn < $t1 );
			}

		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 *
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
