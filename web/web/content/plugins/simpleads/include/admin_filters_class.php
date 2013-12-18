<?php
if (! class_exists('SIMPLEADS_Admin_Filters')) {
    class SIMPLEADS_Admin_Filters {
        
        /******************************
         * PUBLIC PROPERTIES & METHODS
         ******************************/
        /** SIMPLEADS main plugin */
        var $simpleads;

        /*************************************
         * The Constructor
         */
        function __construct() {
            $this->simpleads = $GLOBALS['SimpleAds'];
        }
        
        /*************************************
         * method: simpleads_ad_columns
         */                        
        function simpleads_ad_columns($columns) {
            return 
                array(
                    'id'            => __('ID', $this->simpleads->prefix)          ,
                    'shorthand'     => __('Shorthand', $this->simpleads->prefix)  ,
                    'title'         => __('Name', $this->simpleads->prefix)       ,
                    'graphic'       => __('Graphic',$this->simpleads->prefix),
                    'destination'   => __('Destination', $this->simpleads->prefix),
                    'status'        => __('Status', $this->simpleads->prefix),
                    'date'          => __('Date', $this->simpleads->prefix)
                );
        }       
    }
}        
     

