<?php
if (! class_exists('SIMPLEADS_Actions')) {
    class SIMPLEADS_Actions {
        
        /******************************
         * PUBLIC PROPERTIES & METHODS
         ******************************/
        /** SIMPLEADS Plugin */
        var $simpleads;

        /*************************************
         * The Constructor
         */
        function __construct() {
            $this->simpleads = $GLOBALS['SimpleAds'];
        }
        
        
        /**************************************
         ** method: init()
         **
         ** Called when the WordPress init action is processed.
         **          
         ** WordPress builtin post types:
         ** 
         ** post - WordPress built-in post type
         ** page - WordPress built-in post type
         ** mediapage - WordPress built-in post type
         ** attachment - WordPress built-in post type
         ** revision - WordPress built-in post type
         ** nav_menu_item - WordPress built-in post type (Since 3.0)
         ** custom post type - any custom post type (Since 3.0)          
         **
         **/
        function init() {
            
            // Register Store Pages Custom Type
            register_post_type( 'simpleads_ad',
                array(
                    'labels' => array(
                        'name'              => __( 'SimpleAds Ads', $this->simpleads->prefix ),
                        'singular_name'     => __( 'SimpleAds Ad', $this->simpleads->prefix ),
                        'add_new'           => __('Create An Ad', $this->simpleads->prefix),
                        'add_new_item'      => __('Create New Ad', $this->simpleads->prefix),
                        'edit_item'         => __('Edit Ad', $this->simpleads->prefix),
                        'view_item'         => __('View Ad', $this->simpleads->prefix),
                        'search_items'      => __('Search Ads', $this->simpleads->prefix),
                        'not_found'         => __('No ads found.', $this->simpleads->prefix),
                        'not_found_in_trash'=> __('No ads found in trash.', $this->simpleads->prefix),
                        'all_items'         => __('List Ads', $this->simpleads->prefix),
                    ),
                'public'            => true,
                'has_archive'       => true,
                'description'       => __('SimpleAds ads.',$this->simpleads->prefix),
                'menu_postion'      => 20,   
                'menu_icon'         => $this->simpleads->plugin_url . '/images/simpleads_menuicon_16x16.png',
                'capability_type'   => 'page',
                'supports'          => 
                    array(
                            'title',
                            'revisions',
                            'thumbnail'
                        ),
                )
            );                
            
            // Register Stores Taxonomy
            //                
            register_taxonomy(
                    'ads',
                    'ad',
                    array (
                        'hierarchical'  => true,
                        'labels'        => 
                            array(
                                    'menu_name' => __('SimpleAds Ads',$this->simpleads->prefix),
                                    'name'      => __('Ad Attributes',$this->simpleads->prefix),
                                 )
                        )
                );                
        }        
    }
}        
     

