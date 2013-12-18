<?php
if (! class_exists('SIMPLEADS_UserInterface')) {
    class SIMPLEADS_UserInterface {
        
        /******************************
         * PUBLIC PROPERTIES & METHODS
         ******************************/
        /** SIMPLEADS main plugin class */
        var $simpleads;

        /*************************************
         * The Constructor
         */
        function __construct() {
            $this->simpleads = $GLOBALS['SimpleAds'];
        } 

        
        /*************************************
         * method: render_shortcode
         *
         * Allows attributes:
         *
         *  id = numeric, the ID number of the ad to display
         *  shorthand = string, the shorthand id of the ad to display
         *
         */
        function render_shortcode($params=null) {

            $this->simpleads->wpcsl->shortcode_was_rendered = true;

            // If we are a widget, clear blank params
            //
            if ($this->simpleads->is_widget) {
                foreach ($params as $key=>$value) {
                    if ($params[$key] === '') {
                        unset($params[$key]);
                    }
                }
            }

            // Set the attributes, default or passed in shortcode
            //
            $this->simpleads->Attributes = shortcode_atts(
                array(
                        'id'        => $this->simpleads->wpcsl->settings->get_item('adid', -1),
                        'shorthand' => $this->simpleads->wpcsl->settings->get_item('shorthand','')
                    ), 
                $params
                );
            $custom = array();

            // Use shorthand to get ID if ID not set
            //
            if ( 
                  ( ($this->simpleads->Attributes['id'] < 0) ||
                    ($this->simpleads->Attributes['id'] === '') 
                  ) && 
                    ($this->simpleads->Attributes['shorthand'] != '')
                ){   
                $postInfo = get_posts(
                    array(
                        'post_type' => 'simpleads_ad',
                        'name' => $this->simpleads->Attributes['shorthand']     
                        )
                    );   
                $this->simpleads->Attributes['id']  = $postInfo[0]->ID;
            }
            
            // If ID is set, use that to get custom fields
            if ($this->simpleads->Attributes['id'] > 0) {
                
                // Post type is not an simpleads ad - get out
                //
                if (get_post_type($this->simpleads->Attributes['id']) != 'simpleads_ad') { return; }

                // Get the attributes for the ad
                //
                $this->simpleads->Attributes = array_merge($this->simpleads->Attributes, get_post_custom($this->simpleads->Attributes['id']));
                
                // Set The Image
                $this->simpleads->Attributes['thumbnails'] =
                    wp_get_attachment_image_src(
                        get_post_thumbnail_id( $this->simpleads->Attributes['id'] )
                    );

                // Setup The Ad
                //
                $adContent = apply_filters($this->simpleads->prefix."RenderAd",
                    '<a href="'.$this->simpleads->Attributes[$this->simpleads->prefix.'-destination'][0].'">' .
                        '<img src="' . $this->simpleads->Attributes['thumbnails'][0] . '" />'.                
                    '</a>'
                    );
                return $adContent;


            }
            
            return '';
        }
        
    }
}        
     

