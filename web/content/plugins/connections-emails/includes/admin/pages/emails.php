<?php
function connectionsEmailsPage()
{
	/*
	 * Check whether user can edit roles
	 */
	if ( ! current_user_can('connections_add_entry') ) {

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
		global $wpdb, $connections, $connectionsEmails;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();
	
		$queryVars = array();

		$form = new cnFormObjects();

		?>
			<div class="wrap">
			<div id="icon-connections" class="icon32">
		        <br />
		    </div>

			<h2>Connections : Emails</h2>
		<?php

			$action = isset( $_GET['action'] ) ? $_GET['action'] : 'start' ;

			switch ( $action ) {

				case 'send_email' :
					echo "emails sent";
				break;
				

				case 'set_up_email' :
					
					$attr = array(
						'action'  => 'admin.php?page=connections_emails&action=send_email',
						'method'  => 'post',
						'enctype' => 'multipart/form-data',
						);

					$form->open( $attr );
					wp_nonce_field( 'cnemails-nonce-send_email', 'cncsv_nonce' );

					?><p class="submit"><input class="button-primary" type="submit" name="submit_csv" value="send_email" /></p><?php

					$form->close();
					
				break;



				case 'start':

					$attr = array(
						'action'  => 'admin.php?page=connections_emails&action=set_up_email',
						'method'  => 'post',
						'enctype' => 'multipart/form-data',
						);

					$form->open( $attr );
					wp_nonce_field( 'cnemails-nonce-set_up_email', 'cncsv_nonce' );

					?>
                    Here is your list, select what you want, then preceed to set your email up.
                    
                    <p class="submit"><input class="button-primary" type="submit" name="submit_csv" value="set_up_email" /></p><?php

					$form->close();
			$form = new cnFormObjects();
			$categoryObjects = new cnCategoryObjects();

			$page = $connections->currentUser->getFilterPage( 'manage' );
			$offset = ( $page->current - 1 ) * $page->limit;

			echo '<div class="wrap">';
			echo get_screen_icon( 'connections' );
			echo '<h2>Connections : ' , __( 'Emails', 'connections' ) , '</h2>';

			/*
			 * Check whether user can view the entry list
			 */
			if ( current_user_can( 'connections_manage' ) ) {

				$retrieveAttr['list_type']  = $connections->currentUser->getFilterEntryType();
				$retrieveAttr['category']   = $connections->currentUser->getFilterCategory();

				$retrieveAttr['char']       = isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ? $_GET['cn-char'] : '';
				$retrieveAttr['visibility'] = $connections->currentUser->getFilterVisibility();
				$retrieveAttr['status']     = $connections->currentUser->getFilterStatus();

				$retrieveAttr['limit']      = $page->limit;
				$retrieveAttr['offset']     = $offset;

				if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) $retrieveAttr['search_terms'] = $_GET['s'];

				$results = $connections->retrieve->entries( $retrieveAttr );
				// print_r($connections->lastQuery);
				?>

				<?php if ( current_user_can( 'connections_edit_entry' ) ) { ?>

				<ul class="subsubsub">

					<?php

					$statuses = array(
						'all'      => __( 'All', 'connections' ),
						'approved' => __( 'Approved', 'connections' ),
						'pending'  => __( 'Moderate', 'connections' ),
					);

					foreach ( $statuses as $key => $status ) {

						$subsubsub[] = sprintf( '<li><a%1$shref="%2$s">%3$s</a> <span class="count">(%4$d)</span></li>',
							$instance->currentUser->getFilterStatus() == $key ? ' class="current" ' : ' ',
							$form->tokenURL( add_query_arg( array( 'page' => 'connections_manage', 'cn-action' => 'filter', 'status' => $key ) ), 'filter' ),
							$status,
							cnRetrieve::recordCount( array( 'status' => $key ) )
						 );
					}

					echo implode( ' | ', $subsubsub );

					?>

				</ul>

				<?php } ?>

				<form method="post">
<!--
					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Entries', 'connections' ); ?>:</label>
						<input type="text" id="entry-search-input" name="s" value="<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) echo $_GET['s'] ; ?>" />
						<input type="submit" name="" id="search-submit" class="button" value="<?php _e( 'Search Entries', 'connections' ); ?>"  />
					</p>
-->
					<?php $form->tokenField( 'cn_manage_actions' ); ?>

					<input type="hidden" name="cn-action" value="manage_actions"/>

					<div class="tablenav">

						<div class="alignleft actions">
							<?php
							echo '<select class="postform" id="category" name="category">';
							echo '<option value="-1">' , __( 'Show All Categories', 'connections' ) , '</option>';
							echo $categoryObjects->buildCategoryRow( 'option', $connections->retrieve->categories(), 0, $connections->currentUser->getFilterCategory() );
							echo '</select>';

							echo $form->buildSelect(
								'entry_type',
								array(
									'all' => __( 'Show All Entries', 'connections' ),
									'individual' => __( 'Show Individuals', 'connections' ),
									'organization' => __( 'Show Organizations', 'connections' ),
									'family' => __( 'Show Families', 'connections' )
								),
								$connections->currentUser->getFilterEntryType()
							);

							/*
							 * Builds the visibilty select list base on current user capabilities.
							 */
							if ( current_user_can( 'connections_view_public' ) || $connections->options->getAllowPublic() ) $visibilitySelect['public'] = __( 'Show Public', 'connections' );
							if ( current_user_can( 'connections_view_private' ) ) $visibilitySelect['private'] = __( 'Show Private', 'connections' );
							if ( current_user_can( 'connections_view_unlisted' ) ) $visibilitySelect['unlisted'] = __( 'Show Unlisted', 'connections' );

							if ( isset( $visibilitySelect ) ) {

								/*
								 * Add the 'Show All' option and echo the list.
								 */
								$showAll['all'] = __( 'Show All', 'connections' );
								$visibilitySelect = $showAll + $visibilitySelect;
								echo $form->buildSelect( 'visibility_type', $visibilitySelect, $connections->currentUser->getFilterVisibility() );
							}

							?>

							<input class="button-secondary action" type="submit" name="filter" value="Filter"/>

						</div>

						<div class="tablenav-pages">
							<?php

							echo '<span class="displaying-num">' . sprintf( __( 'Displaying %1$d of %2$d entries.', 'connections' ), $connections->resultCount, $connections->resultCountNoLimit ) . '</span>';

							/*
							 * // START --> Pagination
							 *
							 * Grab the pagination data again incase a filter reset the values
							 * or the user input an invalid number which the retrieve query would have reset.
							 */
							$page = $connections->currentUser->getFilterPage( 'manage' );

							$pageCount = ceil( $connections->resultCountNoLimit / $page->limit );

							if ( $pageCount > 1 ) {

								$pageDisabled   = array();
								$pageFilterURL  = array();
								$pageValue      = array();
								$currentPageURL = add_query_arg( array( 'page' => FALSE , /*'connections_process' => TRUE , 'process' => 'manage' ,*/ 'cn-action' => 'filter' )  );

								$pageValue['first_page']    = 1;
								$pageValue['previous_page'] = ( $page->current - 1 >= 1 ) ? $page->current - 1 : 1;
								$pageValue['next_page']     = ( $page->current + 1 <= $pageCount ) ? $page->current + 1 : $pageCount;
								$pageValue['last_page']     = $pageCount;

								( $page->current > 1 ) ? $pageDisabled['first_page'] = '' : $pageDisabled['first_page'] = ' disabled';
								( $page->current - 1 >= 1 ) ? $pageDisabled['previous_page'] = '' : $pageDisabled['previous_page'] = ' disabled';
								( $page->current + 1 <= $pageCount ) ? $pageDisabled['next_page'] = '' : $pageDisabled['next_page'] = ' disabled';
								( $page->current < $pageCount ) ? $pageDisabled['last_page'] = '' : $pageDisabled['last_page'] = ' disabled';

								/*
								 * Genreate the page link token URL.
								 */
								$pageFilterURL['first_page']    = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['first_page'] ) , $currentPageURL ) , 'filter' );
								$pageFilterURL['previous_page'] = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['previous_page'] ) , $currentPageURL ) , 'filter' );
								$pageFilterURL['next_page']     = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['next_page'] ) , $currentPageURL ) , 'filter' );
								$pageFilterURL['last_page']     = $form->tokenURL( add_query_arg( array( 'pg' => $pageValue['last_page'] ) , $currentPageURL ) , 'filter' );

								echo '<span class="page-navigation" id="page-input">';

								echo '<a href="' . $pageFilterURL['first_page'] . '" title="' . __( 'Go to the first page.', 'connections' ) . '" class="first-page' , $pageDisabled['first_page'] , '">&laquo;</a> ';
								echo '<a href="' . $pageFilterURL['previous_page'] . '" title="' . __( 'Go to the previous page.', 'connections' ) . '" class="prev-page' , $pageDisabled['previous_page'] , '">&lsaquo;</a> ';

								echo '<span class="paging-input"><input type="text" size="2" value="' . $page->current . '" name="pg" title="' . __( 'Current page', 'connections' ) . '" class="current-page"> ' . __( 'of', 'connections' ) . ' <span class="total-pages">' . $pageCount . '</span></span> ';

								echo '<a href="' . $pageFilterURL['next_page'] . '" title="' . __( 'Go to the next page.', 'connections' ) . '" class="next-page' , $pageDisabled['next_page'] , '">&rsaquo;</a> ';
								echo '<a href="' . $pageFilterURL['last_page'] . '" title="' . __( 'Go to the last page.', 'connections' ) . '" class="last-page' , $pageDisabled['last_page'] , '">&raquo;</a>';

								echo '</span>';
							}

							/*
							 * // END --> Pagination
							 */
							?>
						</div>

					</div>
					<div class="clear"></div>
					<div class="tablenav">

						<?php

						if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_delete_entry' ) ) {
							echo '<div class="alignleft actions">';
							/*echo '<select name="action">';
							echo '<option value="" SELECTED>' , __( 'Bulk Actions', 'connections' ) , '</option>';

							$bulkActions = array();

							if ( current_user_can( 'connections_edit_entry' )  || current_user_can( 'connections_edit_entry_moderated' ) ) {
								$bulkActions['unapprove'] = __( 'Unapprove', 'connections' );
								$bulkActions['approve']   = __( 'Approve', 'connections' );
								$bulkActions['public']    = __( 'Set Public', 'connections' );
								$bulkActions['private']   = __( 'Set Private', 'connections' );
								$bulkActions['unlisted']  = __( 'Set Unlisted', 'connections' );
							}

							if ( current_user_can( 'connections_delete_entry' ) ) {
								$bulkActions['delete'] = __( 'Delete', 'connections' );
							}

							$bulkActions = apply_filters( 'cn_manage_bulk_actions', $bulkActions );

							foreach ( $bulkActions as $action => $string ) {
								echo '<option value="', $action, '">', $string, '</option>';
							}

							echo '</select>';*/
							echo '<input class="button-secondary action" type="submit" name="bulk_action" value="' , __( 'Start Email process', 'connections' ) , '" />';
							echo '</div>';
						}
						?>

						<div class="tablenav-pages">
							<?php

							/*
							 * Display the character filter control.
							 */
							echo '<span class="displaying-num">' , __( 'Filter by character:', 'connections' ) , '</span>';
							cnTemplatePart::index( array( 'status' => $connections->currentUser->getFilterStatus(), 'tag' => 'span' ) );
							cnTemplatePart::currentCharacter();
							?>
						</div>
					</div>
					<div class="clear"></div>

			       	<table cellspacing="0" class="widefat connections">
						<thead>
				            <tr>
				                <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
								<!--<th class="col" style="width:10%;"></th>-->
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</thead>
						<tfoot>
				            <tr>
				                <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
								<!--<th class="col" style="width:10%;"></th>-->
								<th scope="col" colspan="2" style="width:40%;"><?php _e( 'Name', 'connections' ); ?></th>
								<th scope="col" style="width:30%;"><?php _e( 'Categories', 'connections' ); ?></th>
								<th scope="col" style="width:20%;"><?php _e( 'Last Modified', 'connections' ); ?></th>
				            </tr>
						</tfoot>
						<tbody>

				<?php

				$previousLetter = '';

				foreach ( $results as $row ) {
					/**
					 *
					 *
					 * @TODO: Use the Output class to show entry details.
					 */
					$entry = new cnvCard( $row );
					$vCard =& $entry;

					$currentLetter = strtoupper( mb_substr( $entry->getSortColumn(), 0, 1 ) );
					if ( $currentLetter != $previousLetter ) {
						$setAnchor = "<a name='$currentLetter'></a>";
						$previousLetter = $currentLetter;
					} else {
						$setAnchor = null;
					}

					/*
					 * Genreate the edit, copy and delete URLs with nonce tokens.
					 */
					$editTokenURL      = $form->tokenURL( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(), 'entry_edit_' . $entry->getId() );
					$copyTokenURL      = $form->tokenURL( 'admin.php?page=connections_manage&cn-action=copy_entry&id=' . $entry->getId(), 'entry_copy_' . $entry->getId() );
					$deleteTokenURL    = $form->tokenURL( 'admin.php?cn-action=delete_entry&id=' . $entry->getId(), 'entry_delete_' . $entry->getId() );
					$approvedTokenURL  = $form->tokenURL( 'admin.php?cn-action=set_status&status=approved&id=' . $entry->getId(), 'entry_status_' . $entry->getId() );
					$unapproveTokenURL = $form->tokenURL( 'admin.php?cn-action=set_status&status=pending&id=' . $entry->getId(), 'entry_status_' . $entry->getId() );

					switch ( $entry->getStatus() ) {
						case 'pending' :
							$statusClass = ' unapproved';
							break;

						case 'approved' :
							$statusClass = ' approved';
							break;

						default:
							$statusClass = '';
							break;
					}

					echo '<tr id="row-' , $entry->getId() , '" class="parent-row' . $statusClass .'">';
					echo "<th class='check-column' scope='row'><input type='checkbox' value='" . $entry->getId() . "' name='id[]'/></th> \n";
					/*echo '<td>';
					$entry->getImage( array( 'image' => 'photo' , 'preset' => 'thumbnail' , 'height' => 54 , 'width' => 80 , 'zc' => 2 , 'fallback' => array( 'type' => 'block' , 'string' => __( 'No Photo Available', 'connections' ) ) ) );
					echo '</td>';*/
					echo '<td  colspan="2">';
					if ( $setAnchor ) echo $setAnchor;
					//echo '<div style="float:right"><a href="#wphead" title="Return to top."><img src="' . CN_URL . 'assets/images/uparrow.gif" /></a></div>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {
						echo '<a class="row-title" title="Edit ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '" href="' . $editTokenURL . '"> ' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</a><br />';
					}
					else {
						echo '<strong>' . $entry->getName( array( 'format' => '%last%, %first%' ) ) . '</strong>';
					}

					/*echo '<div class="row-actions">';
					$rowActions = array();
					$rowEditActions = array();

					$rowActions[] = '<a class="detailsbutton" id="row-' . $entry->getId() . '">' . __( 'Show Details', 'connections' ) . '</a>';
					$rowActions[] = $vCard->download( array( 'anchorText' => __( 'vCard', 'connections' ), 'return' => TRUE ) );
					$rowActions[] = cnURL::permalink( array(
							'slug' => $entry->getSlug(),
							'title' => sprintf( __( 'View %s', 'connections' ) , $entry->getName( array( 'format' => '%first% %last%' ) ) ),
							'text' => __( 'View', 'connections' ),
							'return' => TRUE
						)
					);

					if ( $entry->getStatus() == 'approved' && current_user_can( 'connections_edit_entry' ) ) $rowEditActions[] = '<a class="action unapprove" href="' . $unapproveTokenURL . '" title="' . __( 'Unapprove', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Unapprove', 'connections' ) . '</a>';
					if ( $entry->getStatus() == 'pending' && current_user_can( 'connections_edit_entry' ) ) $rowEditActions[] = '<a class="action approve" href="' . $approvedTokenURL . '" title="' . __( 'Approve', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Approve', 'connections' ) . '</a>';

					if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) $rowEditActions[] = '<a class="editbutton" href="' . $editTokenURL . '" title="' . __( 'Edit', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Edit', 'connections' ) . '</a>';
					if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) $rowEditActions[] = '<a class="copybutton" href="' . $copyTokenURL . '" title="' . __( 'Copy', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Copy', 'connections' ) . '</a>';
					if ( current_user_can( 'connections_delete_entry' ) ) $rowEditActions[] = '<a class="submitdelete" onclick="return confirm(\'You are about to delete this entry. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteTokenURL . '" title="' . __( 'Delete', 'connections' ) . ' ' . $entry->getFullFirstLastName() . '">' . __( 'Delete', 'connections' ) . '</a>';

					if ( ! empty( $rowEditActions ) ) echo implode( ' | ', $rowEditActions ) , '<br/>';
					if ( ! empty( $rowActions ) ) echo implode( ' | ', $rowActions );

					echo '</div>';*/
					echo "</td> \n";
					echo "<td > \n";

					$categories = $entry->getCategory();

					if ( !empty( $categories ) ) {
						$i = 0;

						foreach ( $categories as $category ) {
							/*
							 * Genreate the category link token URL.
							 */
							$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $category->term_id, 'filter' );

							echo '<a href="' . $categoryFilterURL . '">' . $category->name . '</a>';

							$i++;
							if ( count( $categories ) > $i ) echo ', ';
						}

						unset( $i );
					}

					echo "</td> \n";
					echo '<td >';
						echo '<strong>' . __( 'On', 'connections' ) . ':</strong> ' . $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) . '<br />';
						//echo '<strong>' . __( 'By', 'connections' ) . ':</strong> ' . $entry->getEditedBy() . '<br />';
						//echo '<strong>' . __( 'Visibility', 'connections' ) . ':</strong> ' . $entry->displayVisibiltyType() . '<br />';

						$user = $entry->getUser() ? get_userdata( $entry->getUser() ) : FALSE;

						/**
						 * NOTE: WP 3.5 introduced get_edit_user_link()
						 * REF:  http://codex.wordpress.org/Function_Reference/get_edit_user_link
						 *
						 * @TODO Use get_edit_user_link() to simplify this code when WP hits >= 3.9.
						 */
						if ( $user ) {

							if ( get_current_user_id() == $user->ID ) {

								$editUserLink = get_edit_profile_url( $user->ID );

							} else {

								$editUserLink = add_query_arg( 'user_id', $user->ID, self_admin_url( 'user-edit.php' ) );
							}

							echo '<strong>' . __( 'Linked to:', 'connections' ) . '</strong> ' . '<a href="'. $editUserLink .'">'. esc_attr( $user->display_name ) .'</a>';
						}

					echo "</td> \n";
					echo "</tr> \n";
				}
?>
								</tbody>
					        </table>
							</form>


					<script type="text/javascript">
						/* <![CDATA[ */
						(function($){
							$(document).ready(function(){
								$('#doaction, #doaction2').click(function(){
									if ( $('select[name^="action"]').val() == 'delete' ) {
										var m = 'You are about to delete the selected entry(ies).\n  \'Cancel\' to stop, \'OK\' to delete.';
										return showNotice.warn(m);
									}
								});
							});
						})(jQuery);
						/* ]]> */
					</script>
				<?php
			}
			else {
				$connections->setErrorMessage( 'capability_view_entry_list' );
			}

				break;
			}

		?>

		</div>

		<?php
	}
}
