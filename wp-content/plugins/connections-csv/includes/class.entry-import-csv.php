<?php

/**
 * Implements the methods import entries via a supplied CSV file.
 *
 * @package     Connections
 * @subpackage  CSV Import
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnCSV {

	/**
	 * An instance of the parseCSV PHP library.
	 *
	 * @access  private
	 * @since  1.0
	 * @var object
	 */
	private $csv;

	/**
	 * Stores the parsed CSV data mapped to the available fields in Connections.
	 *
	 * @access  private
	 * @since 1.0
	 * @var array
	 */
	private $results = array();

	/**
	 * Runtime local cache of objects queried from the db.
	 * Currently being used to store categories that have been queried
	 * so they data does not need to be quired mulitple times.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $cache = array();

	public function __construct( $atts ) {

		$this->loadDependencies();

		$defaults = array(
			'file'      => '',
			'delimiter' => 'comma',
			'enclosure' => 'double',
			'offset'    => NULL,
			'limit'     => NULL,
			'headers'   => array(),
			);

		$atts = wp_parse_args( $atts, $defaults );

		if ( empty( $atts['file'] ) ) return FALSE;

		$this->csv = new parseCSV();

		switch ( $atts['delimiter'] ) {

			case 'comma':
				$this->csv->delimiter = ',';
				break;

			case 'semicolin':
				$this->csv->delimiter = ';';
				break;

			case 'tab':
				$this->csv->delimiter = "\t";
				break;

			case 'space':
				$this->csv->delimiter = ' ';
				break;

			default:
				$this->csv->delimiter = ',';
				break;
		}

		switch ( $atts['enclosure'] ) {

			case 'double':
				$this->csv->enclosure = '"';
				break;

			case 'single':
				$this->csv->enclosure = '\'';
				break;

			default:
				$this->csv->enclosure = '"';
				break;
		}

		// Add option for user to set character encoding, by language perhaps?
		// @url  http://en.wikipedia.org/wiki/ISO/IEC_8859
		// Sample to allow importing of Hebrew
		// $csv->encoding( 'ISO-8859-8', 'UTF-8' );

		// There seems to be a bug when using the parseCSV offset option.
		// The previous row is used as the header names rather the the first row.
		// Luckily it provides a method to override the column header names.
		// Parse the file to get the column header names and set the header override array.
		// $this->csv->parse( $atts['file'], NULL, 1 );
		// $this->csv->fields = $this->headers();
		if ( ! empty( $atts['headers'] ) ) $this->csv->fields = $atts['headers'];

		// Set the offset and limit.
		$offset = ! empty( $atts['offset'] ) ? absint( $atts['offset'] ) + 1 : NULL;
		$limit  = ! empty( $atts['limit']  ) ? absint( $atts['limit'] ) : NULL;

		// Parse the file for reals.
		$this->csv->parse( $atts['file'], $offset, $limit );
	}

	/**
	 * Load the parseCSV PHP library.
	 *
	 * @access  private
	 * @since  1.0
	 * @return void
	 */
	private function loadDependencies() {

		if ( ! class_exists('parseCSV') )
			require_once( CNCSV_BASE_PATH . '/includes/libraries/parseCSV/class.parsecsv.php' );
	}

	/**
	 * Returns the CSV column header.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Two dimensional array of the CSV headers.
	 */
	public function headers() {

		return $this->csv->titles;
	}

	/**
	 * Returns the CSV record count.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function count() {

		return count( $this->csv->data );
	}

	/**
	 * Returns the CSV data, sans the column headers.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Two dimensional array of the CSV rows.
	 */
	public function data() {

		return $this->csv->data;
	}

	private function map( $csvMap ) {

		// Map the CSV data by row to the Connections field defined by the supplied map $csvMap.
		// This does create the array of objects used for the actual import.
		foreach ( $this->csv->data as $key => $row ) {

			// PHP error fixing...
			$entry = new stdClass();

			foreach ( $csvMap as $cvsKey => $cnField ) {

				// Filter out the CSV columns that were marked as "Do Not Import".
				if ( empty( $cnField ) ) continue;

				if ( array_key_exists( $cvsKey, $row ) ) $entry->{ $cnField } = $row[ $cvsKey ];
			}

			$this->results[ $key ] = $entry;
			unset( $entry );
		}

		return count( $this->results );
	}

	public function import( $key ) {

		$recordCount = 0;

		// Map the CSV rows to Connections field by user supplied key.
		$this->map( $key );

		foreach ( $this->results as $key => $row ) {

			$doUpdate = FALSE;

			@set_time_limit(180);

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			// $date  = new cnDate();
			$entry = new cnEntry();

			// Let's see if we're performing an update.
			if ( isset( $row->entry_id ) && ! empty( $row->entry_id ) ) {

				$entry->set( absint( $row->entry_id ) );

				$doUpdate = TRUE;
			}

			/*
			 * Attempt to determine the entry type by the data supplied in the CSV
			 * If the type is not supplied, it must be either these values:
			 *
			 * 		* individual
			 * 		* organization
			 *
			 * If it can not be determined by the supplied data, unset the row.
			 */
			if ( ( isset( $row->entry_type ) && strtolower( $row->entry_type ) == 'individual' ) ||
				( ( isset( $row->first_name ) && ! empty( $row->first_name ) ) && ( isset( $row->last_name )  && ! empty( $row->last_name ) ) ) ) {

				$entry->setEntryType( 'individual' );

			} elseif ( ( isset( $row->entry_type ) && strtolower( $row->entry_type ) == 'organization' ) || ( isset( $row->organization ) && ! empty( $row->organization ) ) ) {

				$entry->setEntryType( 'organization' );

			} else {

				$recordCount++;

				// TODO:  A log should be kept of any rows that were dropped.
				unset( $this->results[ $key ] );
			}
			// $entry->setEntryType( $row->entry_type );

			/*
			 * Determine the entry's visibility if supplied in the CSV file.
			 * The type supplied must be:
			 *
			 * 		* public
			 * 		* private
			 * 		* unlisted
			 *
			 * If no value has been supplied, default to public.
			 */
			if ( isset( $row->visibility ) || ! empty( $row->visibility ) ) {

				$visibility = in_array( strtolower( $row->visibility ) , array( 'public' , 'private' , 'unlisted' ) ) ? strtolower( $row->visibility ) : 'public';
				$entry->setVisibility( $visibility );

			} else {

				$entry->setVisibility( 'public' );
			}

			// Set the status to 'approved'.
			$entry->setStatus('approved');

			/*
			 * Set the name.
			 */
			( isset($row->honorific_prefix) || ! empty($row->honorific_prefix) ) ? $entry->setHonorificPrefix( $row->honorific_prefix ) : $entry->setHonorificPrefix( '' );
			( isset($row->first_name) || ! empty($row->first_name) ) ? $entry->setFirstName( $row->first_name ) : $entry->setFirstName( '' );
			( isset($row->middle_name) || ! empty($row->middle_name) ) ? $entry->setMiddleName( $row->middle_name ) : $entry->setMiddleName( '' );
			( isset($row->last_name) || ! empty($row->last_name) ) ? $entry->setLastName( $row->last_name ) : $entry->setLastName( '' );
			( isset($row->honorific_suffix) || ! empty($row->honorific_suffix) ) ? $entry->setHonorificSuffix( $row->honorific_suffix ) : $entry->setHonorificSuffix( '' );

			/*
			 * Set Title, Org, Dept and Contact Name.
			 */
			( isset($row->title) || ! empty($row->title) ) ? $entry->setTitle( $row->title ) : $entry->setTitle( '' );
			( isset($row->organization) || ! empty($row->organization) ) ? $entry->setOrganization( $row->organization ) : $entry->setOrganization( '' );
			( isset($row->department) || ! empty($row->department) ) ? $entry->setDepartment( $row->department ) : $entry->setDepartment( '' );
			( isset($row->contact_first_name) || ! empty($row->contact_first_name) ) ? $entry->setContactFirstName( $row->contact_first_name ) : $entry->setContactFirstName( '' );
			( isset($row->contact_last_name) || ! empty($row->contact_last_name) ) ? $entry->setContactLastName( $row->contact_last_name ) : $entry->setContactLastName( '' );

			/*
			 * Convert the parsed address data to an array that can be saved.
			 */

			// Fetch the core address types.
			$coreAddressTypes      = $instance->options->getDefaultAddressValues();

			// We only require the core address type key names.
			$permittedAddressTypes = array_keys( $coreAddressTypes );

			// Create the array for the valid address types. This is to support the core and extended address types.
			$addressTypes          = array_merge( $permittedAddressTypes, array( '0', '1', '2', '3', '4' ) );

			// The core address field.
			$addressFields         = array( 'line_one', 'line_two', 'line_three', 'city', 'state', 'zipcode', 'country', 'latitude', 'longitude' );

			// The array passed to the cnEntry object to set the addresses.
			$addresses             = array();

			$i = 0;

			foreach ( $addressTypes as $addressType ) {

				$i++;

				$address = array();

				foreach ( $addressFields as $addressField ) {

					// The address type/field key passed from the user map.
					$addressKey = 'address_' . $addressType . '_' . $addressField;

					if ( isset( $row->{ $addressKey } ) && $row->{ $addressKey } != NULL ) {

						// The street address field names need to be swapped to one of the supported key names.
						if ( $addressField == 'line_one' )   $addressField = 'line_1';
						if ( $addressField == 'line_two' )   $addressField = 'line_2';
						if ( $addressField == 'line_three' ) $addressField = 'line_3';

						$address[ $addressField ] = $row->{ $addressKey };
					}
				}

				if ( ! empty( $address ) ) {

					// Set the address type. If the address type is not supported by core, default to 'other'.
					$address['type'] = in_array( $addressType, $permittedAddressTypes ) ? $addressType : 'other';

					// Save the addess in to the array to be saved.
					$addresses[ $i ] = $address;
				}

				unset( $address );
			}

			// Set the addresses.
			if ( ! empty( $addresses ) ) $entry->setAddresses( $addresses );

			/*
			 * Convert the parsed phone number data to an array that can be saved.
			 */

			// Fetch the core address types.
			$corePhoneTypes      = $instance->options->getDefaultPhoneNumberValues();

			// We only require the core phone type key names.
			$premittedPhoneTypes = array_keys( $corePhoneTypes );

			// The array passed to the cnEntry object to set the phone numbers.
			$phoneNumbers        = array();

			$i = 0;

			foreach ( $corePhoneTypes as $phoneNumberTypeKey => $phoneNumberValue ) {

				$i++;

				$phoneNumberKey = 'phone_' . $phoneNumberTypeKey;

				if ( isset( $row->{ $phoneNumberKey } ) && ! empty( $row->{ $phoneNumberKey } ) ) {

					$phoneNumbers[ $i ]['number'] = $row->{ $phoneNumberKey };

					// Set the phone type. If the phone type is not supported by core, default to 'homephone'.
					$phoneNumbers[ $i ]['type']   = in_array( $phoneNumberTypeKey, $premittedPhoneTypes ) ? $phoneNumberTypeKey : 'homephone';
				}
			}

			// Set the phone numbers.
			if ( ! empty( $phoneNumbers ) ) {

				$entry->setPhoneNumbers( $phoneNumbers );
			}

			/*
			 * Convert the parsed email data to an array that can be saved.
			 */

			// Fetch the core email types.
			$coreEmailTypes      = $instance->options->getDefaultEmailValues();

			// We only require the core email type key names.
			$permittedEmailTypes = array_keys( $coreEmailTypes );

			// The array passed to the cnEntry object to set the email addresses.
			$emailAddresses      = array();

			$i = 0;

			foreach ( $coreEmailTypes as $emailTypeKey => $emailValue ) {

				$i++;

				$emailKey = 'email_' . $emailTypeKey;

				if ( isset( $row->{ $emailKey } ) && ! empty( $row->{ $emailKey } ) ) {

					$emailAddresses[ $i ]['address'] = $row->{ $emailKey };

					// Set the email type. If the email type is not supported by core, default to 'home'.
					$emailAddresses[ $i ]['type']    = in_array( $emailTypeKey, $permittedEmailTypes ) ? $emailTypeKey : 'personal';
				}
			}

			// Set the email addresses.
			if ( ! empty( $emailAddresses ) ) {

				$entry->setEmailAddresses( $emailAddresses );
			}


			/*
			 * Convert the parsed IM data to an array that can be saved.
			 */

			// Fetch the core IM types.
			$coreIMTypes      = $instance->options->getDefaultIMValues();

			// We only require the core IM type key names.
			$permittedIMTypes = array_keys( $coreEmailTypes );

			// The array passed to the cnEntry object to set the IM IDs.
			$imIDs            = array();

			$i = 0;

			foreach ( $coreIMTypes as $imTypeKey => $imValue ) {

				$i++;

				$imKey = 'messenger_' . $imTypeKey;

				if ( isset( $row->{ $imKey } ) && ! empty( $row->{ $imKey } ) ) {

					$imIDs[ $i ]['id']   = $row->{ $imKey };

					$imIDs[ $i ]['type'] = $imTypeKey;
				}
			}

			// Set the IM IDs.
			if ( ! empty( $imIDs ) ) {

				$entry->setIm( $imIDs );
			}


			/*
			 * Convert the parsed social media data to an array that can be saved.
			 */

			// Fetch the core social media types.
			$coreSocialTypes      = $instance->options->getDefaultSocialMediaValues();

			// We only require the core social media type key names.
			$permittedSocialTypes = array_keys( $coreSocialTypes );

			// The array passed to the cnEntry object to set the social media IDs.
			$socialIDs            = array();

			$i = 0;

			foreach ( $coreSocialTypes as $socialTypeKey => $socialValue ) {

				$i++;

				$socialKey = 'social_' . $socialTypeKey;

				if ( isset( $row->{ $socialKey } ) && ! empty( $row->{ $socialKey } ) ) {

					$socialIDs[ $i ]['url']  = $row->{ $socialKey };

					$socialIDs[ $i ]['type'] = $socialTypeKey;
				}
			}

			// Set the social media IDs.
			if ( ! empty( $socialIDs ) ) {

				$entry->setSocialMedia( $socialIDs );
			}


			/*
			 * Convert the parsed links data to an array that can be saved.
			 */

			// Fetch the core link types.
			$coreLinkTypes      = $instance->options->getDefaultLinkValues();

			// We only require the core link type key names.
			$permittedLinkTypes = array_keys( $coreLinkTypes );

			// The array passed to the cnEntry object to set the links.
			$linkIDs            = array();

			$i = 0;

			foreach ( $coreLinkTypes as $linkTypeKey => $linkValue ) {

				$i++;

				$linkKey = 'link_' . $linkTypeKey;

				if ( isset( $row->{ $linkKey } ) && ! empty( $row->{ $linkKey } ) ) {

					$linkIDs[ $i ]['url']  = $row->{ $linkKey };

					$linkIDs[ $i ]['type'] = $linkTypeKey;
				}
			}

			// Set the links.
			if ( ! empty( $linkIDs ) ) {

				$entry->setLinks( $linkIDs );
			}


			/*
			 * Convert the parsed date data to an array that can be saved.
			 */

			// Fetch the core date types.
			$coreDateTypes      = $instance->options->getDateOptions();

			// We only require the core link type key names.
			$permittedDateTypes = array_keys( $coreDateTypes );

			// The array passed to the cnEntry object to set the links.
			$dates            = array();

			$i = 0;

			foreach ( $coreDateTypes as $dateTypeKey => $dateValue ) {

				$i++;

				$dateKey = 'date_' . $dateTypeKey;

				if ( isset( $row->{ $dateKey } ) && ! empty( $row->{ $dateKey } ) ) {

					$dates[ $i ]['date'] = $row->{ $dateKey };

					$dates[ $i ]['type'] = $dateTypeKey;

					// This is a required option for dates.
					// The other core data types previous version 0.7.1.6
					// did not have that option so it is assumed that they were public.
					$dates[ $i ]['visibility'] = 'public';
				}
			}

			// Set the dates.
			if ( ! empty( $dates ) ) {

				$entry->setDates( $dates );
				// var_dump($dates); var_dump( $entry->getDates() ); die();
			}


			// Set the bio.
			if ( isset( $row->bio ) && ! empty( $row->bio ) ) $entry->setBio( $row->bio );

			// Set the notes.
			if ( isset( $row->notes ) && ! empty( $row->notes ) ) $entry->setNotes( $row->notes );



			// Finally, save/update the entry.
			$result = $doUpdate ? $entry->update() : $entry->save();

			// Set the ID that will be used to set the term relationship.
			$entryID = $doUpdate ? $entry->getId() : $instance->lastInsertID;
			
			$row = (array)$row;
			$row['entryID'] = $entryID;
			$row = (object)$row;
			
			
			
			$row->entryID = $entryID;
			
			//allow plugins to add their data
			apply_filters( 'cncsv_import_fields', $row );
			

			// If importing the entry was successful, process the categories.
			if ( $result && ! empty( $row->category ) ) {

				$format  = new cnFormatting();
				$termIDs = array();

				// Set the category cache key if not already set.
				if ( ! isset( $this->cache['category'] ) ) $this->cache['category'] = array();

				// Convert the supplied categories to an array.
				$categories = explode( ',', $row->category );
				//print_r( $categories ); die;

				foreach ( $categories as $category ) {

					$category = $format->sanitizeString( $category );

					if ( array_key_exists( $category, $this->cache['category'] ) ) {

						// If the term has already been queried use the cached copy rather than query the db again.
						$term = $this->cache['category'][ $category ];

					} else {

						// Query the db for the term to be added.
						$term = $instance->term->getTermBy( 'name', $category, 'category' );

						// If the term was found store it in the cache for reuse.
						if ( $term != FALSE ) $this->cache['category'][ $category ] = $term;
					}

					if ( $term != FALSE && $term->name == $category ) {

						$termIDs[] = $term->term_id;

					} else {

						// Add the new category.
						$instance->term->addTerm( $category, 'category', array( 'slug' => '', 'parent' => '0', 'description' => '' ) );
						// addTerm should be refactored to return the insert ID so the getTermBy query does not need done.

						// Get the newly added term object.
						$term = $instance->term->getTermBy('name', $category, 'category');

						$termIDs[] = $term->term_id;
					}
				}

				$instance->term->setTermRelationships( $entryID, $termIDs, 'category' );

			// If importing the entry was successful and no categories were supplied, add the "Uncategorized" category.
			} elseif ( $result && ( ! isset( $row->category ) || empty( $row->category ) ) ) {

				$termIDs = array();

				// Set the category cache key if not already set.
				if ( ! isset( $this->cache['category'] ) ) $this->cache['category'] = array();

				if ( array_key_exists( 'uncategorized', $this->cache['category'] ) ) {

					// If the term has already been queried use the cached copy rather than query the db again.
					$term = $this->cache['category']['uncategorized'];

				} else {

					// Query the db for the "Uncategorized" term.
					$term = $instance->term->getTermBy( 'slug', 'uncategorized', 'category' );

					// Store it in the cache for reuse.
					if ( $term != FALSE ) $this->cache['category']['uncategorized'] = $term;
				}

				$termIDs[] = $term->term_id;

				$instance->term->setTermRelationships( $entryID, $termIDs, 'category' );
			}

		}

		return $recordCount;
	}
}
