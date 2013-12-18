<?php
if (! class_exists('SIMPLEADS_AdminUI')) {
    class SIMPLEADS_AdminUI {
        
        /******************************
         * PUBLIC PROPERTIES & METHODS
         ******************************/
        /** SIMPLEADS main plugin */
        public $parent = null;

        /**
         * The Constructor
         */
        function __construct() {
            $this->setParent();
        }

        /**
         * Set the parent property to point to the primary plugin object.
         *
         * Returns false if we can't get to the main plugin object.
         *
         * @global wpCSL_plugin__slplus $slplus_plugin
         * @return type boolean true if plugin property is valid
         */
        function setParent() {
            if (!isset($this->parent) || ($this->parent == null)) {
                $this->parent = $GLOBALS['SimpleAds'];
            }
            return (isset($this->parent) && ($this->parent != null));
        }        



    }
}        
     

