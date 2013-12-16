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

		global $connectionsEmails;

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
						'action'  => 'admin.php?page=connections_email&action=send_email',
						'method'  => 'post',
						'enctype' => 'multipart/form-data',
						);

					$form->open( $attr );
					wp_nonce_field( 'cncsv-nonce-map', 'cncsv_nonce' );

					?><p class="submit"><input class="button-primary" type="submit" name="submit_csv" value="send_email" /></p><?php

					$form->close();
					
				break;



				case 'start':

					$attr = array(
						'action'  => 'admin.php?page=connections_email&action=set_up_email',
						'method'  => 'post',
						'enctype' => 'multipart/form-data',
						);

					$form->open( $attr );
					wp_nonce_field( 'cncsv-nonce-map', 'cncsv_nonce' );

					?>
                    Here is your list, select what you want, then preceed to set your email up.
                    
                    <p class="submit"><input class="button-primary" type="submit" name="submit_csv" value="set_up_email" /></p><?php

					$form->close();

				break;
			}

		?>

		</div>

		<?php
	}
}
