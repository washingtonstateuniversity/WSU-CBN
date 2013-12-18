<?php

/**
 * We need the generic WSUWP plugin class, since that is the
 * foundation of much of our plugin.  So here we make sure that it has
 * not already been loaded by another plugin that may also be
 * installed, and if not then we load it.
 */
if (defined('SIMPLEADS_PLUGINDIR')) {
    if (class_exists('wsuwp_plugin__simpleads') === false) {
        require_once(SIMPLEADS_PLUGINDIR.'WSUWP-generic/classes/SP_AD-plugin.php');
    }
    
    /**
     * This section defines the settings for the admin menu.
     */ 
    global $simpleads_plugin;
    $simpleads_plugin = new wsuwp_plugin__simpleads(
        array(
            'prefix'                => SIMPLEADS_PREFIX,
            'name'                  => 'SimpleAds',
            'sku'                   => 'SIMPLEADS',
            
            'url'                   => '',            
            'support_url'           => '',
            
            'basefile'              => SIMPLEADS_BASENAME,
            'plugin_path'           => SIMPLEADS_PLUGINDIR,
            'plugin_url'            => SIMPLEADS_PLUGINURL,
            'cache_path'            => SIMPLEADS_PLUGINDIR . 'cache',
            
            // We don't want default wsuwp objects, let's set our own
            //
            'use_obj_defaults'      => false,
            
            'cache_obj_name'        => 'none',
            'products_obj_name'     => 'none',
            
            'helper_obj_name'       => 'default',
            'notifications_obj_name'=> 'default',
            'settings_obj_name'     => 'default',
            
            // Licensing and Packages
            //
            'license_obj_name'      => 'default',            
            'url'                   => '',            
            'support_url'           => '',
            'purchase_url'          => '',                        
            'has_packages'          => true,            
            
            
            // Themes and CSS
            //
            'display_settings'      => false,
            'themes_obj_name'       => 'none',
            'themes_enabled'        => false,
            'css_prefix'            => 'csl_themes',
            'css_dir'               => SIMPLEADS_PLUGINDIR . 'css/',
            'no_default_css'        => true,
            
            // Custom Config Settings
            //
            'display_settings_collapsed'=> false,
            'show_locale'               => false,            
            'uses_money'                => false,
            
            'driver_type'           => 'none',
            'driver_args'           => array(
                    ),
        )
    );   
    
    // Setup our optional packages
    //
    require_once(SIMPLEADS_PLUGINDIR . 'include/extra_help_class.php');    
    $simpleads_plugin->extrahelper = new SIMPLEADS_Extra_Help(
        array(
            'parent' => $simpleads_plugin
            )
        );
}    

