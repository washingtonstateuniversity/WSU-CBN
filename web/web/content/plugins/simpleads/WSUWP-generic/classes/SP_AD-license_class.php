<?php
class wsuwp_license__simpleads {    

    /**------------------------------------
     ** CONSTRUCTOR
     **/
    function __construct($params) {
        
        // Defaults
        //

        // Set by incoming parameters
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        
        // Override incoming parameters
        
    }

    /**------------------------------------
     ** method: check_license_key()
     **
     ** Currently only checks for an existing license key (PayPal
     ** transaction ID).
     **/
    function check_license_key($theSKU='', $isa_package=false, $usethis_license='', $force = false) {
    	return true;
	}

    /**------------------------------------
     ** method: AmIEnabled
     ** Parameters: $isa_package = is it a package or the main product
     **             $theSKU = the sku of the product
     ** Returns: True if enabled/purchased, false if not
     **/
    function AmIEnabled($isa_package, $theSKU) {
        if (!$isa_package) {
                return get_option($this->prefix.'-purchased',false);

                // add on package
            } else {
                return get_option($this->prefix.'-'.$theSKU.'-isenabled',false);
            }
    }

    /**------------------------------------
     ** method: check_product_key()
     **
     **/
    function check_product_key() {
        return true;
    }

    /**------------------------------------
     ** method: initialize_options()
     **
     **/
    function initialize_options() {
        register_setting($this->prefix.'-settings', $this->prefix.'-license_key');
        register_setting($this->prefix.'-settings', $this->prefix.'-purchased');
        
        if ($this->has_packages) {
            foreach ($this->packages as $aPackage) {
                $aPackage->initialize_options_for_admin();
            }
        }            
    }
    
    /**------------------------------------
     ** method: add_licensed_package()
     **
     ** Add a package object to the license object.
     **
     ** Packages are components that have their own license keys to be
     ** activated, but are always related to a parent product with a valid
     ** license.
     **
     **/
    function add_licensed_package($params) {
        
        // If we don't have a package name or SKU get outta here
        //
        if (!isset($params['name']) || !isset($params['sku'])) return;

        // Setup the new package only if it was not setup before
        //
        if (!isset($this->packages[$params['name']])) {
            $this->packages[$params['name']] = new wsuwp_license_package__simpleads(
                array_merge(
                    $params,
                    array(
                        'prefix' => $this->prefix,
                        'parent' => $this
                        )
                    )
            );
        } 
   }
    
}


/****************************************************************************
 **
 ** class: wsuwp_license_package__simpleads
 **
 **/
class wsuwp_license_package__simpleads {

    public $active_version = 0;
    public $force_enabled = false;
    
    /**------------------------------------
     **/
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        
        // Register these settings
        //
        $this->enabled_option_name = $this->prefix.'-'.$this->sku.'-isenabled';
        $this->lk_option_name      = $this->prefix.'-'.$this->sku.'-lk';
         
        // If the isenabled flag is not explicitly passed in,
        // set this package to the pre-saved enabled/disabled setting from wp_options
        // which will return false if never set before
        //
        $this->isenabled = ($this->force_enabled || get_option($this->enabled_option_name));        
        
        // Set our license key property
        //
        $this->license_key = get_option($this->lk_option_name);
        
        // Set our active version (what we are licensed for)
        //
        $this->active_version =  (isset($this->force_version)?$this->force_version:get_option($this->prefix.'-'.$this->sku.'-latest-version-numeric'));
    }
    
    
    /**------------------------------------
     ** method: initialize_options_for_admin
     **
     ** Initialize the admin option settings.
     **/
    function initialize_options_for_admin() {
        register_setting($this->prefix.'-settings', $this->lk_option_name);
    }
    
    function isenabled_after_forcing_recheck() {
        // Now attempt to license ourselves, make sure we license as
        // siblings (second param) in order to properly set all of the
        // required settings.
        if (!$this->isenabled) {

            // License is OK - mark it as such
            //
            $this->isenabled = $this->parent->check_license_key($this->sku, true, get_option($this->lk_option_name));
            update_option($this->enabled_option_name,$this->isenabled);
            $this->active_version =  get_option($this->prefix.'-'.$this->sku.'-latest-version-numeric');
        }

        // Attempt to register the parent if we have one
        $this->parent->check_license_key($this->sku, true);

        return $this->isenabled;
    }
}
