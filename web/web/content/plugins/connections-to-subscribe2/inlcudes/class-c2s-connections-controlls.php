<?php
/**
 * Class WNPA_Feed_Item
 *
 * Manage the feed item content type used by the WNPA Syndication plugin.
 */
class C2S_Connections_Controlls {
	public function __construct() {
		add_action( 'init',      array( $this, 'register_post_type'           ), 10 );
		add_action( 'init',      array( $this, 'register_taxonomy_visibility' ), 10 );
		add_action( 'rss2_item', array( $this, 'rss_item_visibility'          ), 10 );
		add_filter( 'wp_dropdown_cats', array( $this, 'selective_taxonomy_dropdown' ), 10, 1 );
	}

	/**
	 * Don't display a parent taxonomy selection drop down when dealing with the
	 * visibility taxonomy.
	 *
	 * @param string $output Current output for dropndown taxonomy list.
	 *
	 * @return string Modified output for dropdown taxonomy list.
	 */
	public function selective_taxonomy_dropdown( $output ) {
		if ( $this->item_content_type !== get_current_screen()->id ) {
			return $output;
		}

		return '';
	}

	/**
	 * Register the feed item post type used to track incoming and outgoing
	 * feed items across multiple publishers.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => 'Feed Items',
			'singular_name'      => 'Feed Item',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Feed Item',
			'edit_item'          => 'Edit Feed Item',
			'new_item'           => 'New Feed Item',
			'all_items'          => 'All Feed Items',
			'view_item'          => 'View Feed Item',
			'search_items'       => 'Search Feed Items',
			'not_found'          => 'No feed items found',
			'not_found_in_trash' => 'No feed items found in Trash',
			'menu_name'          => 'Feed Items'
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'feed-item' ),
			'capability_type'    => 'post',
			'has_archive'        => 'feed-items',
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
		);

		register_post_type( $this->item_content_type, $args );
	}

	/**
	 * Register the taxonomy controlling the visibility of a feed item.
	 */
	public function register_taxonomy_visibility() {
		$labels = array(
			'name'              => 'Visibility',
			'search_items'      => 'Search Visibility',
			'all_items'         => 'All Visibilities',
			'edit_item'         => 'Edit Visibility',
			'update_item'       => 'Update Visibility',
			'add_new_item'      => 'Add New Visibility',
			'new_item_name'     => 'New Visibility Name',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'visibility' ),
		);
		register_taxonomy( $this->item_visibility_taxonomy, array( $this->item_content_type ), $args );
	}

	/**
	 * Output a field in the RSS feed indicating the visibility of each
	 * individual item. Uses the accessRights term available through the
	 * Dublin Core namespace.
	 */
	public function rss_item_visibility() {
		global $post;

		$visibility_terms = wp_get_object_terms( $post->ID, $this->item_visibility_taxonomy );

		if ( empty( $visibility_terms ) ) {
			$visibility = 'public';
		} else {
			$visibility = $visibility_terms[0]->slug;
		}

		?>	<dc:accessRights><?php echo esc_html( $visibility ); ?></dc:accessRights><?php
	}
}
global $c2s_connections_controlls;
$c2s_connections_controlls = new C2S_Connections_Controlls();
