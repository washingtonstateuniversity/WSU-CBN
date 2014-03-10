<?php
add_filter('cn_manage_bulk_actions', 'cnCSVAddExportBulkAction');
add_action('cncsv_processes', 'cnCSVExport');

function cnCSVAddExportBulkAction($bulkActions)
{
	unset( $bulkActions['delete'] );
	
	$bulkActions['export_csv'] = 'Export CSV';
	$bulkActions['delete'] = 'Delete';
	
	return $bulkActions;
}

function cnCSVExport()
{
	if ( isset($_GET['page']) && $_GET['page'] === 'connections' )
	{
		if ( isset( $_GET['action']) && $_GET['action'] === 'do' )
		{
			if ( isset($_POST['action']) && $_POST['action'] === 'export_csv' )
			{
				if (empty($_POST['entry'])) return;
				
				$form = new cnFormObjects();
				check_admin_referer($form->getNonce('bulk_action'), '_cn_wpnonce');
				
				global $connections;
				
				$atts['id'] = implode( ',', $_POST['entry'] );
				$results = $connections->retrieve->entries($atts);
				
				foreach ( $results as $key => $data )
				{
					$entry = new cnEntry($data);
					
					$csvData[$key][] = $entry->getFirstName();
					$csvData[$key][] = $entry->getMiddleName();
					$csvData[$key][] = $entry->getLastName();
					$csvData[$key][] = $entry->getTitle();
					$csvData[$key][] = $entry->getOrganization();
					$csvData[$key][] = $entry->getDepartment();
					
					$address = $entry->getAddresses();
					
					$csvData[$key][] = $address[0]->line_one;
					$csvData[$key][] = $address[0]->line_two;
					$csvData[$key][] = $address[0]->city;
					$csvData[$key][] = $address[0]->state;
					$csvData[$key][] = $address[0]->zipcode;
					$csvData[$key][] = $address[0]->country;
					
					$phoneNumbers = $entry->getPhoneNumbers();
					
					/*
					 * Convert the parsed phone number data to an array of objects that can be
					 * used to populate the input fields.
					 */
					$phoneNumberMap = array('homephone', 'homefax', 'workphone', 'workfax', 'cellphone');
					
					foreach ( $phoneNumberMap as $phoneMapType )
					{
						$match = FALSE;
						
						foreach ( (array) $phoneNumbers as $phone )
						{
							if ( $phoneMapType == $phone->type )
							{
								$csvData[$key][] = $phone->number;
								$match = TRUE;
							}
						}
						
						if ( $match == FALSE ) $csvData[$key][] = '';
					}
					
					$emailAddresses = $entry->getEmailAddresses();
					
					/*
					 * Convert the parsed email data to an array of objects that can be
					 * used to populate the input fields.
					 */
					$emailMap = array('personal', 'work');
					
					foreach ( $emailMap as $emailMapType )
					{
						$match = FALSE;
						
						foreach ( (array) $emailAddresses as $email )
						{
							if ( $emailMapType == $email->type )
							{
								$csvData[$key][] = $email->address;
								$match = TRUE;
							}
						}
						
						if ( $match == FALSE ) $csvData[$key][] = '';
					}
					
					$website = $entry->getWebsites();
					
					$csvData[$key][] = $website[0]->url;
				}
				
				$csv = new parseCSV();
				$csv->save(CNCSV_BASE_PATH . '/connections.csv', $csvData);
				
				add_action('admin_notices', create_function('', 'echo \'<div id="message" class="updated fade"><p><a href="' . CNCSV_BASE_URL . '/connections.csv' . '">Download CSV</a></p></div>\';') );
			}
		}
	}
}
?>