<?php

/**
 * Get and Set the plugin options
 */
class connectionsCSVOptions {

	/**
	 * Array of options returned from WP get_option method
	 * @var array
	 */
	private $options;

	/**
	 * String: plugin version
	 * @var string
	 */
	private $version;

	public static function fields() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Core fields.
		$fields = array(
			'0'                  => 'Do Not Import',
			'entry_type'         => 'Entry Type',
			'entry_id'           => 'Entry ID',
			'visibility'         => 'Visibility',
			'honorific_prefix'   => 'Honorific Prefix',
			'first_name'         => 'First Name',
			'middle_name'        => 'Middle Name',
			'last_name'          => 'Last Name',
			'honorific_suffix'   => 'Honorific Suffix',
			'title'              => 'Title',
			'organization'       => 'Organization',
			'department'         => 'Department',
			'contact_first_name' => 'Contact First Name',
			'contact_last_name'  => 'Contact Last Name',
			'bio'                => 'Biography',
			'notes'              => 'Notes',
			);

		$fields['category'] = 'Categories';

		/*
		 * Build the array of core and extended address fields for mapping duing import.
		 */

		$coreAddressTypes = $instance->options->getDefaultAddressValues();
		$addressFields    = array(
			'line_one'   => 'Line One',
			'line_two'   => 'Line Two',
			'line_three' => 'Line Three',
			'city'       => 'City',
			'state'      => 'State',
			'zipcode'    => 'Zipcode',
			'country'    => 'Country',
			'latitude'   => 'Latitude',
			'longitude'  => 'Longitude',
			);

		/*
		 * Add the core address types to the field array.
		 */
		foreach ( $coreAddressTypes as $addressType => $addressName ) {

			foreach ( $addressFields as $addressFieldType => $addressFieldName ) {

				$key = 'address_' . $addressType . '_' . $addressFieldType;

				$fields[ $key ] = $addressName . ' Address | ' . $addressFieldName;
			}
		}

		/*
		 * Add 5 additional address types to the fields array.
		 * NOTE: This will be mapped as the "Other" address type to maintain compatibility with core.
		 */
		$extendedAddressTypes = array(
			'address_0_line_one'   => 'Address 1 | Line One',
			'address_0_line_two'   => 'Address 1 | Line Two',
			'address_0_line_three' => 'Address 1 | Line Three',
			'address_0_city'       => 'Address 1 | City',
			'address_0_state'      => 'Address 1 | State',
			'address_0_zipcode'    => 'Address 1 | Zipcode',
			'address_0_country'    => 'Address 1 | Country',
			'address_0_latitude'   => 'Address 1 | Latitude',
			'address_0_longitude'  => 'Address 1 | Longitude',
			'address_1_line_one'   => 'Address 2 | Line One',
			'address_1_line_two'   => 'Address 2 | Line Two',
			'address_1_line_three' => 'Address 2 | Line Three',
			'address_1_city'       => 'Address 2 | City',
			'address_1_state'      => 'Address 2 | State',
			'address_1_zipcode'    => 'Address 2 | Zipcode',
			'address_1_country'    => 'Address 2 | Country',
			'address_1_latitude'   => 'Address 2 | Latitude',
			'address_1_longitude'  => 'Address 2 | Longitude',
			'address_2_line_one'   => 'Address 3 | Line One',
			'address_2_line_two'   => 'Address 3 | Line Two',
			'address_2_line_three' => 'Address 3 | Line Three',
			'address_2_city'       => 'Address 3 | City',
			'address_2_state'      => 'Address 3 | State',
			'address_2_zipcode'    => 'Address 3 | Zipcode',
			'address_2_country'    => 'Address 3 | Country',
			'address_2_latitude'   => 'Address 3 | Latitude',
			'address_2_longitude'  => 'Address 3 | Longitude',
			'address_3_line_one'   => 'Address 4 | Line One',
			'address_3_line_two'   => 'Address 4 | Line Two',
			'address_3_line_three' => 'Address 4 | Line Three',
			'address_3_city'       => 'Address 4 | City',
			'address_3_state'      => 'Address 4 | State',
			'address_3_zipcode'    => 'Address 4 | Zipcode',
			'address_3_country'    => 'Address 4 | Country',
			'address_3_latitude'   => 'Address 4 | Latitude',
			'address_3_longitude'  => 'Address 4 | Longitude',
			'address_4_line_one'   => 'Address 5 | Line One',
			'address_4_line_two'   => 'Address 5 | Line Two',
			'address_4_line_three' => 'Address 5 | Line Three',
			'address_4_city'       => 'Address 5 | City',
			'address_4_state'      => 'Address 5 | State',
			'address_4_zipcode'    => 'Address 5 | Zipcode',
			'address_4_country'    => 'Address 5 | Country',
			'address_4_latitude'   => 'Address 5 | Latitude',
			'address_4_longitude'  => 'Address 5 | Longitude',
			);

		// Merge the core and exntended addresses into the fields array.
		$fields = array_merge( $fields , $extendedAddressTypes );

		/*
		 * Build the array of core phone fields for mapping duing import.
		 */

		$corePhoneTypes = $instance->options->getDefaultPhoneNumberValues();

		// Add the core phone types to the field array.
		foreach ( $corePhoneTypes as $phoneType => $phoneName ) {

			$key = 'phone_' . $phoneType;

			$fields[ $key ] = 'Phone | ' . $phoneName;
		}

		/*
		 * Build the array of core email fields for mapping duing import.
		 */

		$coreEmailTypes = $instance->options->getDefaultEmailValues();

		// Add the core email types to the field array.
		foreach ( $coreEmailTypes as $emailType => $emailName ) {

			$key = 'email_' . $emailType;

			$fields[ $key ] = 'Email | ' . $emailName;
		}

		/*
		 * Build the array of core IM fields for mapping duing import.
		 */

		$coreIMTypes = $instance->options->getDefaultIMValues();

		// Add the core IM types to the field array.
		foreach ( $coreIMTypes as $IMType => $IMName ) {

			$key = 'messenger_' . $IMType;

			$fields[ $key ] = 'Messenger | ' . $IMName;
		}


		/*
		 * Build the array of core social media fields for mapping duing import.
		 */

		$coreSocialTypes = $instance->options->getDefaultSocialMediaValues();

		// Add the core email types to the field array.
		foreach ( $coreSocialTypes as $socialType => $socialName ) {

			$key = 'social_' . $socialType;

			$fields[ $key ] = 'Social Network | ' . $socialName;
		}


		/*
		 * Build the array of core link fields for mapping duing import.
		 */

		$coreLinkTypes = $instance->options->getDefaultLinkValues();

		// Add the core email types to the field array.
		foreach ( $coreLinkTypes as $linkType => $linkName ) {

			$key = 'link_' . $linkType;

			$fields[ $key ] = 'Link | ' . $linkName;
		}


		/*
		 * Build the array of core date fields for mapping duing import.
		 */

		$coreDateTypes = $instance->options->getDateOptions();

		// Add the core date types to the field array.
		foreach ( $coreDateTypes as $dateType => $dateName ) {

			$key = 'date_' . $dateType;

			$fields[ $key ] = 'Date | ' . $dateName;
		}

		return apply_filters( 'cncsv_map_import_fields', $fields );
	}

	public static function map() {

		return array_change_key_case( array_flip( self::fields() ) );
	}

	/**
	 * Sets up the plugin option properties. Requires the current WP user ID.
	 * @param interger $userID
	 */
	public function __construct() {
		$this->options = get_option('connections_cncsv_options');
	}

	/**
	 * Saves the plug-in options to the database.
	 */
	public function saveOptions() {
		update_option('connections_cncsv_options', $this->options);
	}

	public function removeOptions() {
		delete_option('connections_cncsv_options');
	}

    /**
     * Returns $version.
     * @see options::$version
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Sets $version.
     * @param object $version
     * @see options::$version
     */
    public function setVersion($version) {
        $this->version = $version;
		$this->saveOptions();
    }

    /**
     * Returns $options.
     * @see pluginOptions::$options
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Sets $options.
     * @param object $options
     * @see pluginOptions::$options
     */
    public function setOptions($options) {
        $this->options = $options;
    }
}
