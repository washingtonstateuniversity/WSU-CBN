<?php
function connectionsCSVImportPage()
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

		global $connectionsCSV;

		$form = new cnFormObjects();

		?>
			<div class="wrap">
			<div id="icon-connections" class="icon32">
		        <br />
		    </div>

			<h2>Connections : CSV Import</h2>
		<?php

			$action = isset( $_GET['action'] ) ? $_GET['action'] : 'start' ;

			switch ( $action ) {

				case 'map' :

					check_admin_referer( 'cncsv-nonce-map', 'cncsv_nonce' );

					// Uses the upload.class.php to handle file uploading and image manipulation.
					// GPL PHP upload class from http://www.verot.net/php_class_upload.htm
					//require_once( CN_PATH . '/includes/php_class_upload/class.upload.php');

					$map = connectionsCSVOptions::map();

					//$processCSV = new Upload( $_FILES['csv_file'] );
					$uploadedfile = $_FILES['csv_file'];
					$upload_overrides = array('test_form' => FALSE);
					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
					if ( isset($movefile['file']) ) {
						if ( isset($movefile['file']) ) {

					    	// Grab the file name.
					    	$csvFile = $movefile['file'];

							// Store the user selected values.
							$options = array(
								'file'      => $csvFile,
								'delimiter' => $_POST['csv_delimiter'],
								'enclosure' => $_POST['csv_enclosure'],
								);

							$csv = new cnCSV( $options );

							$attr = array(
								'action'  => 'admin.php?page=connections_csv&action=import',
								'method'  => 'post',
								'enctype' => 'multipart/form-data',
								 );

							$form->open( $attr );
							?>
								<div class="metabox-holder">

									<div class="postbox">

										<h3 class="hndle" style="cursor: auto;"><span>Step 2: Detailed Instructions</span></h3>

										<div class="inside">

											<p>The column on the right are the column headers that were found in the CSV file that was just uploaded.
											   The column on the left are drop downs that contain a list of all the available fields that data can be imported into in Connections.</p>

											<p>For each column select from the drop down the field in Connections the data from the CSV text file should be
											   imported into. You may find that some or all of the drop downs have selections made for you. This because they were
											   auto mapped based on the column header name of the uploaded CSV text file. The full list of column headers that will be
											   auto mapped can be found in the Tips section on <strong>Step 1</strong>.</p>

											<p>For any data in the CSV text file you do not want to be imported change or leave the field drop down set to
											   "Do Not Import".</p>

											<p>NOTE: The CSV text file must have fields for the first name and last name OR organization name and must be mapped to the
											   corresponding fields within Connections, otherwise that row will be skipped when the CSV is being imported.</p>

											<p>After you made all your selections, click the <strong>Import</strong> button. It is recommended that you triple check
											   your mapping selections because after clicking the <strong>Import</strong> button there is no stopping the import process.

										</div>

									</div>

									<div class="postbox">

										<h3 class="hndle" style="cursor: auto;"><span>Map CSV to Connections Fields</span></h3>

										<div class="inside">

											<?php

											echo '<input type="hidden" name="csv_file" value="' . $csvFile . '">';
											echo '<input type="hidden" name="csv_delimiter" value="' . esc_attr( $_POST['csv_delimiter'] ) . '">';
											echo '<input type="hidden" name="csv_enclosure" value="' . esc_attr( $_POST['csv_enclosure'] ) . '">';
											if ( isset( $_POST['csv_import_limit'] ) ) echo '<input type="hidden" name="csv_import_limit" value="' . absint( $_POST['csv_import_limit'] ) . '">';
											if ( isset( $_POST['csv_import_offset'] ) ) echo '<input type="hidden" name="csv_import_offset" value="' . absint( $_POST['csv_import_offset'] ) . '">';
											echo '<input type="hidden" name="csv_row_count" value="' . $csv->count() . '">';
											wp_nonce_field( 'cncsv-nonce-import', 'cncsv_nonce' );

											foreach ( $csv->headers() as $header ) {

												$selected = array_key_exists( strtolower( $header ), $map ) ? $map[ strtolower( $header ) ] : '';
												echo '<input class="cn-field-name" type="text" value="' . esc_attr( $header ) . '" name="csv_map[' . esc_attr( $header ) . ']" READONLY />' . $form->buildSelect('csv_map[' . esc_attr( $header ) . ']', connectionsCSVOptions::fields(), $selected ) . "<br />\n";

												//unset($selected);
											}

											echo '<p class="submit"><input class="button-primary" type="submit" name="submit_parse" value="Import" /></p>';

										echo '</div>'; // END --> .inside



										?>
									</div> <!-- END .postbox  -->
								</div> <!-- END .metabox-holder  -->
							<?php

							$form->close();

					    } else {

					    	echo '<div id="message" class="error"><p><strong>ERROR: </strong>' . $processCSV->error . '</p></div>';
					    }

					} else {

						echo '<div id="message" class="error"><p><strong>ERROR: </strong>CSV failed to upload.</p></div>';
						echo '<div id="return-button"><a href="admin.php?page=connections_csv" class="button button-warning">Return</a></div>';
					}

				break;

				case 'import':

					check_admin_referer( 'cncsv-nonce-import', 'cncsv_nonce' );
					?>

					<!-- Display a message for the user. -->
					<div id="message" class="updated fade">
						<p><strong>NOTICE: </strong>The CSV file is being imported, please be patient. Do not leave or close this page until you receive the "Import Complete" message. Doing so would interupt the import process.</p>
					</div>

					<div class="metabox-holder">

						<div class="postbox">

							<h3 class="hndle" style="cursor: auto;"><span>Progess</span></h3>

							<div class="inside">
								<div class="ui-progressbar" id="cncsv-import-progress"><div class="ui-progressbar-value" id="cncsv-progress-label"></div></div>
							</div>
						</div>
					</div>

					<!-- <p id="cncsv-import-results"></p> // Use only for debugging. -->

					<?php
					// Store the user selected values.
					$options = array(
						'file'      => $_POST['csv_file'],
						'map'       => $_POST['csv_map'],
						'headers'   => array_keys( $_POST['csv_map'] ),
						'delimiter' => $_POST['csv_delimiter'],
						'enclosure' => $_POST['csv_enclosure'],
						'limit'     => isset( $_POST['csv_import_limit'] ) ? $_POST['csv_import_limit'] : 500,
						'offset'    => isset( $_POST['csv_import_offset'] ) ? $_POST['csv_import_offset'] : 0,
						'count'     => $_POST['csv_row_count'],
						);

					// Since we're starting a new import, let ensure the lock flag definitely does not exist.
					delete_option( 'cncsv_import_lock' );

					// Save the options to the WP options table for later use.
					update_option( 'cncsv_import_options', $options );

					// Enqueue jQuery UI Progressbar.
					wp_enqueue_script( 'jquery-ui-progressbar' );

					// Start the import using the AJAX handler.
					add_action( 'admin_footer', array( 'Connections_CSV_Import', 'ajax' ) );

				break;

				case 'start':

					$attr = array(
						'action'  => 'admin.php?page=connections_csv&action=map',
						'method'  => 'post',
						'enctype' => 'multipart/form-data',
						);

					$form->open( $attr );
					?>
						<div class="metabox-holder">

							<div class="postbox">

								<h3 class="hndle" style="cursor: auto;"><span>Basic Instructions</span></h3>

								<div class="inside">

									<p>Importing a CSV into Connections has three primary steps.</p>

									<ol>
										<li>Select a CSV file and upload.</li>
										<li>Map the uploaded CSV file column header to Connections fields and Import.</li>
									</ol>

								</div>

							</div>

							<div class="postbox">

								<h3 class="hndle" style="cursor: auto;"><span>Step 1: Detailed Instructions</span></h3>

								<div class="inside">

									<p>Select the CSV text file and upload. The upload will occur when the "Map" button is clicked.</p>

									<ol>
										<li>Click the <code>Choose</code> button and select the CSV from your desktop that you want to import.</li>
										<li>If needed, change the <code>Field delimited by:</code> and <code>Fields enclosed by:</code> options. In most cases the default values are correct and do not need changed.</li>
										<li>Click the "Map" button to upload the CSV file and to proceed to Step 2.</li>
									</ol>

									<p>See the CSV File Setup section at the bottom of this page for more details about the CSV file requirements and other tips for a successful import.</p>

									<p>Having trouble importing the CSV file, see the Trouble Shooting section at the bottom of this page.</p>

								</div>

							</div>

							<div class="postbox">

								<h3 class="hndle" style="cursor: auto;"><span>Select CSV File</span></h3>

								<div class="inside">

									<input type="file" size="25" name="csv_file" value=""/>

									<fieldset class="cncsv-csv-options">

										<legend>Options</legend>

										<p>

											<label class="desc" for="text_csv_delimiter">Fields delimited by:</label>

											<?php

											echo $form->buildRadio(
												'csv_delimiter',
												'csv_delimiter',
												array(
													'Comma'     => 'comma',
													'Semicolon' => 'semicolin',
													'Tab'       => 'tab',
													'Space'     => 'space'
													),
												'comma'
												);

											?>

										</p>

										<p>

											<label class="desc" for="text_csv_enclosure">Fields enclosed by:</label>

											<?php

											echo $form->buildRadio(
												'csv_enclosure',
												'csv_enclosure',
												array(
													'Double Quote' => 'double',
													'Single Quote' => 'single' ),
												'double'
												);

											?>

										</p>

										<!--<div class="formelementrow">

											<p>
												<label class="desc" for="csv_import_limit">Limit the number of rows to be imported from the uploaded CSV text file.</label>
												<input type="text" id="csv_import_limit" name="csv_import_limit" value="">
											</p>

											<p>
												<label class="desc" for="csv_import_offset">The row from which to start importing from the uploaded CSV text file.</label>
												<input type="text" id="csv_import_offset" name="csv_import_offset" value="">
											</p>

										</div>-->

									</fieldset>

									<?php wp_nonce_field( 'cncsv-nonce-map', 'cncsv_nonce' ); ?>

									<p class="submit"><input class="button-primary" type="submit" name="submit_csv" value="Map" /></p>

								</div>

							</div>


							<div class="postbox">

								<h3 class="hndle" style="cursor: auto;"><span>CSV File Setup</span></h3>

								<div class="inside">

									<h4>Requirements:</h4>

									<p>
										<ul>
											<li>The initial row of the CSV text file must contain the column headers. For example; first name, last name, street, city, state, zip code and so on. The column headers names do not
												need to be specific but should at least be descriptive so you will be able to identify the data contained within the various CSV columns when mapping to the Connections fields.
												However, if you use fields names that are common with CSV text files that are exported from Outlook, Gmail and Thunderbird, Connections will try to auto map those to the Connections fields.</li>
											<li>The CSV text file must have fields for the <strong>first name and last name OR organization name</strong> and must be mapped to the corresponding fields within Connections,
												otherwise that row will be skipped when the CSV is being imported.</li>
										</ul>
									</p>

									<h4>Tips:</h4>

									<p>
										<ul>
											<li>The import process will automatically attempt to identify the entry type. It does this by checking to see if the CSV text file has first and last name or organization name fields
												and that they were mapped to the matching Connections fields. For example, if a row in the CSV text file has a first and a last name that row would import as an <em>Individual</em>.
												Or if a row has only an organizstion name that row would be imported as an <em>Organization</em>. If the row contains both a first / last name and an organization name the entry would
												be imported as an <em>Individual</em>. You can explicitly specify the entry type of each row to be imported as by adding an <em>Entry Type</em> column to the CSV text file and setting each
												row as either <code>individual</code> or <code>organization</code>. In <strong>Step 2</strong> you would map the CSV <em>Entry Type</em> column to the <em>Entry Type</em> field.</li>

											<li>The imported entries will be set to the <em>Public</em> visibility setting by default. You can explicitly set the visibility of each row to be imported by adding a <em>Visibility</em>
												column to the CSV text file each row to one of the following; <code>public</code>, <code>private</code> or <code>unlisted</code>. In <strong>Step 2</strong> you would map the CSV <em>Visibility</em>
												column to the <em>Visibility</em> field.</li>

											<li>
												<p><strong>Auto Mapping.</strong> Here's the list of CSV column header names that they will be mapped to automatically in <strong>Step 2</strong></p>

												<p><a href="<?php echo CNCSV_BASE_URL . '/samples/core-fields-column-header-names.txt'; ?>">Download a text file which contains the CSV file headers.</a></p>

												<p><a href="<?php echo CNCSV_BASE_URL . '/samples/column-headers.xls'; ?>">Download an Excel file which has the column headers already set and is auto mapping ready.</a></p>

												<p><ul>
													<?php

													foreach ( connectionsCSVOptions::fields() as $key => $field ) {

														if ( ! empty( $key ) ) printf('<div><input class="cn-field-name" type="text" value="%1$s" READONLY /></div>', esc_attr( $field ));

														// printf( '<li>%1$s</li>', esc_attr( $field ) );
													}

													?>
												</ul></p>

											</li>
										</ul>
									</p>

								</div>

							</div>

						</div>
					<?php
					$form->close();

				break;
			}

		?>

		</div>

		<?php
	}
}
