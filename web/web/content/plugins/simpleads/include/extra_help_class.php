<?php
if (! class_exists('SIMPLEADS_Extra_Help')) {
    class SIMPLEADS_Extra_Help {
        
        var $simpleads;

            /*************************************
             * The Constructor
             */
            function __construct($params) {
                foreach ($params as $name => $value) {            
                    $this->$name = $value;
                }         
                
                $this->add_options_packages();
            }        

            /**************************************
             ** function: add_options_packages
             **
             ** This is where add-on initializations go.
             ** If you add something other than the propack
             ** create a method for it, then hook it in here.
             **
             **/
            function add_options_packages() {
                $this->configure_propack();
            }
            
            /**************************************
             ** function: configure_propack
             **
             ** Configure the Pro Pack.
             ** Requires parent to be passed in the constructor.
             ** Parent is set to the instantiated WSUWP object.
             **
             ** USE THIS (it checks to see if the license is enabled first)...
             ** Pro Pack Enabled : after forcing a retest of the license:
             ** $simpleads_plugin->license->packages['Pro Pack']->isenabled_after_forcing_recheck()
             **
             ** Pro Pack Version 2.4 is licensed (version is cast to 0 padded int)
             ** $simpleads_plugin->license->packages['Pro Pack']->isenabled &&
             ** $simpleads_plugin->license->packages['Pro Pack']->active_version >= 2004000
             **
             **/
            function configure_propack() {
                $this->parent->license->add_licensed_package(
                        array(
                            'name'              => 'Pro Pack',
                            'help_text'         =>
                                sprintf(
                                    __('A variety of enhancements are provided with this package.  ' .
                                        'See the <a href="%s" target="CSA">product page</a> for details.  '.
                                        'If you purchased this add-on enter the license key to activate the new features.',
                                        $this->parent->prefix
                                        ),
                                    $this->parent->purchase_url
                                    ),
                            'sku'               => 'SIMPLEADS',
                            'paypal_button_id'  => '86NWWUHH9PTCC'
                        )
                    );
                
                $this->parent->propack_enabled = $this->parent->license->packages['Pro Pack']->isenabled_after_forcing_recheck();
                
            }
    }
}


