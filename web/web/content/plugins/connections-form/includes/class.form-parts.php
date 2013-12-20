<?php
if (!defined('ABSPATH'))
    exit;
class cnfmFormParts {
    public static function getDefaultBlocks() {
        //for now we are hard coding this stuff
        //it's the defaults, but we would merge_array later
        $blocks = array(
            "contact" => __('Contact Information', 'connections_form'),
            "category" => __('Categories', 'connections_form'),
            "image" => __('Photos or Logos', 'connections_form'),
            'address' => __('Phyiscal addresses', 'connections_form'),
            'phone' => __('Phone numbers', 'connections_form'),
            'email' => __('Email addresses', 'connections_form'),
            'messenger' => __('Messengers', 'connections_form'),
            'social' => __('Social', 'connections_form'),
            'link' => __('Links', 'connections_form'),
            "speicalDate" => __('Anniversary or Birthday information', 'connections_form'),
            "bio" => __('Biography', 'connections_form'),
            "notes" => __('Notes', 'connections_form')
        );
        return $blocks;
    }
    public static function getRegisteredBlocks() {
        $registeredblocks = get_option('cnfm_registeredblocks', json_encode(self::getDefaultBlocks()));
        $blocks           = json_decode($registeredblocks);
        return $blocks;
    }
    public static function registerBlock() {
    }
    public static function getFormBlock($name = null, $entry = null, $atts = array()) {
        $out = call_user_func(array(
            __CLASS__,
            $name . 'Block'
        ), $entry, $atts); //escaspe failed warning for now, @todo
        return $out;
    }
    public static function tokenizeBlock($blockStr, $token, $blockObject) {
        $valuePatteren = '/(:?value=[\'|"])::(.*?)::(:?[\'|"])/i';
        $blockStr      = preg_replace_callback($valuePatteren, function($matches, $blockObject) {
            $prop = $matches[0];
            return (isset($blockObject->$prop) ? 'value="' . $blockObject->$prop . '"' : "");
        }, $blockStr);
        $valuePatteren = '/::FIELD::/i';
        $blockStr      = preg_replace($valuePatteren, $token, $blockStr);
        return $blockStr;
    }
    public static function cleanseStub($blockStr) {
        $valuePatteren = '/(:?value=[\'|"])::(.*?)::(:?[\'|"])/i'; //pull the whole value attr
        $blockStr      = preg_replace($valuePatteren, '', $blockStr);
        return $blockStr;
    }
    public static function makeCountriesDropdown($token = "::FIELD::", $selected) {
        global $connections;
        $form         = new cnFormObjects();
        $display_code = $connections->settings->get('connections_form', 'connections_form_preferences', 'form_preference_countries_display_code');
        $out          = '<select name="address[' . $token . '][country]">';
        $enabled      = $connections->settings->get('connections_form', 'connections_form_preferences', 'form_preference_show_countries');
        foreach (cnDefaultValues::getCountries() as $code => $country) {
            if (in_array($code, $enabled)) {
                $lable = $display_code ? $code : $country['name'];
                $out .= '<option value="' . $code . '" ' . selected($selected, $code, false) . ' >' . $lable . '</option>';
            }
        }
        $out .= '</select>';
        return $out;
    }
    public static function makeRegionsDropdown($token = "::FIELD::", $selected) {
        global $connections;
        $form         = new cnFormObjects();
        $display_code = $connections->settings->get('connections_form', 'connections_form_preferences', 'form_preference_regions_display_code');
        $out          = '<select name="address[' . $token . '][state]">';
        foreach (cnDefaultValues::getRegions() as $code => $regions) {
            $lable = $display_code ? $code : $regions;
            $out .= '<option value="' . $code . '" ' . selected($selected, $code, false) . ' >' . $lable . '</option>';
        }
        $out .= '</select>';
        return $out;
    }
	
	/**
     * Returns the options for the category select list.
     *
     * @author Steven A. Zahm
     * @version 1.0
     * @param object $category
     * @param integer $level
     * @param integer $selected
     * @return string
     */
    private static function buildOptionRowHTML($category, $level, $selected) {
        $selectString = NULL;
        $out          = '';
        //$pad = str_repeat('&emsp; ', max(0, $level));
        if ($selected == $category->term_id)
            $selectString = ' SELECTED ';
        //($this->showCategoryCount) ? $count = ' (' . $category->count . ')' : $count = '';
        //if ( ($this->showEmptyCategories && empty($category->count)) || ($this->showEmptyCategories || !empty($category->count)) || !empty($category->children) ) $out .= '<option style="margin-left: ' . $level . 'em;" value="' . $category->term_id . '"' . $selectString . '>' . /*$pad .*/ $category->name . $count . '</option>' . "\n";
        $out .= '<option style="margin-left: ' . $level . 'em;" value="' . $category->term_id . '"' . $selectString . '>' . $category->name . '</option>' . "\n";
        if (!empty($category->children)) {
            foreach ($category->children as $child) {
                ++$level;
                $out .= $this->buildOptionRowHTML($child, $level, $selected);
                --$level;
            }
        }
        return $out;
    }
	
	
    /*
     * Block area
     * named blocks are set here.  These are the defaults
     * extend the blocks ______TBD_________
     */
    public static function contactBlock($entry = null, $atts = array()) {
        global $connections;
        $form = new cnFormObjects();
        $type = ($entry->getEntryType()) ? $entry->getEntryType() : $atts['default_type'];
        $out  = '<div id="metabox-name" class="postbox">' . "\n";
			$out .= '<h3 class="hndle">';
			if ($atts['select_type']) {
				$out .= '<span>' . __('I am an', 'connections_form') . ':</span>' . "\n";
				$out .= $form->buildRadio("entry_type", "entry_type", array(
					__('Individual', 'connections_form') => 'individual',
					__('Organization', 'connections_form') => 'organization'
				), $type);
			} else {
				if (!empty($atts['str_contact_name']))
					$out .= '<span>' . $atts['str_contact_name'] . '</span>' . "\n";
				// Hidden Field -- For the default entry type if the user selectable radio is disabled.
				$out .= '<input type="hidden" name="entry_type" value="' . $type . '" />' . "\n";
			}
			$out .= '</h3>' . "\n";
			$out .= '<div class="cnf-inside">' . "\n";
				$out .= '<div class="form-field" id="cn-name">' . "\n";
					$out .= '<div id="honorific-prefix" class="cn-float-left"><label>' . __('Prefix', 'connections_form') . ': <input type="text" name="honorific_prefix" value="' . $entry->getHonorificPrefix() . '"></label></div>' . "\n";
					$out .= '<div id="first-name" class="cn-float-left"><label>' . __('First Name', 'connections_form') . ': <input class="required" type="text" name="first_name" value="' . $entry->getFirstName() . '"></label></div>' . "\n";
					$out .= '<div id="middle-name" class="cn-float-left"><label>' . __('Middle Name', 'connections_form') . ': <input type="text" name="middle_name" value="' . $entry->getMiddleName() . '"></label></div>' . "\n";
					$out .= '<div id="last-name" class="cn-float-left"><label>' . __('Last Name', 'connections_form') . ': <input class="required" type="text" name="last_name" value="' . $entry->getLastName() . '"></label></div>' . "\n";
					$out .= '<div id="honorific-suffix" class="cn-float-left"><label>' . __('Suffix', 'connections_form') . ': <input type="text" name="honorific_suffix" value="' . $entry->getHonorificSuffix() . '"></label></div>' . "\n";
					$out .= '<div class="cn-clear"></div>' . "\n";
					$out .= '<div id="title"><label>' . __('Title', 'connections_form') . ': <input type="text" name="title" value="' . $entry->getTitle() . '"></label></div>' . "\n";
				$out .= '</div>' . "\n";
				$out .= '<div class="form-field" id="cn-org-unit">';
					$out .= '<label>' . __('Organization', 'connections_form') . ': <input class="required" type="text" name="organization" value="' . $entry->getOrganization() . '"></label>' . "\n";
					$out .= '<label>' . __('Department', 'connections_form') . ': <input type="text" name="department" value="' . $entry->getDepartment() . '"></label>' . "\n";
					$out .= '<div id="cn-contact-name">' . "\n";
						$out .= '<div class="cn-float-left cn-half-width" id="contact-first-name"><label>' . __('Contact First Name', 'connections_form') . ': <input type="text" name="contact_first_name" value="' . $entry->getContactFirstName() . '"></label></div>';
						$out .= '<div class="cn-float-left cn-half-width" id="contact-last-name"><label>' . __('Contact Last Name', 'connections_form') . ': <input type="text" name="contact_last_name" value="' . $entry->getContactLastName() . '"></label></div>';
						$out .= '<div class="cn-clear"></div>' . "\n";
					$out .= '</div>' . "\n";
				$out .= '</div>' . "\n";
				$out .= '<div class="cn-clear"></div>' . "\n";
			$out .= '</div>' . "\n";
			/* END --> .cnf-inside */
        $out .= '</div>' . "\n";
        /* END --> .postbox */
        return $out;
    }
    public static function imageBlock($entry = null, $atts = array()) {
        $out = '<div id="metabox-image" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Image', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="form-field">';
        if ($atts['photo']) {
            if ($entry->getImageLinked()) {
                $selected   = ($entry->getImageDisplay()) ? 'show' : 'hidden';
                $imgOptions = $form->buildRadio('imgOptions', 'imgOptionID_', array(
                    __('Display', 'connections_form') => 'show',
                    __('Not Displayed', 'connections_form') => 'hidden',
                    __('Remove', 'connections_form') => 'remove'
                ), $selected);
                $out .= '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getImageNameProfile() . '" /> <br /> <span class="radio_group">' . $imgOptions . '</span></div> <br />';
            }
            $out .= '<div class="clear"></div>';
            $out .= '<label for="original_image">' . __('Select Photo', 'connections_form') . ': <input type="file" value="" name="original_image" size="25" /></label>';
        }
        if ($atts['logo']) {
            if ($entry->getLogoLinked()) {
                $selected    = ($entry->getLogoDisplay()) ? 'show' : 'hidden';
                $logoOptions = $form->buildRadio('logoOptions', 'logoOptionID_', array(
                    __('Display', 'connections_form') => 'show',
                    __('Not Displayed', 'connections_form') => 'hidden',
                    __('Remove', 'connections_form') => 'remove'
                ), $selected);
                $out .= '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getLogoName() . '" /> <br /> <span class="radio_group">' . $logoOptions . '</span></div> <br />';
            }
            $out .= '<div class="clear"></div>';
            $out .= '<label for="original_logo">' . __('Select Logo', 'connections_form') . ': <input type="file" value="" name="original_logo" size="25" /></label>';
        }
        $out .= '</div>';
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function addressBlock($entry = null, $atts = array()) {
        global $connections;
        $form      = new cnFormObjects();
        $addresses = $entry->getAddresses(array(), FALSE);
        $out       = '<div id="metabox-address" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Address Type', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('address', $atts['open_blocks']) && empty($addresses)) ? " startwithone " : "") . '" id="addresses">' . "\n";
        // --> Start template <-- \\
        $out .= '<textarea id="address-template" style="display: none;">' . "\n";
        $out .= self::cleanseStub(self::addressBlockStub(array(
            'type' => 'work',
            'country' => 'US',
            'state' => 'WA'
        )));
        $out .= '</textarea>' . "\n";
        // --> End template <-- \\
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $out .= '<div class="widget address" id="address_row_' . $token . '">' . "\n";
                $out .= self::tokenizeBlock(self::addressBlockStub(array(
                    'type' => $address->type,
                    'country' => $address->country,
                    'state' => $address->state
                )), $token, $address);
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>' . "\n";
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="address" data-container="addresses">' . __('Add Address', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function addressBlockStub($directValues = array()) {
        global $connections;
        $form = new cnFormObjects();
        $out  = '<div class="widget-top">' . "\n";
			$out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
			$out .= '<div class="widget-title"><h4>' . "\n";
			$out .= __('Address Type', 'connections_form') . ': ' . $form->buildSelect('address[::FIELD::][type]', $connections->options->getDefaultAddressValues(), isset($directValues['type']) ? $directValues['type'] : "") . "\n";
				$out .= '<label><input type="radio" name="address[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
				$out .= '<input type="hidden" name="address[::FIELD::][visibility]" value="public">';
			$out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">';
			$out .= '<div class="address-local">';
				$out .= '<div class="address-line">';
					$out .= '<label for="address">' . __('Address Line 1', 'connections_form') . ':</label>';
					$out .= '<input type="text" name="address[::FIELD::][line_1]" value="::line_1::" />';
				$out .= '</div>';
				$out .= '<div class="address-line">';
					$out .= '<label for="address">' . __('Address Line 2', 'connections_form') . ':</label>';
					$out .= '<input type="text" name="address[::FIELD::][line_2]" value="::line_2::" />';
				$out .= '</div>';
				$out .= '<div class="address-line">';
					$out .= '<label for="address">' . __('Address Line 3', 'connections_form') . ':</label>';
					$out .= '<input type="text" name="address[::FIELD::][line_3]" value="::line_3::" />';
				$out .= '</div>';
			$out .= '</div>';
			$out .= '<div class="address-region">';
				$out .= '<div class="address-city cn-float-left">';
					$out .= '<label for="address">' . __('City', 'connections_form') . ':</label>';
					$out .= '<input type="text" name="address[::FIELD::][city]" value="::city::">';
				$out .= '</div>';
				$out .= '<div class="address-state cn-float-left">';
					$out .= '<label for="address">' . __('State', 'connections_form') . ':</label><br/>';
					$out .= self::makeRegionsDropdown("::FIELD::", isset($directValues['state']) ? $directValues['state'] : "");
				$out .= '</div>';
				$out .= '<div class="address-zipcode cn-float-left">';
					$out .= '<label for="address">' . __('Zipcode', 'connections_form') . ':</label>';
					$out .= '<input type="text" name="address[::FIELD::][zipcode]" value="::zipcode::">';
				$out .= '</div>';
			$out .= '</div>';
			$out .= '<div class="address-country">';
				$out .= '<label for="address">' . __('Country', 'connections_form') . '</label>';
				$out .= self::makeCountriesDropdown("::FIELD::", isset($directValues['country']) ? $directValues['country'] : "");
			$out .= '</div>';
			$out .= '<div class="address-geo">';
				$out .= '<div class="address-latitude cn-float-left">';
					$out .= '<label for="latitude">' . __('Latitude', 'connections_form') . '</label>';
					$out .= '<input type="text" name="address[::FIELD::][latitude]" value="::latitude::">';
				$out .= '</div>';
				$out .= '<div class="address-longitude cn-float-left">';
					$out .= '<label for="longitude">' . __('Longitude', 'connections_form') . '</label>';
					$out .= '<input type="text" name="address[::FIELD::][longitude]" value="::longitude::">';
				$out .= '</div>';
				$out .= '<a class="geocode button" data-uid="::FIELD::" href="#">' . __('Geocode', 'connections') . '</a>';
			$out .= '</div>';
			$out .= '<div class="map" id="map-::FIELD::" data-map-id="::FIELD::" style="display: none; height: 400px;">' . __('Geocoding Address.', 'connections') . '</div>';
			$out .= '<div class="cn-clear"></div>';
			$out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="address" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        return $out;
    }
    public static function phoneBlock($entry = null, $atts = array()) {
        global $connections;
        $form         = new cnFormObjects();
        $phoneNumbers = $entry->getPhoneNumbers(array(), FALSE);
        $out          = '<div id="metabox-phone" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Phone Numbers', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('phone', $atts['open_blocks']) && empty($phoneNumbers)) ? " startwithone " : "") . '" id="phone-numbers">';
        // --> Start template <-- \\
        $out .= '<textarea id="phone-template" style="display: none">';
        $out .= '<div class="widget-top">' . "\n";
        $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
        $out .= '<div class="widget-title"><h4>' . "\n";
        $out .= __('Phone Type', 'connections_form') . ': ' . $form->buildSelect('phone[::FIELD::][type]', $connections->options->getDefaultPhoneNumberValues()) . "\n";
        $out .= '<label><input type="radio" name="phone[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
        //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('phone[::FIELD::][visibility]', 'phone_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="phone[::FIELD::][visibility]" value="public">';
        $out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">' . "\n";
        $out .= '<label>' . __('Phone Number', 'connections_form') . '</label><input type="text" name="phone[::FIELD::][number]" value="" style="width: 30%"/>' . "\n";
        $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="phone" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '</textarea>';
        // --> End template <-- \\
        if (!empty($phoneNumbers)) {
            foreach ($phoneNumbers as $phone) {
                //$token = $form->token( $entry->getId() );
                $selectName = 'phone[' . $token . '][type]';
                ($phone->preferred) ? $preferredPhone = 'CHECKED' : $preferredPhone = '';
                $out .= '<div class="widget phone" id="phone-row-' . $token . '">' . "\n";
                $out .= '<div class="widget-top">' . "\n";
                $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
                $out .= '<div class="widget-title"><h4>' . "\n";
                $out .= __('Phone Type', 'connections_form') . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultPhoneNumberValues(), $phone->type) . "\n";
                $out .= '<label><input type="radio" name="phone[preferred]" value="' . $token . '" ' . $preferredPhone . '> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
                //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('phone[' . $token . '][visibility]', 'phone_visibility_'  . $token . $this->visibiltyOptions, $phone->visibility) . '</span>' . "\n";
                $out .= '<input type="hidden" name="phone[' . $token . '][visibility]" value="public">';
                $out .= '</h4></div>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '<div class="widget-inside">' . "\n";
                $out .= '<label>' . __('Phone Number', 'connections_form') . '</label><input type="text" name="phone[' . $token . '][number]" value="' . $phone->number . '" style="width: 30%"/>';
                $out .= '<input type="hidden" name="phone[' . $token . '][id]" value="' . $phone->id . '">' . "\n";
                $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="phone" data-token="' . $token . '">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>';
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="phone" data-container="phone-numbers">' . __('Add Phone Number', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function emailBlock($entry = null, $atts = array()) {
        global $connections;
        $form           = new cnFormObjects();
        $emailAddresses = $entry->getEmailAddresses(array(), FALSE);
        $out            = '<div id="metabox-email" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Email', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('email', $atts['open_blocks']) && empty($emailAddresses)) ? " startwithone " : "") . '" id="email-addresses">';
        // --> Start template <-- \\
        $out .= '<textarea id="email-template" style="display: none">';
        $out .= '<div class="widget-top">' . "\n";
        $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
        $out .= '<div class="widget-title"><h4>' . "\n";
        $out .= __('Email Type', 'connections_form') . ': ' . $form->buildSelect('email[::FIELD::][type]', $connections->options->getDefaultEmailValues()) . "\n";
        $out .= '<label><input type="radio" name="email[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
        //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('email[::FIELD::][visibility]', 'email_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="email[::FIELD::][visibility]" value="public">';
        $out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">' . "\n";
        $out .= '<label>' . __('Email Address', 'connections_form') . '</label><input type="text" name="email[::FIELD::][address]" value="" style="width: 30%"/>' . "\n";
        $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="email" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '</textarea>';
        // --> End template <-- \\
        if (!empty($emailAddresses)) {
            foreach ($emailAddresses as $email) {
                //$token = $form->token( $entry->getId() );
                $selectName = 'email[' . $token . '][type]';
                ($email->preferred) ? $preferredEmail = 'CHECKED' : $preferredEmail = '';
                $out .= '<div class="widget email" id="email-row-' . $token . '">' . "\n";
                $out .= '<div class="widget-top">' . "\n";
                $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
                $out .= '<div class="widget-title"><h4>' . "\n";
                $out .= __('Email Type', 'connections_form') . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultEmailValues(), $email->type) . "\n";
                $out .= '<label><input type="radio" name="email[preferred]" value="' . $token . '" ' . $preferredEmail . '> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
                //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('email[' . $token . '][visibility]', 'email_visibility_'  . $token . $this->visibiltyOptions, $email->visibility) . '</span>' . "\n";
                $out .= '<input type="hidden" name="email[' . $token . '][visibility]" value="public">';
                $out .= '</h4></div>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '<div class="widget-inside">' . "\n";
                $out .= '<label>' . __('Email Address', 'connections_form') . '</label><input type="text" name="email[' . $token . '][address]" value="' . $email->address . '" style="width: 30%"/>';
                $out .= '<input type="hidden" name="email[' . $token . '][id]" value="' . $email->id . '">' . "\n";
                $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="email" data-token="' . $token . '">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>';
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="email" data-container="email-addresses">' . __('Add Email Address', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function messengerBlock($entry = null, $atts = array()) {
        global $connections;
        $form  = new cnFormObjects();
        $imIDs = $entry->getIm(array(), FALSE);
        $out   = '<div id="metabox-messenger" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Messenger', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('messenger', $atts['open_blocks']) && empty($imIDs)) ? " startwithone " : "") . '" id="im-ids">';
        // --> Start template.  <-- \\
        $out .= '<textarea id="im-template" style="display: none">';
        $out .= '<div class="widget-top">' . "\n";
        $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
        $out .= '<div class="widget-title"><h4>' . "\n";
        $out .= __('IM Type', 'connections_form') . ': ' . $form->buildSelect('im[::FIELD::][type]', $connections->options->getDefaultIMValues()) . "\n";
        $out .= '<label><input type="radio" name="im[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
        //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('im[::FIELD::][visibility]', 'im_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="im[::FIELD::][visibility]" value="public">';
        $out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">' . "\n";
        $out .= '<label>' . __('IM Network ID', 'connections_form') . '</label><input type="text" name="im[::FIELD::][id]" value="" style="width: 30%"/>' . "\n";
        $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="im" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '</textarea>';
        // --> End template. <-- \\
        if (!empty($imIDs)) {
            foreach ($imIDs as $network) {
                //$token = $form->token( $entry->getId() );
                $selectName = 'im[' . $token . '][type]';
                ($network->preferred) ? $preferredIM = 'CHECKED' : $preferredIM = '';
                $out .= '<div class="widget im" id="im-row-' . $token . '">' . "\n";
                $out .= '<div class="widget-top">' . "\n";
                $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
                $out .= '<div class="widget-title"><h4>' . "\n";
                $out .= __('IM Type', 'connections_form') . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultIMValues(), $network->type) . "\n";
                $out .= '<label><input type="radio" name="im[preferred]" value="' . $token . '" ' . $preferredIM . '> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
                //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('im[' . $token . '][visibility]', 'im_visibility_'  . $token . $this->visibiltyOptions, $network->visibility) . '</span>' . "\n";
                $out .= '<input type="hidden" name="im[' . $token . '][visibility]" value="public">';
                $out .= '</h4></div>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '<div class="widget-inside">' . "\n";
                $out .= '<label>' . __('IM Network ID', 'connections_form') . '</label><input type="text" name="im[' . $token . '][id]" value="' . $network->id . '" style="width: 30%"/>';
                $out .= '<input type="hidden" name="im[' . $token . '][uid]" value="' . $network->uid . '">' . "\n";
                $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="im" data-token="' . $token . '">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>';
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="im" data-container="im-ids">' . __('Add Messenger ID', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function socialBlock($entry = null, $atts = array()) {
        global $connections;
        $form           = new cnFormObjects();
        $socialNetworks = $entry->getSocialMedia(array(), FALSE);
        $out            = '<div id="metabox-social" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Social Media', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('social', $atts['open_blocks']) && empty($socialNetworks)) ? " startwithone " : "") . '" id="social-media">';
        // --> Start template <-- \\
        $out .= '<textarea id="social-template" style="display: none">';
        $out .= '<div class="widget-top">' . "\n";
        $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
        $out .= '<div class="widget-title"><h4>' . "\n";
        $out .= __('Social Network', 'connections_form') . ': ' . $form->buildSelect('social[::FIELD::][type]', $connections->options->getDefaultSocialMediaValues()) . "\n";
        $out .= '<label><input type="radio" name="social[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
        //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('social[::FIELD::][visibility]', 'social_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="social[::FIELD::][visibility]" value="public">';
        $out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">' . "\n";
        $out .= '<label>' . __('URL', 'connections_form') . '</label><input type="text" name="social[::FIELD::][url]" value="http://" style="width: 30%"/>' . "\n";
        $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="social" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '</textarea>';
        // --> End template <-- \\
        if (!empty($socialNetworks)) {
            foreach ($socialNetworks as $network) {
                //$token = $form->token( $entry->getId() );
                $selectName = 'social[' . $token . '][type]';
                ($network->preferred) ? $preferredNetwork = 'CHECKED' : $preferredNetwork = '';
                $out .= '<div class="widget social" id="social-row-' . $token . '">' . "\n";
                $out .= '<div class="widget-top">' . "\n";
                $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
                $out .= '<div class="widget-title"><h4>' . "\n";
                $out .= __('Social Network', 'connections_form') . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultSocialMediaValues(), $network->type) . "\n";
                $out .= '<label><input type="radio" name="social[preferred]" value="' . $token . '" ' . $preferredNetwork . '> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
                //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('social[' . $token . '][visibility]', 'social_visibility_'  . $token . $this->visibiltyOptions, $network->visibility) . '</span>' . "\n";
                $out .= '<input type="hidden" name="social[' . $token . '][visibility]" value="public">';
                $out .= '</h4></div>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '<div class="widget-inside">' . "\n";
                $out .= '<label>' . __('URL', 'connections_form') . '</label><input type="text" name="social[' . $token . '][url]" value="' . $network->url . '" style="width: 30%"/>';
                $out .= '<input type="hidden" name="social[' . $token . '][id]" value="' . $network->id . '">' . "\n";
                $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="social" data-token="' . $token . '">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>';
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="social" data-container="social-media">' . __('Add Social Media ID', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function linkBlock($entry = null, $atts = array()) {
        global $connections;
        $form  = new cnFormObjects();
        $links = $entry->getLinks(array(), FALSE);
        $out   = '<div id="metabox-link" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Links', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $out .= '<div class="widgets-sortables ui-sortable form-field ' . ((in_array('link', $atts['open_blocks']) && empty($links)) ? " startwithone " : "") . '" id="links">';
        // --> Start template <-- \\
        $out .= '<textarea id="link-template" style="display: none">';
        $out .= '<div class="widget-top">' . "\n";
        $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
        $out .= '<div class="widget-title"><h4>' . "\n";
        $out .= __('Type', 'connections_form') . ': ' . $form->buildSelect('link[::FIELD::][type]', $connections->options->getDefaultLinkValues()) . "\n";
        $out .= '<label><input type="radio" name="link[preferred]" value="::FIELD::"> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
        //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('link[::FIELD::][visibility]', 'website_visibility_::FIELD::' . $this->visibiltyOptions, 'public' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="link[::FIELD::][visibility]" value="public">';
        $out .= '</h4></div>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div class="widget-inside">' . "\n";
        $out .= '<div>' . "\n";
        $out .= '<label>' . __('Title', 'connections_form') . '</label><input type="text" name="link[::FIELD::][title]" value="" style="width: 30%"/>' . "\n";
        $out .= '<label>' . __('URL', 'connections_form') . '</label><input type="text" name="link[::FIELD::][url]" value="http://" style="width: 30%"/>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '<div>' . "\n";
        //$out .= '<span class="target">Target: ' . $form->buildSelect('link[::FIELD::][target]', array( 'new' => 'New Window', 'same' => 'Same Window' ), 'same' ) . '</span>' . "\n";
        //$out .= '<span class="follow">' . $form->buildSelect('link[::FIELD::][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), 'nofollow' ) . '</span>' . "\n";
        $out .= '<input type="hidden" name="link[::FIELD::][target]" value="new">';
        $out .= '<input type="hidden" name="link[::FIELD::][follow]" value="nofollow">';
        $out .= '</div>' . "\n";
        if ($atts['photo'] || $atts['logo']) {
            $out .= '<div>' . "\n";
            if ($atts['photo'])
                $out .= '<label><input type="radio" name="link[image]" value="::FIELD::"> ' . __('Assign link to the image.', 'connections_form') . '</label>' . "\n";
            if ($atts['logo'])
                $out .= '<label><input type="radio" name="link[logo]" value="::FIELD::"> ' . __('Assign link to the logo.', 'connections_form') . '</label>' . "\n";
            $out .= '</div>' . "\n";
        }
        $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="link" data-token="::FIELD::">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '</div>' . "\n";
        $out .= '</textarea>';
        // --> End template <-- \\
        if (!empty($links)) {
            foreach ($links as $link) {
                $selectName = 'link[' . $token . '][type]';
                $preferredLink = ($link->preferred) ? 'CHECKED' : '';
                $imageLink = ($link->image) ? 'CHECKED' : '';
                $logoLink = ($link->logo) ? 'CHECKED' : '';
                $out .= '<div class="widget link" id="link-row-' . $token . '">' . "\n";
                $out .= '<div class="widget-top">' . "\n";
                $out .= '<div class="widget-title-action"><a class="widget-action"></a></div>' . "\n";
                $out .= '<div class="widget-title"><h4>' . "\n";
                $out .= __('Type', 'connections_form') . ': ' . $form->buildSelect($selectName, $connections->options->getDefaultLinkValues(), $link->type) . "\n";
                $out .= '<label><input type="radio" name="link[preferred]" value="' . $token . '" ' . $preferredLink . '> ' . __('Preferred', 'connections_form') . '</label>' . "\n";
                //$out .= '<span class="visibility">Visibility: ' . $form->buildRadio('link[' . $token . '][visibility]', 'link_visibility_'  . $token . $this->visibiltyOptions, $link->visibility ) . '</span>' . "\n";
                $out .= '<input type="hidden" name="link[' . $token . '][visibility]" value="public">';
                $out .= '</h4></div>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '<div class="widget-inside">' . "\n";
                $out .= '<div>' . "\n";
                $out .= '<label>' . __('Title', 'connections_form') . '</label><input type="text" name="link[' . $token . '][title]" value="' . $link->title . '" style="width: 30%"/>' . "\n";
                $out .= '<label>' . __('URL', 'connections_form') . '</label><input type="text" name="link[' . $token . '][url]" value="' . $link->url . '" style="width: 30%"/>';
                $out .= '</div>' . "\n";
                $out .= '<div>' . "\n";
                //$out .= '<span class="target">Target: ' . $form->buildSelect('link[' . $token . '][target]', array( 'new' => 'New Window', 'same'  => 'Same Window' ), $link->target ) . '</span>' . "\n";
                //$out .= '<span class="follow">' . $form->buildSelect('link[' . $token . '][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), $link->followString ) . '</span>' . "\n";
                $out .= '<input type="hidden" name="link[::FIELD::][target]" value="new">';
                $out .= '<input type="hidden" name="link[::FIELD::][follow]" value="nofollow">';
                $out .= '</div>' . "\n";
                if ($atts['photo'] || $atts['logo']) {
                    $out .= '<div>' . "\n";
                    if ($atts['photo'])
                        $out .= '<label><input type="radio" name="link[image]" value="' . $token . '" ' . $imageLink . '> ' . __('Assign link to the image.', 'connections_form') . '</label>' . "\n";
                    if ($atts['logo'])
                        $out .= '<label><input type="radio" name="link[logo]" value="' . $token . '" ' . $logoLink . '> ' . __('Assign link to the logo.', 'connections_form') . '</label>' . "\n";
                    $out .= '</div>' . "\n";
                }
                $out .= '<input type="hidden" name="link[' . $token . '][id]" value="' . $link->id . '">' . "\n";
                $out .= '<p class="cn-remove-button"><span class="cn-button-shell red"><a class="cn-remove cn-button" href="#" data-type="link" data-token="' . $token . '">' . __('Remove', 'connections_form') . '</a></span></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</div>' . "\n";
            }
        }
        $out .= '</div>';
        $out .= '<p class="cn-add"><span class="cn-button-shell blue"><a class="cn-add cn-button" data-type="link" data-container="links">' . __('Add Link', 'connections_form') . '</a></span></p>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function speicalDateBlock($entry = null, $atts = array()) {
        global $connections;
        $form = new cnFormObjects();
        $date = new cnDate();
        $out  = '<div id="metabox-note" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Dates', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        if ($atts['birthday']) {
            // Birthday Field
            $out .= '<div class="form-field celebrate">
							<span class="selectbox">' . __('Birthday', 'connections_form') . ': ' . $form->buildSelect('birthday_month', $date->months, $date->getMonth($entry->getBirthday())) . '</span>
							<span class="selectbox">' . $form->buildSelect('birthday_day', $date->days, $date->getDay($entry->getBirthday())) . '</span>
						</div>';
            $out .= '<div class="form-field celebrate-disabled"><p>' . __('Field not available for this entry type.', 'connections_form') . '</p></div>';
        }
        if ($atts['anniversary']) {
            // Anniversary Field
            $out .= '<div class="form-field celebrate">
							<span class="selectbox">' . __('Anniversary', 'connections_form') . ': ' . $form->buildSelect('anniversary_month', $date->months, $date->getMonth($entry->getAnniversary())) . '</span>
							<span class="selectbox">' . $form->buildSelect('anniversary_day', $date->days, $date->getDay($entry->getAnniversary())) . '</span>
						</div>';
            $out .= '<div class="form-field celebrate-disabled"><p>' . __('Field not available for this entry type.', 'connections_form') . '</p></div>';
        }
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
    }
    public static function categoryBlock($entry = null, $atts = array()) {
        global $connections;
        $form = new cnFormObjects();
        $out  = '<div id="metabox-category" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . __('Category', 'connections_form') . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        $level      = 0;
        $selected   = 0;
        $categories = $connections->retrieve->categories();
        $out .= "\n" . '<select class="cn-cat-select" id="cn-category" name="entry_category[]" multiple="true" data-placeholder="' . __('Select Categories', 'connections_form') . '" style="width:100%;">';
        $out .= "\n" . '<option value=""></option>';
        foreach ($categories as $key => $category) {
            $out .= self::buildOptionRowHTML($category, $level, $selected);
        }
        $out .= '</select>' . "\n";
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function bioBlock($entry = null, $atts = array()) {
        global $connections;
        $form = new cnFormObjects();
        $out  = '<div id="metabox-bio" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . $atts['str_bio'] . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        if ($atts['rte']) {
            ob_start();
            wp_editor($entry->getBio(), 'cn-form-bio', array(
                'media_buttons' => FALSE,
                'wpautop' => TRUE,
                'textarea_name' => 'bio',
                'tinymce' => array(
                    'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
                    'theme_advanced_buttons2' => '',
                    'inline_styles' => TRUE,
                    'relative_urls' => FALSE,
                    'remove_linebreaks' => FALSE,
                    'plugins' => 'inlinepopups,spellchecker,tabfocus,paste'
                ),
                'quicktags' => array(
                    'buttons' => 'strong,em,ul,ol,li,close'
                )
            ));
            $out .= ob_get_contents();
            ob_end_clean();
        } else {
            $out .= '<textarea rows="20" cols="40" name="bio" id="cn-form-bio"></textarea>';
        }
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    public static function notesBlock($entry = null, $atts = array()) {
        global $connections;
        $form = new cnFormObjects();
        $out  = '<div id="metabox-note" class="postbox">';
        $out .= '<h3 class="hndle"><span>' . $atts['str_notes'] . '</span></h3>';
        $out .= '<div class="cnf-inside">';
        if ($atts['rte']) {
            ob_start();
            wp_editor($entry->getNotes(), 'cn-form-notes', array(
                'media_buttons' => FALSE,
                'wpautop' => TRUE,
                'textarea_name' => 'notes',
                'tinymce' => array(
                    'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
                    'theme_advanced_buttons2' => '',
                    'inline_styles' => TRUE,
                    'relative_urls' => FALSE,
                    'remove_linebreaks' => FALSE,
                    'plugins' => 'inlinepopups,spellchecker,tabfocus,paste'
                ),
                'quicktags' => array(
                    'buttons' => 'strong,em,ul,ol,li,close'
                )
            ));
            $out .= ob_get_contents();
            ob_end_clean();
        } else {
            $out .= '<textarea rows="20" cols="40" name="notes" id="cn-form-notes"></textarea>';
        }
        $out .= '<div class="cn-clear"></div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
}