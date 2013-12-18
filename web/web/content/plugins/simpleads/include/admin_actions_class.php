<?php
if (! class_exists('SIMPLEADS_Admin_Actions')) {
    class SIMPLEADS_Admin_Actions {
        
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
            $this->parent = $this->simpleads;
        }
        
        /*************************************
         * method: admin_init
         */        
        function admin_init() {
            // SimpleAds Ad Interface Extra Data
            // *should we check we are on simpleads create ad page first?*
            //
            add_meta_box('render_content_area_ui', __('Ad Information',$this->simpleads->prefix), 
                array('SIMPLEADS_Ad_EditUI','render_content_area_ui')  , 
                'simpleads_ad', 'normal', 'high'
                );            
            
            // WordPress Built-In Image Box
            add_meta_box('postimagediv', __('Ad Graphic',$this->simpleads->prefix), 
                'post_thumbnail_meta_box', 
                'simpleads_ad', 'normal', 'core');

            require_once($this->parent->plugin_dir . 'include/admin_ui_class.php');
            $this->parent->AdminUI = new SIMPLEADS_AdminUI();

        }            
        
        /**
        * Creates a settings page
        * @param mixed $tabs Currently existing tabs
        * @return mixed the array of new tabs
        */
        function settings($tabs) {
            // Add sections to our SimpleAds Plugin
            // *This is only called when visible*
            //

            $tabs['Settings'] = array (             // The tab to add - can be any arbitrary name
                'name'          => 'Settings',      // The display name to show the world (Will be automagically translated)
                'content'       => array(           // An array of sections
                    'default settings' => array(    // The section to create
                        'name'              => 'Default Settings',
                        'description'       => 'Set the default values for shortcodes and widgets. These settings are optional.',
                        'start_collapsed'   => false,
                        'items'             => array(   // An array of items
                            'adid'          => array(
                                'display_name'      => 'Ad ID',
                                'name'              => 'adid',
                                'type'              => 'text',
                                'required'          => false,
                                'description'       => 'If no ID is specified, show this ad.  A Pro Pack feature.',
                                'custom'            => null,
                                'value'             => null,
                                'disabled'          => true,    //show the option grayed out
                                'action'            => null     //action to call when the option is changed (not yet implemented)
                            ),
                            'shorthand'     => array(
                                'display_name'      => 'Ad Shorthand',
                                'name'              => 'shorthand',
                                'type'              => 'text',
                                'required'          => false,
                                'description'       => 'If no ID is specified, show this ad.  A Pro Pack feature.',
                                'custom'            => null,
                                'value'             => null,
                                'disabled'          => true,
                                'action'            => null
                            )
                        )
                    )
                )
            );

            return $tabs;
        }
            
        /*************************************
         * method: admin_print_styles
         */
        function admin_print_styles() {
            global $post;
            if (
                ($this->simpleads->wsuwp->isOurAdminPage ||
                    (isset($post) && ($post->post_type == 'simpleads_ad'))
                ) &&            
                file_exists($this->simpleads->plugin_dir.'css/admin.css')
                ) {
                    wp_enqueue_style('csl_simpleads_admin_css', $this->simpleads->plugin_url .'/css/admin.css'); 
            }
        } 
        
        /*************************************
         * method: manage_posts_custom_column
         */
        function manage_posts_custom_column($column)
        {
            global $post;
            switch ($column) {
                case 'id':
                    echo $post->ID;
                    break;
                case 'shorthand':
                    echo $post->post_name;
                    break;
                case 'status':
                    echo $post->post_status;
                    break;
                case 'destination':
                    $theURL = apply_filters($this->simpleads->prefix."MetaValue", $post->ID,$column);
                    echo apply_filters($this->simpleads->prefix."Change Destination", $theURL, "CSA");
                    break;
                case 'graphic':                    
                    echo get_the_post_thumbnail( $post->ID);
                    break;
                default:                    
                    echo apply_filters($this->simpleads->prefix."MetaValue", $post->ID,$column);
            }
        }

        /**
         *
         * @param type $theURL
         * @param type $target
         * @return type
         */
        function processDestination($theURL, $target) {
            return '<a href="'.$theURL.'" target="'.$target.'">'.$theURL.'</a>';
        }

        /*************************************
         * method: save_post
         */
        function save_post($post_id)
        {     
                        
            // User is not allowed to edit pages - skip this
            //
            if (!current_user_can( 'edit_page', $post_id )) {
                return;
            }
            
            
            // Save Ads
            //
            if (isset($_POST['post_type'])  && ('simpleads_ad' == $_POST['post_type']))  {            
                SIMPLEADS_Ad_EditUI::save_post();
            }
        }      
        
        //------------------------------------------
        // HELPERS
        //------------------------------------------
        
        
        /*************************************
         * method: getMetaValue
         * 
         * returns the value of a meta field for a given postID
         */        
        function getMetaValue($postid,$name) {
            $custom = get_post_custom($postid);
            return (isset($custom[$this->simpleads->prefix.'-'.$name])?$custom[$this->simpleads->prefix.'-'.$name][0]:'');                
        }
                
    }
}        
     

if (! class_exists('SIMPLEADS_Ad_EditUI')) {
    class SIMPLEADS_Ad_EditUI {
        
        /*************************************
         * method: render_content_area_ui
         */
        public static function render_content_area_ui() {
            $simpleads = $GLOBALS['SimpleAds'];
            global $post;            

            // The JS to open the more info button
            //
            echo 
            '<script type="text/javascript">' .
                'jQuery(document).ready(function($) {' .
                    "$('.".$simpleads->wsuwp->css_prefix."-moreicon').click(function(){".
                        "$(this).siblings('.".$simpleads->wsuwp->css_prefix."-moretext').toggle();".
                        '});'.
                    '});'.         
            '</script>'
            ;            
            
            // The input boxes
            //
            print '<div class="'.$simpleads->wsuwp->css_prefix.'-metabox-parent">';
            SIMPLEADS_Ad_EditUI::render_meta_input(
                'id',
                __('Ad ID', $simpleads->prefix),
                sprintf(
                    __('The ad ID, you can use this or the permalink shorthand to display ads. [simpleads id="%s"]', $simpleads->prefix),
                    $post->ID                    
                    ),
                false,
                $post->ID
                );            
            SIMPLEADS_Ad_EditUI::render_meta_input(
                'shorthand',
                __('Ad Shorthand', $simpleads->prefix),
                sprintf(
                    __('The ad shorthand. You can use this or the ad id to display ads. [simpleads shorthand="%s"]', $simpleads->prefix),
                    $post->post_name                 
                    ),
                false,
                $post->post_name
                );            
            SIMPLEADS_Ad_EditUI::render_meta_input(
                'destination',
                __('Destination', $simpleads->prefix),
                __('The URL you want the ad to go to, fully qualified. (i.e. http://www.charlestonsw.com)', $simpleads->prefix)
                );
            print '</div>';            
        }
        
        /*************************************
         * method: save_post
         */
        public static function save_post() {
			$simpleads = $GLOBALS['SimpleAds'];
            update_post_meta($_POST['ID'], $simpleads->prefix.'-'.'destination', $_POST[$simpleads->prefix.'-'.'destination']);            
        }    
        
        /*************************************
         * method: render_meta_input
         */
       public static function render_meta_input($name,$label,$description,$enabled=true,$value=null) {
            global $post;     
            $simpleads = $GLOBALS['SimpleAds'];
            $custom = get_post_custom($post->ID);            
            if ($value == null) {
                $value = (isset($custom[$simpleads->prefix.'-'.$name])?$custom[$simpleads->prefix.'-'.$name][0]:'');                
            }
            print 
                '<div class="'.$simpleads->wsuwp->css_prefix.'-metabox">'.
                '<div class="'.$simpleads->wsuwp->css_prefix.'-input' . ($enabled?'':'-disabled').'">' .                
                    '<label>'.$label.'</label>' .
                    '<input type="text" name="'.$simpleads->prefix.'-'.$name.'" value="'.$value.'" '. ($enabled?'':'disabled="disabled"').'/>' .
                    '</div>' .
                    '<div class="'.$simpleads->wsuwp->css_prefix.'-moreicon" title="click for more info"><br/></div>' .
                    '<div class="'.$simpleads->wsuwp->css_prefix.'-moretext">' .
                        $description .
                    '</div>' .
                '</div>'
                ;
             
         }
        
    }
}


