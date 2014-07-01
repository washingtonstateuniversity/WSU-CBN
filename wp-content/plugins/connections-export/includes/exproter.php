<?php

$host	=	'localhost';
$user	=	'user';
$pass	=	'pass';
$db		=	'localuser_wrd1';
$file	=	'export';

// You can change this to a non-live settings table...
$settingsTable = "wp_connections_export_settings";

// Open the database, get the export settings, and set up the export fields...
openExport($host, $user, $pass, $db);

// Draw the header (sets up field breakouts, so it must be called if you use breakouts)...
exportHeader();

// Draw the main export data based on settings acquired in the above 2 functions...
exportCells();

// Close the export...
closeExport();

// Connects to the database, loads export settings, defines export delimiters and begins the export process...
function openExport($host, $user, $pass, $db) {
	global	$exportData, $contacts, $exportFields, $settings, $settingsTable, $numContacts;

	// Connect to the database...
	$link = mysql_connect($host, $user, $pass) or die("Cannot connect: ".mysql_error());
	mysql_select_db($db) or die("Cannot connect.");
	// Read the export settings into the export array (the order here is how the fields will appear in the export)...
	$sql = mysql_query("SELECT * FROM ".$settingsTable." WHERE type > -1 ORDER BY field_order");
	while($exportFields[] = mysql_fetch_array($sql));

	// Explode the settings stored in INTERTNAL_SETTINGS.fields into the $settings variable...
	$sql = mysql_query("SELECT * FROM ".$settingsTable." WHERE type = -1");
	$i = mysql_fetch_array($sql);
	parse_str($i['fields'], $settings);

	switch (strtolower($settings['exportType'])) {
		case "htm":
		case "html":
			$settings['outputOpenData']		= "<table border=1>\r\n";
			$settings['outputOpenHeader']	= "\t<tr>\r\n";
			$settings['outputCloseHeader']	= "\t</tr>\r\n";
			$settings['outputOpenRec']		= "\t<tr>\r\n";
			$settings['outputOpenDelim']	= "\t\t<td>";
			$settings['outputCloseDelim']	= "</td>\r\n";
			$settings['outputCloseRec']		= "\t</tr>\r\n";
			$settings['outputCloseData']	= "</table>\r\n";
			$settings['escape']				= '&';
			$settings['escapeWith']			= '&amp;';
			break;
		case "tsv":
			$settings['outputOpenData']		= "";
			$settings['outputOpenHeader']	= "";
			$settings['outputCloseHeader']	= "\r\n";
			$settings['outputOpenRec']		= "";
			$settings['outputOpenDelim']	= "";
			$settings['outputCloseDelim']	= "\t";
			$settings['outputCloseRec']		= "\r\n";
			$settings['outputCloseData']	= "";
			$settings['escape']				= "\t";
			$settings['escapeWith']			= "\t\t";
			break;
		case "csv":
			$settings['outputOpenData']		= "";
			$settings['outputOpenHeader']	= "";
			$settings['outputCloseHeader']	= "\r\n";
			$settings['outputOpenRec']		= "";
			$settings['outputOpenDelim']	= '"';
			$settings['outputCloseDelim']	= '",';
			$settings['outputCloseRec']		= "\r\n";
			$settings['outputCloseData']	= "";
			$settings['escape']				= '"';
			$settings['escapeWith']			= '""';
			break;
	}

	// Setup the main contact connection...
	$contacts = mysql_query("SELECT * FROM wp_connections".($settings['order'] == '' ? '' : ' ORDER BY '.$settings['order']));
	$numContacts = mysql_num_rows($contacts);

	// Write the open data code to the export to get things started...
	$exportData .= $settings['outputOpenData'];
}

// Writes out the data fields...
function exportCells() {
	global $contacts, $exportData, $exportFields, $maxCategories;
	$dataset = '';
	// Go through each contact...
	while ($contact = mysql_fetch_array($contacts)) {
		$rec = '';
		// ...and go through each cell the user wants to export, and match it with the cell in the contact...
		for ($i=0; $i < count($exportFields)-1; $i++) {
			// ...then find out if it's a breakout cell and process it properly...
			switch ($exportFields[$i]['type']) {
				case 1:
					// Export a standard breakout (just list them all in the order requested...
					$rec .= exportBreakoutCell($exportFields[$i], $contact);
					break;
				case 2:
					// Process category (special since taxonomy data must be climbed) table and list all categories in a single cell...
					$line = '';
					$row = mysql_query("SELECT wp_connections_terms.name as value FROM wp_connections_terms JOIN wp_connections_term_relationships ON wp_connections_term_relationships.term_taxonomy_id = wp_connections_terms.term_id WHERE wp_connections_term_relationships.entry_id = ".$contact['id']);
					while ($result = mysql_fetch_array($row)) {
						if ($line != '') $line .= '; ';		// Add a comma to separate multiple entries...
						$line .= $result['value'];
					}
					$rec .= data($line);
					break;
				case 3:
					// Process category table by breaking them out in separate cells...
					// Prepare an empty frame of the category cells...
					for ($j = 0; $j < $maxCategories; $j++) {
						// Make an array filled with empty cells
						$catField[$j] = data('');
					}
					// Now start filling in the empty cells with data...
					$row = mysql_query("SELECT wp_connections_terms.name as value FROM wp_connections_terms JOIN wp_connections_term_relationships ON wp_connections_term_relationships.term_taxonomy_id = wp_connections_terms.term_id WHERE wp_connections_term_relationships.entry_id = ".$contact['id']." ORDER BY wp_connections_terms.name");
					$j = 0;
					while ($result = mysql_fetch_array($row)) {
						$catField[$j] = data($result['value']);
						$j++;
					}
					$x = implode('',$catField);
					$rec .= $x;
					break;
				case 4:
					// Process the category table by breaking them out in separate cells, and also listing the primary parent in the left-most cell...
					// Prepare an empty frame of the category cells...
					for ($j = 0; $j < $maxCategories+1; $j++) {
						// Make an array filled with empty cells
						$catField[$j] = data('');
					}
					// Now start filling in the empty cells with data...
					$row = mysql_query("SELECT wp_connections_terms.name as value, wp_connections_term_taxonomy.parent as parent FROM wp_connections_terms JOIN wp_connections_term_relationships ON wp_connections_term_relationships.term_taxonomy_id = wp_connections_terms.term_id JOIN wp_connections_term_taxonomy ON wp_connections_term_taxonomy.term_taxonomy_id = wp_connections_terms.term_id WHERE wp_connections_term_relationships.entry_id = ".$contact['id']." ORDER BY wp_connections_term_taxonomy.parent");
					$j = 0;
					while ($result = mysql_fetch_array($row)) {
						if ($j == 0) {
							// If the contact has a top-level category...
							if ($result['parent'] == 0) {
								$catField[$j] = data($result['value']);
							} else {
								$catField[$j] = data('None');
								$j++;
								$catField[$j] = data($result['value']);
							}
						} else {
							$catField[$j] = data($result['value']);
						}
						$j++;
					}
					$x = implode('',$catField);
					$rec .= $x;
					break;
				default:			// If no breakout type is defined, only display the cell data...
					$rec .= data($contact[$exportFields[$i]['field']]);
					break;
			}
		}
		$dataset .= exportRecord($rec);
	}
	// Now write the data...
	$exportData .= $dataset;
}

// Draw breakout data...
function exportBreakoutCell($breakout, $contact) {
	global $db;
	$record = '';
	$breakoutFields = explode(";", $breakout['fields']);
	$breakoutTypes = explode(";", $breakout['breakout_types']);

	// Prepare an empty frame of cells...
	for ($i = 0; $i < count($breakoutTypes); $i++) {
		// Go through each type...
		$type = '';
		for ($j = 0; $j < count($breakoutFields); $j++) {
			// Go through each field in each type...
			$type .= data('');
		}
		// Write the type to the type array...
		$breakoutTypeField[$i] = $type;
	}
	// Get the data for this breakout...
	$row = mysql_query("SELECT * FROM wp_connections_".$breakout['table_name']." WHERE wp_connections_".$breakout['table_name'].".entry_id = ".$contact['id']." ORDER BY wp_connections_".$breakout['table_name'].".order");

	// Go through each breakout record from it's table...
	while ($result = mysql_fetch_array($row)) {
		$x +=1;
		// Go through all the types that are supposed to be exported...
		for ($i = 0; $i < count($breakoutTypes); $i++) {
			$type = '';
			// If the type is in our list, we need to export it...
			if ($breakoutTypes[$i] == $result['type']) {
				// Loop through each field and record it...
				for ($j = 0; $j < count($breakoutFields); $j++) {
					$type .= data($result[$breakoutFields[$j]]);
				}
				$breakoutTypeField[$i] = $type;
			}
		}
	}

	if ($breakout['field'] == 'dates') {

	}

	// Return the breakout type field array (imploded)...
	$record = implode('',$breakoutTypeField);
	return $record;

}


// Writes out the header to the $exportData string...
function exportHeader() {
	global $exportData, $exportFields, $settings;
	$header = '';
	for ($i=0; $i < count($exportFields)-1; $i++) {
		// If there is a special type, export it, otherwise, just draw it (and when you draw it, check if settings say the first letter is upper case).
		$header .= ($exportFields[$i]['type'] > 0 ? explodeBreakoutHeader($exportFields[$i]) : ($settings['ucfirst'] == 1 ? data($exportFields[$i]['display_as']) : data(ucfirst($exportFields[$i]['display_as']))) );
	}
	// Now write the header...
	$exportData .= $settings['outputOpenHeader'] . $header . $settings['outputCloseHeader'];
}

// This is called for each breakout field encountered while writing the header, it returns all header cells that needed to be drawn by the breakout.
// It also populates the fields and types array strings if they're empty.
function explodeBreakoutHeader(&$breakout) {
	global $db, $maxCategories;

	// We need a list of fields (i.e. adr_line1, adr_line2, city, state, zip), and a list of types (i.e. work, home, other)

	// If 'table_name' doesn't exist, put the contents of 'field' into it (this step allows for odd things like the dates field/date table)...
	if (empty($breakout['table_name'])) $breakout['table_name'] = $breakout['field'];

	// Get an array of each field we need to use...
	$breakoutFields = explode(";", $breakout['fields']);

	// If no breakout field list was specified, include all fields...
	if (empty($breakout['fields'])) {
		// Get the field names from the SQL schema for the table we're going to use, and plop them into an array...
		$row = mysql_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '".$db."' AND table_name = 'wp_connections_".$breakout['table_name']."';");
		for ($i = 0; $result = mysql_fetch_array($row); $i++) {
			$breakoutFields[$i] = $result[0];
		}
		// Copy the array back into the fields item for use later...
		$breakout['fields'] = implode(";", $breakoutFields);
	}

	// ### Take care of the types...

	// Get an array of each type we need to use...
	$breakoutTypes = explode(";", $breakout['breakout_types']);
	// You can specify you only want home addresses in an export for example, if nothing is specified, get a list of all types from the breakout's table...
	if (empty($breakout['breakout_types'])) {
		// Put the result into an array...
		$row = mysql_query("SELECT DISTINCT type FROM wp_connections_".$breakout['table_name']." ORDER BY type");
		// Put a list of types for this breakout into an array...
		for ($i = 0; $result = mysql_fetch_array($row); $i++) {
			$breakoutTypes[$i] = $result['type'];
		}
		// Copy the array back into the breakout_types item for use later...
		$breakout['breakout_types'] = implode(";", $breakoutTypes);
	}

	// ### When we get here, both the fields and types are checked and ready to go in 2 arrays, we just need to write the breakout...

	// Handle different types...
	switch ($breakout['type']) {
		// Explode all field columns and types...
		case 1:
			foreach ($breakoutTypes as $type) {
				foreach ($breakoutFields as $field) {
					$line .= exportBreakoutHeaderField($breakout, $field, $type);
				}
			}
			break;
		// Joined from another table
		case 2:
			$line .= data($breakout['display_as']);
			break;
		// Breakout a list in the header...
		case 3:
			$rTemp = mysql_query("SELECT id FROM wp_connections");
			// Go through each contact...
			$maxCategories = 0;
			while ($cont = mysql_fetch_array($rTemp)) {
				// And get a count of how many categories it has...
				$rTemp2 = mysql_query("SELECT count(*) as total FROM wp_connections_terms JOIN wp_connections_term_relationships ON wp_connections_term_relationships.term_taxonomy_id = wp_connections_terms.term_id WHERE wp_connections_term_relationships.entry_id = ".$cont['id']);
				$res = mysql_fetch_array($rTemp2);
				// Find the biggest result...
				if ($res['total'] > $maxCategories) $maxCategories = $res['total'];
			}

			// Finally, write a list of fields for each category...
			for ($i = 1; $i < $maxCategories+1; $i++) {
				$line .= data('Category '.$i);
			}
			break;
		// Breakout a list in the header, using primaries in the first column...
		case 4:
			$rTemp = mysql_query("SELECT id FROM wp_connections");
			// Go through each contact...
			$maxCategories = 0;
			while ($cont = mysql_fetch_array($rTemp)) {
				// And get a count of how many categories it has...
				$rTemp2 = mysql_query("SELECT count(*) as total FROM wp_connections_terms JOIN wp_connections_term_relationships ON wp_connections_term_relationships.term_taxonomy_id = wp_connections_terms.term_id WHERE wp_connections_term_relationships.entry_id = ".$cont['id']);
				$res = mysql_fetch_array($rTemp2);
				// Find the biggest result...
				if ($res['total'] > $maxCategories) $maxCategories = $res['total'];
			}

			// Finally, write a list of fields for each category...
			for ($i = 0; $i < $maxCategories+1; $i++) {
				if ($i == 0) {
					$line .= data('Main Category');
				} else {
					$line .= data('Sub-Cat '.$i);
				}
			}
			break;
	}

	return $line;
}

// outputs a breakout header type...
function exportBreakoutHeaderField($breakout, $field, $type) {
	global $settings;
	if ($settings['ucfirst'] == 1) {
		$field = ucfirst($field);
		$type = ucfirst($type);
	}
	// Display the field name based on settings...
	switch (strtolower($breakout['display'])) {
		case 'pre':
			return data($type .' '. $field);
		case 'post':
			return data($field .' '. $type);
		case 'only':
			return data($type);
		default:
			return data($field);
	}
}

// Writes out an entire record to the $exportData string...
function exportRecord($data) {
	global $settings;
	return $settings['outputOpenRec'] . $data . $settings['outputCloseRec'];
}

// Returns an export-ready data item...
function data($data) {
	global $settings;
	$data = str_replace($settings['escape'], $settings['escapeWith'], $data);
	$data = str_replace('&amp;', '&', $data);
	$data = str_replace('&nbsp;', ' ', $data);
	return $settings['outputOpenDelim'] . $data . $settings['outputCloseDelim'];
}

function closeExport() {
	global $settings, $exportData, $file;
	$file = 'export';
	// Write the close statement to the export data stream...
	$exportData .= $settings['outputCloseData'];
	// Process special instructions for closing the export...
	switch (strtolower($settings['exportType'])) {
		case 'htm':
		case 'html':
			// Echo the data...
			echo $exportData;
		case 'tsv':
			$filename = $file."_".date("Y-m-d_H-i",time());
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: tsv" . date("Y-m-d") . ".tsv");
			header( "Content-disposition: filename=".$filename.".tsv");
			print $exportData;
		case 'csv':
			$filename = "export_".date("Y-m-d_H-i",time());
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header( "Content-disposition: filename=".$filename.".csv");
			print $exportData;
	}
}
?>
