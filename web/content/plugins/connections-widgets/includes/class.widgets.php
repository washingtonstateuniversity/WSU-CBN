<?php

class cnWidgetSearch extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('Simple Search', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_search',
			'Connections : ' . __('Simple Search', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Search Directory', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $wp_rewrite, $connections;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( FALSE );

		$form = '';

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		// Get the directory home page ID.
		$homeID = $connections->settings->get('connections', 'connections_home_page', 'page_id');

		// Get the permalink of the of the directory home.
		$permalink = get_permalink($homeID);

		$title = strlen($title) > 0 ? $title : __('Search Directory', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		if ( $wp_rewrite->using_permalinks() )
		{
			$form .= '<form role="search" method="get" action="' . $permalink . '">';
		}
		else
		{
			$form .= '<form role="search" method="get">';
			$form .= '<input type="hidden" name="p" value="' . $homeID .'">';
		}

		do_action( 'cn_search_form' );

		$form .= $connections->template->search( array( 'show_label' => FALSE , 'return' => TRUE ) );

		$form .= '</form>';

		echo apply_filters('cn_search_form', $form);

		echo $after_widget;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( TRUE );
	}
}

class cnWidgetCategory extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('A list of categories.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_categories',
			'Connections : ' . __('Categories', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Directory Categories', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		$title = strlen($title) > 0 ? $title : __('Directory Categories', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$atts = array(
			'type' => 'link',
			'show_count' => TRUE,
			'show_empty' => TRUE,
			);

		$connections->template->category( $atts );

		echo $after_widget;
	}
}

class cnWidgetRecentlyAdded extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('A list of recently added entries.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_recent_added',
			'Connections : ' . __('Recently Added', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Recently Added', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		$title = strlen($title) > 0 ? $title : __('Recently Added', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		global $connections;

		$atts = array(
			'order_by' => 'date_added|SORT_DESC',
			'limit' => 10
		);

		add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->entries($atts);

		remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty($results) )
		{
			echo '<ul class="cn-widget cn-recent cn-added">';

			foreach ( $results as $row )
			{
				$entry = new cnOutput($row);
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' , $entry->getNameBlock( array( 'link' => TRUE ) ) , '</span></li>';
			}

			echo '</ul>';
		}

		echo $after_widget;
	}
}

class cnWidgetRecentlyModified extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('A list of recently modified entries.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_recent_modified',
			'Connections : ' . __('Recently Modified', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Recently Modified', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		$title = strlen($title) > 0 ? $title : __('Recently Modified', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		global $connections;

		$atts = array(
			'order_by' => 'date_modified|SORT_DESC',
			'limit' => 10
		);

		add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->entries($atts);

		remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty($results) )
		{
			echo '<ul class="cn-widget cn-recent cn-modified">';

			foreach ( $results as $row )
			{
				$entry = new cnOutput($row);
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' , $entry->getNameBlock( array( 'link' => TRUE ) ) , '</span></li>';
			}

			echo '</ul>';
		}

		echo $after_widget;
	}
}

class cnWidgetUpcomingBirthdays extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct() {

		$options = array(
			'description' => __('A list of entries with upcoming birthdays.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_upcoming_birthdays',
			'Connections : ' . __('Upcoming Birthdays', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update( $new_instance, $old_instance ) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form( $instance ) {

		$title       = isset( $instance['title'] ) && strlen( $instance['title'] ) > 0 ? esc_attr( $instance['title'] ) : __('Upcoming Birthdays', 'connections_widgets');
		$date_format = isset( $instance['date_format'] ) && strlen( $instance['date_format'] ) > 0 ? esc_attr( $instance['date_format'] ) : esc_attr( get_option('date_format') );

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<label for="<?php echo $this->get_field_id('date_format');?>"><?php _e('Date Format:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('date_format');?>" name="<?php echo $this->get_field_name('date_format');?>" type="text" value="<?php echo $date_format?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance) {
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract( $args );

		// Extract your options
		extract( $instance );

		$title       = isset( $title ) && strlen( $title ) > 0 ? $title : __('Upcoming Birthdays', 'connections_widgets');
		$date_format = isset( $date_format ) && strlen( $date_format ) > 0 ? esc_attr( $date_format ) : esc_attr( get_option('date_format') );

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$atts = array(
			'type'                  => 'birthday',
			'days'                  => 30,
			'today'                 => FALSE,
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		//add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->upcoming($atts);

		//remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty( $results ) ) {

			echo '<ul class="cn-widget cn-birthday-upcoming">';

			foreach ( $results as $row ) {

				$entry = new cnOutput( $row );
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' ,
					$entry->getNameBlock( array( 'link' => TRUE ) ) , '</span>' ,
					$entry->getBirthdayBlock( NULL , array ( 'format' => '%date%', 'date_format' => $date_format, 'return' => TRUE ) ) ,
					'</li>';
			}

			echo '</ul>';

		} else {

			echo '<ul class="cn-widget cn-birthday-upcoming">';
				echo '<li class="cat-item cn-entry"><span class="cn-widget cn-no-results">' , __('No Upcoming Birthdays', 'connections_widgets' ) , '</li>';
			echo '</ul>';
		}

		echo $after_widget;
	}
}

class cnWidgetTodaysBirthdays extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('A list of entries with birthdays today.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_todays_birthdays',
			'Connections : ' . __('Birthdays Today', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Birthdays Today', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		$title = strlen($title) > 0 ? $title : __('Birthdays Today', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		global $connections;

		$atts = array(
			'type' => 'birthday',
			'days' => 0,
			'today' => TRUE,
			'allow_public_override' => FALSE,
			'private_override' => FALSE
		);

		//add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->upcoming($atts);

		//remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty($results) )
		{
			echo '<ul class="cn-widget cn-birthday-today">';

			foreach ( $results as $row )
			{
				$entry = new cnOutput($row);
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' , $entry->getNameBlock( array( 'link' => TRUE ) ) , '</span></li>';
			}

			echo '</ul>';
		} else {
			echo '<ul class="cn-widget cn-birthday-today">';
				echo '<li class="cat-item cn-entry"><span class="cn-widget cn-no-results">' , __('No Birthdays Today', 'connections_widgets' ) , '</li>';
			echo '</ul>';
		}

		echo $after_widget;
	}
}

class cnWidgetUpcomingAnniversaries extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct() {

		$options = array(
			'description' => __('A list of entries with upcoming anniversaries.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_upcoming_anniversaries',
			'Connections : ' . __('Upcoming Anniversaries', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance) {

		$title       = isset( $instance['title'] ) && strlen( $instance['title'] ) > 0 ? esc_attr( $instance['title'] ) : __('Upcoming Anniversaries', 'connections_widgets');
		$date_format = isset( $instance['date_format'] ) && strlen( $instance['date_format'] ) > 0 ? esc_attr( $instance['date_format'] ) : esc_attr( get_option('date_format') );

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<label for="<?php echo $this->get_field_id('date_format');?>"><?php _e('Date Format:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('date_format');?>" name="<?php echo $this->get_field_name('date_format');?>" type="text" value="<?php echo $date_format?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget( $args, $instance ) {
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract( $args );

		// Extract your options
		extract( $instance );

		$title       = isset( $title ) && strlen( $title ) > 0 ? $title : __( 'Upcoming Anniversaries' , 'connections_widgets' );
		$date_format = isset( $date_format ) && strlen( $date_format ) > 0 ? esc_attr( $date_format ) : esc_attr( get_option('date_format') );

		echo $before_widget;

		echo $before_title . $title . $after_title;

		$atts = array(
			'type'                  => 'anniversary',
			'days'                  => 30,
			'today'                 => FALSE,
			'allow_public_override' => FALSE,
			'private_override'      => FALSE
		);

		//add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->upcoming( $atts );

		//remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty( $results ) ) {

			echo '<ul class="cn-widget cn-anniversaries-upcoming">';

			foreach ( $results as $row ) {

				$entry = new cnOutput( $row );
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' ,
					$entry->getNameBlock( array( 'link' => TRUE ) ) , '</span>' ,
					$entry->getAnniversaryBlock( NULL , array ( 'format' => '%date%', 'date_format' => $date_format, 'return' => TRUE ) ) ,
					'</li>';
			}

			echo '</ul>';

		} else {

			echo '<ul class="cn-widget cn-anniversary-upcoming">';
				echo '<li class="cat-item cn-entry"><span class="cn-widget cn-no-results">' , __('No Upcoming Anniversaries', 'connections_widgets' ) , '</li>';
			echo '</ul>';
		}

		echo $after_widget;
	}
}

class cnWidgetTodaysAnniversaries extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct()
	{
		$options = array(
			'description' => __('A list of entries with anniversaries today.', 'connections_widgets' )
		);

		parent::__construct(
			'cnw_todays_anniversaries',
			'Connections : ' . __('Anniversaries Today', 'connections_widgets' ),
			$options
		);
	}

	/**
	* Logic for handling updates from the widget form.
	*
	* @param array $new_instance
	* @param array $old_instance
	* @return array
	*/
	public function update($new_instance, $old_instance) {
		// Insert update logic here, and check that all the values in $new_instance are valid for your particular widget

		return $new_instance;
	}

	/**
	* Function for handling the widget control in admin panel.
	*
	* @param array $instance
	* @return void
	*/
	public function form($instance)
	{

		$title = isset($instance['title']) && strlen($instance['title']) > 0 ? esc_attr($instance['title']) : __('Anniversaries Today', 'connections_widgets');

		?>

		<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'connections_widgets') ?></label><br/>
		<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title?>" /><br/>

		<?php
	}

	/**
	* Function for displaying the widget on the page.
	*
	* @param array $args
	* @param array $instance
	* @return void
	*/
	public function widget($args, $instance)
	{
		global $connections;

		// Extract $before_widget, $after_widget, $before_title and $after_title
		extract($args);

		// Extract your options
		extract($instance);

		$title = strlen($title) > 0 ? $title : __('Anniversaries Today', 'connections_widgets');

		echo $before_widget;

		echo $before_title . $title . $after_title;

		global $connections;

		$atts = array(
			'type' => 'anniversary',
			'days' => 0,
			'today' => TRUE,
			'allow_public_override' => FALSE,
			'private_override' => FALSE
		);

		//add_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		$results = $connections->retrieve->upcoming($atts);

		//remove_filter( 'cn_list_results', array('cnRetrieve', 'removeUnknownDateAdded'), 9 );

		if ( ! empty($results) )
		{
			echo '<ul class="cn-widget cn-anniversaries-today">';

			foreach ( $results as $row )
			{
				$entry = new cnOutput($row);
				$entry->directoryHome( array( 'force_home' => TRUE ) );

				echo '<li class="cat-item cat-item-' . $entry->getId() . ' cn-entry"><span class="cn-widget cn-name">' , $entry->getNameBlock( array( 'link' => TRUE ) ) , '</span></li>';
			}

			echo '</ul>';
		} else {
			echo '<ul class="cn-widget cn-anniversary-today">';
				echo '<li class="cat-item cn-entry"><span class="cn-widget cn-no-results">' , __('No Anniversaries Today', 'connections_widgets' ) , '</li>';
			echo '</ul>';
		}

		echo $after_widget;
	}
}
