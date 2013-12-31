<?php

/**
 * Static class for displaying template parts.
 *
 * @package     Connections Business Hours
 * @subpackage  Widget
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnbhHoursWidget extends WP_Widget {

	public function __construct() {

		$options = array(
			'description' => __('Business Hours', 'connections_cnbh' )
		);

		parent::__construct(
			'cnbh_display_hours',
			'Connections : ' . __('Business Hours', 'connections_cnbh' ),
			$options
		);
	}

	/**
	 * Logic for handling updates from the widget form.
	 *
	 * @access  private
	 * @since  1.0
	 * @param array $new
	 * @param array $old
	 *
	 * @return array
	 */
	public function update( $new, $old ) {

		$new['title'] = strip_tags( $new['title'] );

		// Check for checkboxes and set their values accordingly.
		$new['header']                = isset( $new['header'] ) ? '1' : '0';
		$new['footer']                = isset( $new['footer'] ) ? '1' : '0';
		$new['show_closed_day']       = isset( $new['show_closed_day'] ) ? '1' : '0';
		$new['show_closed_period']    = isset( $new['show_closed_period'] ) ? '1' : '0';
		$new['show_if_no_hours']      = isset( $new['show_if_no_hours'] ) ? '1' : '0';
		$new['show_open_status']      = isset( $new['show_open_status'] ) ? '1' : '0';
		$new['highlight_open_period'] = isset( $new['highlight_open_period'] ) ? '1' : '0';

		// Ensure only a valid option is saved for the day name.
		$dayName         = isset( $new['day_name'] ) ? $new['day_name'] : 'abbrev';
		$new['day_name'] = in_array( $dayName, array( 'full', 'abbrev', 'initial' ) ) ? $dayName : 'abbrev';

		return $new;
	}

	/**
	 * Function for handling the widget control in admin panel.
	 *
	 * @access  private
	 * @since  1.0
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {

		// Setup the default widget options.
		$title               = isset( $instance['title'] ) && strlen( $instance['title'] ) > 0 ? esc_attr( $instance['title'] ) : __( 'Business Hours', 'connections_cnbh' );
		$header              = isset( $instance['header'] ) ? $instance['header'] : '1';
		$footer              = isset( $instance['footer'] ) ? $instance['footer'] : '0';
		$showClosedDay       = isset( $instance['show_closed_day'] ) ? $instance['show_closed_day'] : '1';
		$showClosedPeriod    = isset( $instance['show_closed_period'] ) ? $instance['show_closed_period'] : '0';
		$show                = isset( $instance['show_if_no_hours'] ) ? $instance['show_if_no_hours'] : '0';
		$showOpenStatus      = isset( $instance['show_open_status'] ) ? $instance['show_open_status'] : '1';
		$highlightOpenPeriod = isset( $instance['highlight_open_period'] ) ? $instance['highlight_open_period'] : '1';

		cnHTML::text(
			array(
				'prefix' => '',
				'class'  => 'widefat',
				'id'     => $this->get_field_id('title'),
				'name'   => $this->get_field_name('title'),
				'label'  => __('Title:', 'connections_cnbh'),
				'before' => '<p>',
				'after'  => '</p>',
				),
			$title
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('show_open_status'),
				'name'    => $this->get_field_name('show_open_status'),
				'label'   => __( 'Show the open status message above the operating hours.', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$showOpenStatus
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('header'),
				'name'    => $this->get_field_name('header'),
				'label'   => __( 'Show Open/Close Header', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$header
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('footer'),
				'name'    => $this->get_field_name('footer'),
				'label'   => __( 'Show Open/Close Footer', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$footer
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('show_closed_day'),
				'name'    => $this->get_field_name('show_closed_day'),
				'label'   => __( 'Show the days that are closed with a "Closed Today" message.', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$showClosedDay
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('show_closed_period'),
				'name'    => $this->get_field_name('show_closed_period'),
				'label'   => __( 'Show the periods within a day that are closed with a "Closed Period" message.', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$showClosedPeriod
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('show_if_no_hours'),
				'name'    => $this->get_field_name('show_if_no_hours'),
				'label'   => __( 'Show the operating hours if there are no open days or periods in the week. All days will be shown with a "Closed Today" message only if the show "Closed Today" message option is enabled.', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$show
		);

		cnHTML::checkbox(
			array(
				'prefix'  => '',
				'id'      => $this->get_field_id('highlight_open_period'),
				'name'    => $this->get_field_name('highlight_open_period'),
				'label'   => __( 'Highlight the current open period within the operating hours.', 'connections_cnbh' ),
				'before'  => '<p>',
				'after'   => '</p>',
				),
			$highlightOpenPeriod
		);

		cnHTML::select(
			array(
				'id'      => $this->get_field_name('day_name'),
				'options' => array(
					'full'    => __( 'Full Name', 'connections_cnbh' ),
					'abbrev'  => __( 'Abbreviated', 'connections_cnbh' ),
					'initial' => __( 'Initial', 'connections_cnbh' ),
					),
				'label'    => __( 'Display the weekday name as:', 'connections_cnbh' ),
				'before'   => '<p>',
				'after'    => '</p>',
				),
			isset( $instance['day_name'] ) ? $instance['day_name'] : 'abbrev'
		);

	}

	/**
	 * Function for displaying the widget on the page.
	 *
	 * @access  private
	 * @since  1.0
	 * @param  array $args
	 * @param  array $instance
	 *
	 * @return string
	 */
	public function widget( $args, $option ) {

		// Only process and display the widget if displaying a single entry.
		if ( get_query_var( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			// Query the entry.
			$result = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Setup the entry object
			$entry = new cnEntry( $result[0] );

			// Query the entry meta.
			$metadata = $entry->getMeta( array( 'key' => 'cnbh', 'single' => TRUE ) );

			// Extract $before_widget, $after_widget, $before_title and $after_title.
			extract( $args );

			// Setup the default widget options if they were not set when they were added to the sidebar;
			// ie. the user did not click the "Save" button on the widget.
			$title               = strlen( $option['title'] ) > 0 ? $option['title'] : __( 'Business Hours', 'connections_cnbh' );
			$header              = isset( $option['header'] ) ? $option['header'] : '1';
			$footer              = isset( $option['footer'] ) ? $option['footer'] : '0';
			$dayName             = isset( $option['day_name'] ) ? $option['day_name'] : 'abbrev';
			$showClosedDay       = isset( $option['show_closed_day'] ) ? $option['show_closed_day'] : '1';
			$showClosedPeriod    = isset( $option['show_closed_period'] ) ? $option['show_closed_period'] : '0';
			$show                = isset( $option['show_if_no_hours'] ) ? $option['show_if_no_hours'] : '0';
			$showOpenStatus      = isset( $option['show_open_status'] ) ? $option['show_open_status'] : '1';
			$highlightOpenPeriod = isset( $option['highlight_open_period'] ) ? $option['highlight_open_period'] : '1';

			// Setup the atts to be passed to the method that displays the business hour data.
			$atts = array(
				'header'                => $header == '1' ? TRUE : FALSE,
				'footer'                => $footer == '1' ? TRUE : FALSE,
				'day_name'              => in_array( $dayName, array( 'full', 'abbrev', 'initial' ) ) ? $dayName : 'abbrev',
				'show_closed_day'       => $showClosedDay == '1' ? TRUE : FALSE,
				'show_closed_period'    => $showClosedPeriod == '1' ? TRUE : FALSE,
				'show_if_no_hours'      => $show == '1' ? TRUE : FALSE,
				'show_open_status'      => $showOpenStatus == '1' ? TRUE : FALSE,
				'highlight_open_period' => $highlightOpenPeriod == '1' ? TRUE : FALSE,
				'open_close_separator'  => '&ndash;',
				);

			// Whether or not to show the block if there are no open hours at all during the week.
			if ( ! $atts['show_if_no_hours'] && ! Connections_Business_Hours::hasOpenHours( $metadata ) ) return;

			echo $before_widget;

			echo $before_title . $title . $after_title;

			// Display the business hours.
			Connections_Business_Hours::block( 'cnbh', $metadata, $atts );

			echo $after_widget;

		}

	}

}
