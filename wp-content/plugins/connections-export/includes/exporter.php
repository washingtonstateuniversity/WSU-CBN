<?php


$file	=	'export';

// You can change this to a non-live settings table...
$settingsTable = "wp_connections_export_settings";





// Open the database, get the export settings, and set up the export fields...
openExport();

// Draw the header (sets up field breakouts, so it must be called if you use breakouts)...
exportHeader();

// Draw the main export data based on settings acquired in the above 2 functions...
exportCells();

// Close the export...
closeExport();

// Connects to the database, loads export settings, defines export delimiters and begins the export process...
function openExport() {
	
/*
			define( 'CN_ENTRY_TABLE', $prefix . 'connections' );
			define( 'CN_ENTRY_ADDRESS_TABLE', $prefix . 'connections_address' );
			define( 'CN_ENTRY_PHONE_TABLE', $prefix . 'connections_phone' );
			define( 'CN_ENTRY_EMAIL_TABLE', $prefix . 'connections_email' );
			define( 'CN_ENTRY_MESSENGER_TABLE', $prefix . 'connections_messenger' );
			define( 'CN_ENTRY_SOCIAL_TABLE', $prefix . 'connections_social' );
			define( 'CN_ENTRY_LINK_TABLE', $prefix . 'connections_link' );
			define( 'CN_ENTRY_DATE_TABLE', $prefix . 'connections_date' );

			define( 'CN_ENTRY_TABLE_META', $prefix . 'connections_meta' );
			define( 'CN_TERMS_TABLE', $prefix . 'connections_terms' );
			define( 'CN_TERM_TAXONOMY_TABLE', $prefix . 'connections_term_taxonomy' );
			define( 'CN_TERM_RELATIONSHIP_TABLE', $prefix . 'connections_term_relationships' );
			*/	
	
	global	$wpdb, $exportData, $contacts, $exportFields, $settings, $settingsTable, $numContacts,$skip;


$skip=array(
	"options",
	"slug",
	"ts",
	"family_name",
	"honorific_prefix",
	"first_name",
	"middle_name",
	"last_name",
	"honorific_suffix",
	"title",
	'anniversary',
	'birthday',
	'im',
	'social',
	'phone_numbers::visibility',
	'phone_numbers::type',
	'addresses::type',
	'addresses::visibility',
	'addresses::line_2',
	'addresses::line_3',
	'addresses::latitude',
	'addresses::longitude',
	'addresses::id',
	'email::type',
	'email::visibility',
	'email::id',
	'email::order',
	'email::preferred',
	'links::title',
	'links::url',
	'links::target',
	'links::follow',
	'links::id',
	'links::order',
	'links::preferred',
	'links::image',
	'links::logo',
	'dates',
	'phone_numbers::id',
	'phone_numbers::order',
	'phone_numbers::preferred',
	'email',
	'links',
	'phone_numbers'
);



	$contacts = $wpdb->get_results( "SELECT * FROM ".CN_ENTRY_TABLE, ARRAY_A );
	$numContacts = count($contacts);




	$exportFields=$wpdb->get_results(
		'SELECT * FROM '.$settingsTable.' WHERE type > -1 ORDER BY field_order',
		ARRAY_A
	);
	
	
	
	
	
	
/*


	$i=$wpdb->get_results(
		'SELECT * FROM '.$settingsTable.' WHERE type = -1',
		ARRAY_A
	);
	parse_str($i['fields'], $settings);*/
	
	$settings['exportType']="csv";
	
	

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
			$settings['dbl_escape']				= '';
			$settings['dbl_escapeWith']			= '';
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
			$settings['dbl_escape']				= '';
			$settings['dbl_escapeWith']			= '';
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
			
			$settings['dbl_escape']				= '"';
			$settings['dbl_escapeWith']			= '""';
			$settings['escape']				= "'";
			$settings['escapeWith']			= "''";
			break;
	}


	// Write the open data code to the export to get things started...
	$exportData .= $settings['outputOpenData'];
}
function isJson($string) {
    if ( !is_string( $string ) ){
        return false;
	}
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

// Writes out the data fields...
function create_header() {
	global $settings,$contacts, $exportData, $exportFields, $maxCategories, $wpdb, $header,$skip;

	$dataset = '';
	
	
	$head=array();
	// Go through each contact...
	foreach($contacts as $contact) {
		
		foreach($contact as $key=>$part){
			if(!isset($head[$key]) && !in_array($key,$skip)){
				
				if(is_serialized($part)){
					$serial_value=@unserialize($part);
					foreach($serial_value as $opkey=>$op){
						foreach($op as $skey=>$spart){
							$true_value=($spart=='0')?'false':($spart=='1')?'true':$spart;
							$contact[]=$true_value;	
							if(!isset($head[$key."::".$skey]) && !in_array($key."::".$skey,$skip)){
								$head[$key."::".$skey]=$key."::".$skey;
							}
						}
					}
				}else{
					$head[$key]=$key;
				}
						
				
				
				
			}
		}		
		$meta = $wpdb->get_results( "SELECT * FROM ".CN_ENTRY_TABLE_META." WHERE `entry_id`=".$contact['id'], ARRAY_A );
		
		
		foreach($meta as $part){
			$key=$part['meta_key'];
			$value=$part['meta_value'];
			if(isJson($value)){
				$json_value = json_decode($value);
				foreach($json_value as $jkey=>$jpart){
					$true_value=($jpart=='0')?'false':($jpart=='1')?'true':$jpart;
					$contact[]=$true_value;	
					
					if(!isset($head[$key."::".$jkey]) && !in_array($key."::".$jkey,$skip)){
						$head[$key."::".$jkey]=$key."::".$jkey;
					}
					
					
				}
			}else{
				if(!isset($head[$key]) && !in_array($key,$skip)){
					$head[$key]=$key;
				}
			}
		}
		

		
	}
	
	$rec = '';
	$header=$head;
	foreach($head as $part){
		$rec.=$settings['outputOpenDelim'].$part.$settings['outputCloseDelim'];
	}
	$dataset .= exportRecord($rec);
	
	
	// Now write the data...
	$exportData .= $dataset;
}



// Writes out the data fields...
function exportCells() {
	global $settings,$contacts, $exportData, $exportFields, $maxCategories, $wpdb, $header,$skip;
	create_header();
	//var_dump($header);
	$dataset = '';
	// Go through each contact...
	$row=array();
	foreach($contacts as $contact) {
		$contact_entry=array();
		$meta = $wpdb->get_results( "SELECT * FROM ".CN_ENTRY_TABLE_META." WHERE `entry_id`=".$contact['id'], ARRAY_A );

		foreach($meta as $part){
			$key=$part['meta_key'];
			$value=$part['meta_value'];
			if(isJson($value)){
				$json_value = json_decode($value);
				foreach($json_value as $jkey=>$jpart){
					$true_value=($jpart=='0')?'false':($jpart=='1')?'true':$jpart;
					$contact[$key."::".$jkey]=$true_value;
				}
			}else{
				$contact[$key]=$value;
			}
		}

		$rec = '';
		foreach($contact as $key=>$part){
			$value=part_filter($key,$part);
			
			if(is_serialized($value)){
				$serial_value=@unserialize($value);
				foreach($serial_value as $opkey=>$op){
					foreach($op as $skey=>$spart){
						$true_value=($spart=='0')?'false':($spart=='1')?'true':$spart;
						if(!in_array($key."::".$skey,$skip)){
							$contact_entry[$key."::".$skey]=$true_value;
						}
					}
				}
			}else{
				if(!in_array($key,$skip)){
					$contact_entry[$key]=$value;
				}
			}
		}
		$row[]=$contact_entry;
	}
	foreach($row as $contact_entry){
		$rec='';
		foreach($header as $col){
			$rec.=data($contact_entry[$col]);
		}
		$dataset .= exportRecord($rec);
	}
	//var_dump($dataset);die();
	
	
	// Now write the data...
	$exportData .= $dataset;
}

function part_filter($key,$part){
	
	switch (strtolower($key)) {
		case "date_added":
			$part=date('Y-m-d H:i:s',$part);
			break;
		case 'user':
		case "edited_by":
		case "owner":
		case "added_by":
		  	$user_info = get_userdata($part);
			if($user_info){
  				$part = $user_info->user_login;
			}
			break;
	}
	
	
	
	return $part;
}

// Writes out the header to the $exportData string...
function exportHeader() {
	global $exportData, $exportFields, $settings, $wpdb;
	$header = '';
	// Now write the header...
	$exportData .= $settings['outputOpenHeader'] . $header . $settings['outputCloseHeader'];
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
	$data = str_replace($settings['dbl_escape'], $settings['dbl_escapeWith'], $data);
	$data = str_replace('&amp;', '&', $data);
	$data = str_replace('&nbsp;', ' ', $data);
	return $settings['outputOpenDelim'] . $data . $settings['outputCloseDelim'];
}

function closeExport() {
	global $settings, $exportData, $file;
	$file = 'export';
	
	//var_dump($exportData);die();
	
	
	
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
	exit();
}
?>
