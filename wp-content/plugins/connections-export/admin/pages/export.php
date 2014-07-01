<?php
function connectionsExportPage() {
    if (!current_user_can('connections_add_entry')) {
        wp_die('<p id="error-page" style="-moz-background-clip:border;
				-moz-border-radius:11px;
				background:#FFFFFF none repeat scroll 0 0;
				border:1px solid #DFDFDF;
				color:#333333;
				display:block;
				font-size:12px;
				line-height:18px;
				margin:25px auto 20px;
				padding:1em 2em;
				text-align:center;
				width:700px">You do not have sufficient permissions to access this page.</p>');
    } else {
        global $wpdb, $connections, $connectionsExports;
        // Grab an instance of the Connections object.
        $instance  = Connections_Directory();
        $queryVars = array();
        $form      = new cnFormObjects();
?>

<div class="wrap">
  <div id="icon-connections" class="icon32"> <br />
  </div>
  <?php
        $action = isset($_REQUEST['action']) && !isset($_REQUEST['filter']) ? $_REQUEST['action'] : 'start';
        switch ($action) {
            case 'start_exporter':
						global $post,$connections, $wpdb;
						require_once(dirname( __FILE__ ) . '/includes/exporter.php');//temp correct later
                break;
            case 'set_up_email':

                break;
            case 'start':
                $form            = new cnFormObjects();
                $categoryObjects = new cnCategoryObjects();
                $page            = $connections->currentUser->getFilterPage('connections_export');
                $offset          = ($page->current - 1) * $page->limit;
                echo '<div class="wrap">';
                echo get_screen_icon('connections');
                echo '<h2>Connections : ', __('Export', 'connections'), '</h2>';

				$exportURL = $form->tokenURL('admin.php?page=connections_export&start_export', 'filter');
				echo '<a href="' . $exportURL . '" class="button large blue full">Export Data</a>';
				echo '<p>As of this moment, the export will be a dump of all the main information, and the meta.  Expanded data will come soon.</p>';
                break;
        }
?>
</div>
<?php
    }
}

