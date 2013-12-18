<?php
/*
Plugin Name: Ad Manager
Version: 0.9.4
Plugin URI: http://digitalnature.eu/forum/plugins/ad-manager/
Description: Manage ads on your website trough the WP dashboard
Author: digitalnature
Author URI: http://digitalnature.eu/
Text Domain: ad_manager
Domain Path: /lang
*/



/*
 * Public methods you can call from outside:
 *
 *   AdManager()->registerAdLocation($location, $label)  - Register a custom ad location (relevant within themes)
 */


require dirname(__FILE__).'/ad-types.php';



/*
 * AdManager Class
 *
 * @since 1.0
 */
class AdManager{



  const
    VERSION     = '0.9.4',                                             // plugin version
    ID          = 'ad_manager',                                        // internally used, mostly for text domain
    PROJECT_URI = 'http://digitalnature.eu/forum/plugins/ad-manager/'; // plugin project page

  protected static
    $instance = null;


  protected

    // current plugin options, includes ads
    $options                     = null,

    // custom ad locations registered by themes or plugins
    $theme_ad_locations          = array(),

    // holds registered ad types
    $ad_types                    = array(),

    // for locations that require post / comment index
    $location_index_conditions   = array(),

    // before / after / half position records for default locations
    $default_location_positions  = array(),

    // for locations that require specific pages
    $location_page_conditions    = array(),

    // visible ads queue
    $queue                       = array(),

    // default option values
    $defaults                    = array(

                                   // version for the options, needed during updates
                                   'version'    => self::VERSION,

                                   // we're storing the ads here; no need for a custom db table,
                                   // because there shouldn't be that many ads on a typical site...
                                   'data'       => array(),

                                 );



 /*
  * This will instantiate the class if needed, and return the only class instance if not...
  *
  * @since 1.0
  */
  public static function app(){

    // first run?
    if(!(self::$instance instanceof self)){

      self::$instance = new self();

      // localize
      load_plugin_textdomain(self::ID, false, dirname(plugin_basename(__FILE__)).'/lang');

      // admin hooks
      if(is_admin()){
        add_action('admin_menu',             array(self::$instance, 'createMenu'));
        add_action('admin_init',             array(self::$instance, 'registerSettings'));

        add_action('wp_ajax_ad_form',        array(self::$instance, 'adForm'));
        add_action('wp_ajax_process_ad',     array(self::$instance, 'processAd'));
        add_action('wp_ajax_change_ad_type', array(self::$instance, 'changeAdType'));
        add_action('wp_ajax_scan_type_html', array(self::$instance, 'scanTypeHTML'));
        add_action('wp_ajax_get_ad_stats',   array(self::$instance, 'getAdStats'));

      // front-end hooks
      }else{
        add_action('wp', array(self::$instance, 'run'));

        add_action('init', array(self::$instance, 'trackAds'));

        // register [ad] shortcode
        add_shortcode('ad', array(self::$instance, 'shortcode'));
      }

      // register widget
      add_action('widgets_init', array(self::$instance, 'widget'));

      // run on plugin uninstall; not sure when does this run?!
      register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

    }

    return self::$instance;
  }



 /*
  * A single instance only
  *
  * @since 1.0
  */
  final protected function __construct(){

    // first call initializes plugin options
    $this->getOptions();

    $this->registerAdType('AdHTML');
    $this->registerAdType('AdImageLink');
    //$this->registerAdType('AdAdSense'); // @todo

  }



 /*
  * No cloning
  *
  * @since 1.0
  */
  final protected function __clone(){}




  /*
   * Returns one or all plugin options.
   *
   * @since   1.0
   * @param   string $key   Option to get; if not given all options are returned
   * @return  mixed         Option(s)
   */
  public function getOptions($key = false){

      // first call, initialize the options
    if(!isset($this->options)){

      $options = get_option(self::ID);

      // options exist
      if($options !== false){

        $new_version = version_compare($options['version'], self::VERSION, '!=');
        $desync = array_diff_key($this->defaults, $options) !== array_diff_key($options, $this->defaults);

        // update options if version changed, or we have missing/extra (out of sync) option entries
        if($new_version || $desync){

          $new_options = array();

          // check for new options and set defaults if necessary
          foreach($this->defaults as $option => $value)
            $new_options[$option] = isset($options[$option]) ? $options[$option] : $value;

          // update version info
          $new_options['version'] = self::VERSION;

          update_option(self::ID, $new_options);
          $this->options = $new_options;

        // no update was required
        }else{
          $this->options = $options;
        }


      // new install (plugin was just activated)
      }else{
        update_option(self::ID, $this->defaults);
        $this->options = $this->defaults;
      }
    }

    return $key ? $this->options[$key] : $this->options;
  }



  /*
   * Set current options
   *
   * @since   1.0
   * @param   array $options Options
   */
  public function setOptions($options){
    update_option(self::ID, $options);
    $this->options = $options;
  }



  /*
   * Loads a template file from the theme or child theme directory.
   *
   * @since   1.0
   * @param   string $_name   Template name, without the '.php' suffix
   * @param   array $_vars    Variables to expose in the template. Note that unlike WP, we're not exposing all the global variable mess inside it...
   */
  final public function loadTemplate($_name, $_vars = array()){

    // you cannot let locate_template to load your template
    // because WP devs made sure you can't pass
    // variables to your template :(
    $_located = locate_template($_name, false, false);

    // use the default one if the (child) theme doesn't have it
    if(!$_located)
      $_located = dirname(__FILE__).'/templates/'.$_name.'.php';

    unset($_name);

    // create variables
    if($_vars)
      extract($_vars);

    // load it
    require $_located;
  }



 /*
  * Hook our plugin options menu / page
  *
  * @since 1.0
  */
  public function createMenu(){
    $page = add_options_page(__('Ad Manager', self::ID), __('Ad Manager', self::ID), 'manage_options', self::ID, array($this, 'SettingsPage'));
    add_action("admin_print_scripts-{$page}", array($this, 'assets'));
  }



 /*
  * Register our setting with the new useless Settings API bloat...
  *
  * @since 1.0
  */
  public function registerSettings(){
    add_filter('plugin_action_links', array($this, 'pluginSettingsLink'), 10, 2);
  }



 /*
  * Settings link in the plugin list
  *
  * @since    1.0
  * @param    string $file
  * @param    array $links
  * @return   array
  */
  public function pluginSettingsLink($links, $file){

    if(plugin_basename(__FILE__) === $file){
      $settings_link = '<a href="'.add_query_arg(array('page' => self::ID), admin_url('options-general.php')).'">'.__('Manage Ads', self::ID).'</a>';
      array_unshift($links, $settings_link);
    }

    return $links;
  }



 /*
  * Get currently registered ads
  *
  * @since    1.0
  * @return   array
  */
  public function getRegisteredAds($id = false){
    $data = $this->getOptions('data');

    if($id !== false)
      return isset($data[$id]) ? $data[$id] : false;

    return $data;
  }



 /*
  * Replaces all ad data.
  *
  * @since    1.0
  * @param    array $data   New data
  */
  public function updateRegisteredAds($data){
    $options = $this->getOptions();
    $options['data'] = $data;
    $this->setOptions($options);
  }



 /*
  * Update properties of a specific ad.
  * If $key_or_data is an array, all existing properties will be replaced with this value,
  * otherwise this is considered a key, and $value will be used as the key's value.
  *
  * @since    1.0
  * @param    int $id                          Ad ID
  * @param    string|array|bool $key_or_data   Property to update, or the values of all properties as an array; if "false", the ad will be removed
  * @param    mixed                            Optional, new value of the property (if a key was given)
  * @return   bool
  */
  public function updateRegisteredAd($id, $key_or_data, $value = ''){
    $options = $this->getOptions();

    // ad doesn't exist; we should never reach this point
    if(!isset($options['data'][$id]))
      return false;

    if(is_array($key_or_data))
      $options['data'][$id] = $key_or_data;

    elseif($key_or_data !== false)
      $options['data'][$id][$key_or_data] = $value;

    else
      unset($options['data'][$id]);

    $this->setOptions($options);

    return true;
  }



 /*
  * Allow other plugins/themes to register new ad types.
  * You must provide the class name as argument
  *
  * @since    1.0
  * @param    string $type
  */
  public function registerAdType($type){
    $object = new $type;

    if(!($object instanceof AdManagerType))
      throw new Exception('The class must be a child of AdManagerType!');

    $this->ad_types[$type] = $object;
  }



 /*
  * Allow themes to register ad locations.
  * Atom themes will do this, and hopefully other themes will too ;)
  *
  * @since    1.0
  * @param    string $id
  * @param    string $label
  * @param    array $pages
  */
  public function registerAdLocation($id, $label = '', $pages = array()){

    // can register multiple locations at once if first argument is an array
    $locations = is_array($id) ? $id : array($id => $label);

    foreach($locations as $id => $label){
      $this->theme_ad_locations["theme:{$id}"] = $label ? $label : $id;
      $this->location_page_conditions["theme:{$id}"] = $pages;
    }
  }



 /*
  * Get a list of valid ad locations.
  *
  * @since    1.0
  * @param    string $single   Only retrieve locations of this type
  * @return   array
  */
  protected function getAdLocations($single = false){

    $locations = apply_filters('ad_manager_locations', array(

      // locations registered by themes (or other plugins)
      'theme'     => $this->theme_ad_locations,

      // built-in locations
      // p: and c: denote post / comment context
      'defaults'  => array(
                       'p:before_post:index'     => __('Prepend to post content', self::ID),
                       'p:half_post:index'       => __('Half-way trough post content (experimental)', self::ID),
                       'p:after_post:index'      => __('Append to post content', self::ID),
                       'c:before_comment:index'  => __('Prepend to comment content', self::ID),
                       'c:half_comment:index'    => __('Half-way trough comment content (experimental)', self::ID),
                       'c:after_comment:index'   => __('Append to comment content', self::ID),
                     ),

      'other'     => array(
                       'shortcode'      => __('Use shortcode', self::ID),
                       'action'         => __('Custom action', self::ID),
                     ),
    ));

    if(!$single)
      return $locations;

    foreach($locations as $group)
      foreach($group as $location => $label)
         if($location === $single) return $label;
  }



 /*
  * Get a list of valid ad types.
  *
  * @since    1.0
  * @return   array
  */
  protected function getAdTypes(){

    $types = array();

    foreach($this->ad_types as $id => $object)
      $types[$id] = $object->getLabel();

    return $types;
  }



 /*
  * Get a list of valid page contexts.
  *
  * @since    1.0
  * @return   array
  */
  protected function getAdPages($single = false){
    $pages = array(
      'any'        => __('Auto (any page with this location)', self::ID),
      'home'       => __('Blog home', self::ID),
    );

    foreach(get_post_types(array('public' => true)) as $post_type){
      $object = get_post_type_object($post_type);
      if(empty($object->labels->name)) continue;
      $pages["singular:{$post_type}"] = sprintf(__('Single: %s', self::ID), $object->labels->name);
    }

    $pages['category'] = __('Category archives', self::ID);
    $pages['tag']       = __('Tag archives', self::ID);
    $pages['author']   = __('Author archives', self::ID);
    $pages['date']     = __('Date-based archives', self::ID);
    $pages['search']   = __('Search results', self::ID);

    foreach(get_taxonomies(array('public' => true, '_builtin' => false)) as $taxonomy){
      $object = get_taxonomy($taxonomy);
      if(empty($object->labels->name)) continue;
      $pages["tax:{$taxonomy}"] = sprintf(__('Taxonomy archive: %s', self::ID), $object->labels->name);
    }

    $pages = apply_filters('ad_manager_pages', $pages);

    if($single)
     return isset($pages[$single]) ? $pages[$single] : '';

    return $pages;
  }



 /*
  * Get a list of valid user class contexts.
  *
  * @since    1.0
  * @return   array
  */
  protected function getAdUsers($single = false){

    $users = array(
      'anyone'     => __('To anyone', self::ID),
      'visitors'   => __('Only to visitors (unregistered users)', self::ID),
      'no-admin'   => __('To all but administrators', self::ID),
    );

    $wp_roles = new WP_Roles();

    foreach($wp_roles->get_names() as $role => $label)
      $users["role:{$role}"] = sprintf(__('By role: %s', self::ID), translate_user_role($label));

    $users = apply_filters('ad_manager_users', $users);

    if(!$single)
      return $users;

    foreach($users as $context => $label)
      if($context === $single) return $label;

  }



 /*
  * Records simple statistics from links that are set to be tracked.
  *
  * @since    1.0
  */
  public function trackAds(){

    // not our request
    if(!isset($_GET['adtrack']))
      return;

    $id = (int)$_GET['adtrack'];

    $ad = $this->getRegisteredAds($id);

    // ad doesn't exist, return
    if(!$ad || !$ad['track']) return;

    // increase click counter
    $this->updateRegisteredAd($id, 'clicks', $ad['clicks'] + 1);

    $stats = get_transient("ad_manager_stats_{$id}");

    if($stats === false)
      $stats = array();

    $ip = $_SERVER['REMOTE_ADDR'];

    $stats[] = array(
      'ip'    => $ip,
      'time'  => current_time('timestamp', true),
      'user'  => is_user_logged_in() ? wp_get_current_user()->ID : 0,
      'ref'   => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
    );

    // 10 entries max.
    while(count($stats) > 10)
      array_shift($stats);

    // stats will get deleted in two weeks if there's no activity
    set_transient("ad_manager_stats_{$id}", $stats, 60 * 60 * 24 * 14);

    wp_redirect($ad['link']);
    exit;
  }



 /*
  * Displays click stats for an ad
  *
  * @since    1.0
  */
  public function getAdStats(){

    if(!current_user_can('manage_options'))
      return;

    $id = (int)$_GET['id'];

    $stats = get_transient("ad_manager_stats_{$id}"); ?>

    <div id="ad-stats">
      <h1><?php printf(__('Last 10 clicks for Ad #%d', self::ID), $id); ?></h1>

      <table class="widefat">

        <thead>
          <tr>
            <th class="log-time"><?php _e('Freshness', self::ID); ?></th>
            <th class="log-ip"><?php _e('IP', self::ID); ?></th>
            <th class="log-ref"><?php _e('Referer', self::ID); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if($stats !== false): ?>
          <?php foreach(array_reverse($stats) as $index => $data): ?>
          <tr>
            <td class="log-time"><p title="<?php echo date('F j, Y, g:i a', $data['time']); ?>"><?php echo human_time_diff($data['time']); ?></p></td>
            <td class="log-ip">
              <?php echo $data['ip']; ?>
              <?php if($data['user']): ?>
              (<?php echo get_userdata($data['user'])->user_login; ?>)
              <?php endif; ?>
            </td>
            <td class="log-ref"><?php echo esc_url($data['ref']); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr>
            <td colspan="3"><p><?php _e('No data yet', self::ID); ?></p></td>
          </tr>
          <?php endif; ?>
        </tbody>

      </table>

      <p><a class="close button-secondary"><?php _e('Close', self::ID); ?></a></p>

    </div> <?php

    exit;
  }



 /*
  * Ad preview in the ad table listing.
  * We have a dedicated function for this because the ad saving ajax needs this as well...
  *
  * @since    1.0
  * @param    int $id
  */
  protected function adTableEntry($id){

    $data = $this->getRegisteredAds($id);

    if(!$data)
     return;

    extract($data, EXTR_SKIP);

    $tr_classes = array();

    if(!$active)
      $tr_classes[] = 'inactive';

    if($id % 2 === 0)
      $tr_classes[] = 'alternate';

    $tr_classes = $tr_classes ? 'class="'.implode(' ', $tr_classes).'"' : '';


    $has_index = strpos($location, ':index') !== false;
    $index_not_supported = $has_index && strpos($location, 'p:') === 0 && strpos($page_visibility, 'singular:') === 0;

    ob_start();
    ?>

    <tr valign="top" <?php echo $tr_classes;  ?>>

      <th class="manage-column column-cb check-column">
        <input type="checkbox" name="ads[]" id="ad-<?php echo $id; ?>" value="<?php echo $id; ?>" />
      </th>

      <td class="ad-id">
        <label for="ad-<?php echo $id; ?>"><?php echo $id; ?></label>
      </td>

      <td class="ad-type">
        <div class="stupid-ff">
          <strong><?php echo $this->ad_types[$type]->getLabel(); ?></strong>
          <?php if(isset($track) && $track): ?>
          (<a class="show-log" data-id="<?php echo $id; ?>" href="#"><?php printf(_n('1 click', '%d clicks', $clicks, self::ID), $clicks); ?></a>)
          <?php endif; ?>
          <div class="controls">
            <a href="#" class="quick-edit" data-id="<?php echo $id; ?>"><?php _e('Edit', self::ID); ?></a> |
            <a href="#" class="quick-clone" data-id="<?php echo $id; ?>"><?php _e('Clone', self::ID); ?></a> |
            <?php if($active): ?>
            <a href="#" class="quick-disable" data-id="<?php echo $id; ?>"><?php _e('Disable', self::ID); ?></a> |
            <?php else: ?>
            <a href="#" class="quick-enable" data-id="<?php echo $id; ?>"><?php _e('Enable', self::ID); ?></a> |
            <?php endif; ?>
            <a href="#" class="quick-remove" data-id="<?php echo $id; ?>"><?php _e('Remove', self::ID); ?></a>
          </div>
        </div>
      </td>

      <td class="ad-location">
        <?php echo $this->getAdLocations($location); ?>
        <?php if($has_index && !$index_not_supported): ?> #<?php echo $index; ?> <?php endif; ?>
      </td>

      <td class="ad-user-visibility">
        <ul class="disc">
          <li><?php echo $this->getAdPages($page_visibility); ?></li>
          <li><?php echo $this->getAdUsers($user_visibility); ?></li>
        </ul>
      </td>
    </tr>
    <?php
    return ob_get_clean();
  }


 /*
  * Ajax function handling the change of ad types
  *
  * @since  1.0
  */
  public function changeAdType(){

    if(!current_user_can('manage_options'))
      return;

    $new_type = $_GET['type'];
    if(isset($this->ad_types[$new_type]))
      $new_type = $this->ad_types[$new_type];

    $id = (int)$_GET['id'];

    $properties = array();
    parse_str($_GET['data'], $properties);

    foreach($new_type->getDefaults() as $field => $default)
      $properties[$field] = $default;

    $new_type->generateFormFields($id, $properties);
    exit;
  }



 /*
  * Fires on HTML fields.
  * Should auto-detect supported ad types
  *
  * @since  1.0
  */
  public function scanTypeHTML(){

    if(!current_user_can('manage_options'))
      return;

    $html = $_POST['html'];
    $current_type = $_POST['type'];
    $have_match = false;

    foreach($this->ad_types as $id => $type)
      if($id !== $current_type && ($have_match = $type->match($html))) break;

    if($have_match):
      $label = '<strong>'.$type->getLabel().'</strong>'; ?>

    <p class="notice">
      <?php printf(__('The code you entered appears to belong to %s. Would you like to change the type of this ad to %s?', self::ID), $label, $label); ?>
      <a href="#" class="button-secondary auto-change-type" data-type="<?php echo $id; ?>"><?php _e('Yes', self::ID); ?></a>
      <a href="#" class="button-secondary ignore-type"><?php _e('No', self::ID); ?></a>
    </p> <?php
    endif;

    exit;
  }



 /*
  * Generates Ad form HTML.
  * Called when clicking the "create new" button...
  *
  * @since  1.0
  */
  public function adForm(){

    if(!current_user_can('manage_options'))
      return;

    if(isset($_GET['id'])){
      $id = (int)$_GET['id'];
      $data = $this->getRegisteredAds($id);

      if(!$data)
        return;

      $editing = true;

      // check for missing type options and add them if necessary (new options can be added trough updates)
      foreach($this->ad_types[$data['type']]->getDefaults() as $field => $default)
        if(!isset($data[$field]))
          $data[$field] = $default;


    }else{
      $id = substr(md5(time()), 0, 6);
      $editing = false;
      reset($this->theme_ad_locations);
      $data = apply_filters('ad_manager_defaults', array(
        'id'              => false,
        'active'          => true,
        'type'            => 'AdHTML',
        'auto_scan'       => true,
        'location'        => key($this->theme_ad_locations),
        'page_visibility' => 'any',
        'user_visibility' => 'anyone',
        'action'          => '',
        'index'           => 4,
      ));

      $data = array_merge($this->ad_types['AdHTML']->getDefaults(), $data);
    }

    extract($data, EXTR_PREFIX_ALL, 'ad');


    ?>

    <form action="" method="POST" class="ad-form" data-id="<?php echo $id; ?>">

       <?php if($editing): // editing existing ad ?>
       <h1><?php printf(__('Editing Ad #%d', self::ID), $id); ?></h1>
       <input type="hidden" name="id" value="<?php echo $id; ?>" />

       <?php else: // new ad ?>
       <h1><?php _e('New Ad', self::ID); ?></h1>

       <?php endif; ?>

       <input type="hidden" name="auto_scan" value="<?php echo (int)$ad_auto_scan; ?>" />

       <div class="block">
         <div class="row clear-block">
           <label class="left" for="ad-type-<?php echo $id; ?>"><?php _e('Type', self::ID); ?></label>
           <select name="type" id="ad-type-<?php echo $id; ?>">
             <?php foreach($this->getAdTypes() as $type => $label): ?>
             <option value="<?php echo $type; ?>" <?php selected($ad_type, $type); ?>><?php echo $label; ?></option>
             <?php endforeach; ?>
           </select>
           <div class="row-add">
             <label for="ad-active-<?php echo $id; ?>">
               <input type="hidden" name="active" value="0" />
               <input type="checkbox" id="ad-active-<?php echo $id; ?>" name="active" <?php checked($ad_active); ?> />
               <?php _e('Enable', self::ID); ?>
             </label>
           </div>
         </div>

         <div class="row clear-block">
           <label class="left" for="ad-location-<?php echo $id; ?>"><?php _e('Location of the ad', self::ID); ?></label>
           <select name="location" id="ad-location-<?php echo $id; ?>">

             <?php $locations = $this->getAdLocations(); ?>

             <optgroup label="<?php _e('Theme locations', self::ID); ?>">
               <?php if(empty($locations['theme'])): ?>
               <option disabled="disabled"><?php _e('Current theme has not registered any ad locations', self::ID); ?></option>
               <?php else: ?>
               <?php foreach($locations['theme'] as $location => $label): ?>
               <option data-pages="<?php echo implode(';', $this->location_page_conditions[$location]); ?>" value="<?php echo $location; ?>" <?php selected($ad_location, $location); ?>><?php echo $label; ?></option>
               <?php endforeach; ?>
               <?php endif; ?>
             </optgroup>

             <optgroup label="<?php _e('Default locations', self::ID); ?>">
               <?php foreach($locations['defaults'] as $location => $label): ?>
               <option value="<?php echo $location; ?>" <?php selected($ad_location, $location); ?>><?php echo $label; ?></option>
               <?php endforeach; ?>
             </optgroup>

             <optgroup label="<?php _e('Others', self::ID); ?>">
               <?php foreach($locations['other'] as $location => $label): ?>
               <option value="<?php echo $location; ?>" <?php selected($ad_location, $location); ?>><?php echo $label; ?></option>
               <?php endforeach; ?>
             </optgroup>

           </select>

           <div class="row-add hidden">
             <label for="ad-location-index-<?php echo $id; ?>"><?php _e('Index:', self::ID); ?></label>
             <input size="2" id="ad-location-index-<?php echo $id; ?>" type="text" name="index" value="<?php echo $ad_index; ?>" />
           </div>

           <div class="row-add hidden">
             <label for="ad-location-action-<?php echo $id; ?>"><?php _e('Action tag name:', self::ID); ?></label>
             <input size="40" id="ad-location-action-<?php echo $id; ?>" type="text" name="action" value="<?php echo $ad_action; ?>" />
           </div>

           <div class="row-add hidden">
             <a name="shortcode"></a>
             <?php if($editing): ?>
             <code>[ad <?php echo $id; ?>]</code>
             <?php else: ?>
             <?php printf(__('Save the ad to find the ID that you can use within the %s shortcode', self::ID), '<code>[ad]</code>'); ?>
             <?php endif; ?>
           </div>
         </div>

         <div class="row clear-block">
           <label class="left" for="ad-page-<?php echo $id; ?>"><?php _e('Page visibility', self::ID); ?></label>
           <select name="page_visibility" id="ad-page-<?php echo $id; ?>">
             <?php foreach($this->getAdPages() as $page => $label): ?>
             <option value="<?php echo $page; ?>" <?php selected($ad_page_visibility, $page); ?>><?php echo $label; ?></option>
             <?php endforeach; ?>
           </select>
         </div>

         <div class="row clear-block">
           <label class="left" for="ad-user-visibility-<?php echo $id; ?>"><?php _e('User visibility', self::ID); ?></label>
           <select name="user_visibility" id="ad-user-visibility-<?php echo $id; ?>">
             <?php foreach($this->getAdUsers() as $class => $label): ?>
             <option value="<?php echo $class; ?>" <?php selected($ad_user_visibility, $class); ?>><?php echo $label; ?></option>
             <?php endforeach; ?>
           </select>
         </div>

       </div>

       <div class="type-options">
         <?php $this->ad_types[$ad_type]->generateFormFields($id, $data); ?>
       </div>

       <div class="block">
         <input id="save-ad" type="submit" class="button-secondary" value="<?php _e('Save', self::ID); ?>" />
         <input id="cancel-edit" type="submit" class="button-secondary" value="<?php _e('Cancel', self::ID); ?>" />
         <?php if($editing): ?>
         <input id="remove-ad" type="submit" class="button-secondary" value="<?php _e('Remove', self::ID); ?>" data-id="<?php echo $id; ?>" />
         <?php endif; ?>
         <input type="hidden" class="nonce" value="<?php echo wp_create_nonce('process_ad'); ?>" />
       </div>
    </form>
    <?php

    exit;
  }



 /*
  * Saves data for an ad
  *
  * @since 1.0
  */
  public function processAd(){

    if(!current_user_can('manage_options') && !isset($_POST['data']))
      return;

    // remove request?
    if($_POST['data'] === 'remove'){

      $removed = 0;
      $id = (int)$_POST['id'];

      if($this->getRegisteredAds($id)){
        $this->updateRegisteredAd($id, false);
        delete_transient("ad_manager_stats_{$id}");
        $removed = true;
      }

      echo json_encode(array('removed' => $removed));

    // remove request?
    }elseif($_POST['data'] === 'change_status'){

      $id = (int)$_POST['id'];
      $ad = $this->getRegisteredAds($id);

      if(!$ad)
        return;

      $ad['active'] = !$ad['active'];
      $this->updateRegisteredAd($id, $ad);

      echo $this->AdTableEntry($id);

    // clone request?
    }elseif($_POST['data'] === 'clone'){

      $id = (int)$_POST['id'];
      $existing_ads = $this->getRegisteredAds();

      $new_id = max(array_keys($existing_ads)) + 1;
      $existing_ads[$new_id] = $existing_ads[$id];

      $this->updateRegisteredAds($existing_ads);

      echo $this->AdTableEntry($new_id);


    // no, save request
    }else{

      $input = array();
      $error = false;
      $existing_ads = $this->getRegisteredAds();

      parse_str(stripslashes_deep($_POST['data']), $input);

      // @see why here: http://php.net/manual/en/function.parse-str.php
      if(get_magic_quotes_gpc())
        $input = stripslashes_deep($input);

      $id = isset($input['id']) ? (int)$input['id'] : 1;

      // not editing, ID must be unique, search for an empty slot...
      if(!isset($input['id']) && !empty($existing_ads))
        $id = max(array_keys($existing_ads)) + 1;

      $properties = array(
        'active'          => (bool)$input['active'],
        'type'            => $input['type'],
        'auto_scan'       => (bool)$input['auto_scan'],
        'location'        => $input['location'],
        'user_visibility' => $input['user_visibility'],
        'page_visibility' => $input['page_visibility'],
        'action'          => sanitize_key($input['action']),
        'index'           => (int)$input['index'],
      );

      $type = $input['type'];

      if(!isset($this->ad_types[$type]))
        $error = __('The requested ad type does not exist!', self::ID);

      if(!in_array($properties['location'], array_keys(call_user_func_array('array_merge', $this->getAdLocations()))))
        $error = __('The provided location is invalid!', self::ID);

      if(!in_array($properties['page_visibility'], array_keys($this->getAdPages())))
        $error = __('The page visibility setting you provided is invalid!', self::ID);

      if(!in_array($properties['user_visibility'], array_keys($this->getAdUsers())))
        $error = __('The user visibility setting you provided is invalid!', self::ID);

      if($properties['location'] === 'action' && empty($input['action']))
        $error = __('You must provide a valid action tag for the selected location.', self::ID);


      if(!$error){
        foreach($this->ad_types[$type]->getDefaults() as $field => $default)
          $properties[$field] = isset($input[$field]) ? $input[$field] : (isset($input['id']) && isset($existing_ads[$id][$field]) ? $existing_ads[$id][$field] : $default);

        $this->ad_types[$type]->validate($id, $properties, $error);
      }

      if(empty($error)){
        $existing_ads[$id] = $properties;
        $this->updateRegisteredAds($existing_ads);
        $output = $this->AdTableEntry($id);

      }else{
        $output = '<p class="error">'.$error.'</p>';
      }

      echo json_encode(array(
        'error'  => !empty($error),
        'html'   => $output,
      ));

    }

    exit;
  }



 /*
  * The options page (form)
  *
  * @since 1.0
  */
  public function settingsPage(){

    // check for old ads form the "Ad module" in Atom themes
    $this->autoImportFromAtom();
    ?>

    <div class="wrap metabox-holder">
      <h2><?php _e('Ad Manager', self::ID); ?></h2>
      <p><?php printf(__('Found a bug, having a feature request or just looking for help on using this plugin? Then head on to the %s.', self::ID), '<a href="'.self::PROJECT_URI.'">'.__('Ad Manager support forums', self::ID).'</a>'); ?></p>
      <table id="ads" class="wp-list-table widefat">

        <thead>
          <tr>
            <th class="check-column"><input type="checkbox" /></th>
            <th class="ad-id"><?php _e('ID', self::ID); ?></th>
            <th class="ad-type"><?php _e('Type', self::ID); ?></th>
            <th class="ad-location"><?php _e('Location', self::ID); ?></th>
            <th class="ad-visibility"><?php _e('Visibility', self::ID); ?></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach($this->getRegisteredAds() as $id => $properties) echo $this->AdTableEntry($id); ?>
        </tbody>

        <tfoot>
          <tr valign="top">
            <td scope="row" colspan="5">
              <p>
                <select disabled="disabled" title="Not yet implemented"> <?php // @todo ?>
                  <option><?php _e('With selected...', self::ID); ?></option>
                  <option value="remove"> - <?php _e('Remove', self::ID); ?></option>
                  <option value="disabled"> - <?php _e('Disable', self::ID); ?></option>

                </select>
                <input id="new-ad" type="submit" class="button-secondary" value="<?php _e('Create new', self::ID); ?>" />
                <?php wp_nonce_field('ad_form', 'ad_form'); ?>
              </p>
            </td>
          </tr>
        </tfoot>
      </table>

    </div>
    <?php
  }



 /*
  * Javascript and CSS used by the plugin,
  * for the administration side only
  *
  * @since 1.0
  */
  public function assets(){

    // js
    wp_enqueue_script('jquery');
    wp_enqueue_script(self::ID, plugins_url('ad-manager.js', __FILE__), array('jquery'), self::VERSION, true);

    /*
    wp_localize_script(self::ID, 'ad_manager', array(

    ));
    */

    wp_enqueue_style(self::ID, plugins_url('ad-manager.css', __FILE__));
  }



 /*
  * Remove plugin options and rating stats on uninstall
  *
  * @since 1.0
  */
  public static function uninstall(){
    delete_option(self::ID);
  }



 /*
  * Checks if an ad is visible in the current context
  *
  * @since   1.0
  * @param   array|int $properties Ad ID or ad properties
  * @return  bool
  */
  protected function isVisible($properties){

    // id can also be given instead of the properties array
    if(is_int($properties)){

      $properties = $this->getRegisteredAds($properties);

      if(!$properties)
        return false;
    }

    extract($properties);

    // check if ad is disabled; if it is we're not going further
    if(!$active)
      return false;

    // start optimistic :D
    $visible = true;


    // page visibility
    if(strpos($page_visibility, 'singular:') === 0)
      $visible = is_singular(substr($page_visibility, 9));

    elseif(strpos($page_visibility, 'tax:') === 0)
      $visible = is_post_type_archive(substr($page_visibility, 4));

    elseif($page_visibility !== 'any')
      $visible = call_user_func("is_{$page_visibility}");


    // user visibility;
    // no point checking if we're not on the right page
    if($visible){
      if((strpos($user_visibility, 'role:') === 0) && is_user_logged_in())
        $visible = in_array(substr($user_visibility, 5), wp_get_current_user()->roles);

      elseif($user_visibility === 'visitors')
        $visible = !is_user_logged_in();

      elseif($user_visibility === 'no-admin')
        $visible = !current_use_can('administrator');

    }

    // some ad types will want to do further visibility checks,
    // like the image ad type (for tracking + max click limit)
    if($visible)
      $visible = $this->ad_types[$type]->isVisible($properties);

    return apply_filters('ad_manager_visibility_check', $visible, $properties);
  }



 /*
  * Get the code associated with an ad.
  *
  * @since   1.0
  * @param   int $id           Ad ID
  * @param   string $content   Optional, content to append/prepend the code to
  * @return  string
  */
  public function getAdCode($id, $content = ''){

    $ad = $this->getRegisteredAds($id);

    if(!$ad)
      return $content;

    extract($ad);

    if(!isset($this->ad_types[$type]))
      return $content;

    $code = $this->ad_types[$type]->getCode($id, $ad);

    if($content === '')
      return $code;

    // handle built-in locations: the_content or comment_text actions;
    // after content
    if(strpos($location, 'after_') !== false)
      return $content.$code;

    // before content
    if(strpos($location, 'before_') !== false)
      return $code.$content;

    // half-way trough - this could be slow -- @todo
    $test = '<!--S-->';

    // stupid wp still supports php 5.2, so we can't rely on closures :(
    $content = preg_replace_callback('/(\.|\?|\!)(?!([^<]+)?>)/i', create_function('$matches', 'return $matches[1]."<!--S-->";'), $content);

    $parts = explode($test, $content);
    $pos = floor(count($parts) / 2);

    if(isset($parts[$pos]))
      $parts[$pos] .= $code;

    $content = str_replace($test, '', implode($test, $parts));
    return $content;
  }



 /*
  * Displays all ads associated with a specific location.
  * This function should only be called trough an action, because the action tag represents the location
  *
  * @since   1.0
  * @param   string $content   Post or comment content, only available for default locations (the_content and comment_text actions)
  */
  public function output($content = ''){

    // tracks the current action index, for locations that require it
    static
      $current_index = array();

    // action tag name, required to indentify ads queued on this action/location
    $place = current_filter();

    $in_content = in_array($place, array('the_content', 'comment_text'));

    if(!isset($current_index[$place]))
      $current_index[$place] = 0;

    // don't go further if there are no ads queued on this action
    if(!isset($this->queue[$place])) return;

    // we have ads, display them
    foreach($this->queue[$place] as $key => $id){

      // check if this location requires an action index;
      // if it does, insert the ad after "index" number of actions have been ran
      if(isset($this->location_index_conditions[$id])){

        // increase index counter for this action
        $current_index[$place]++;

        // skip this location, because we don't have an index match
        if($this->location_index_conditions[$id] !== $current_index[$place]) continue;
      }

      // remove from queue, because this filter can run later
      unset($this->queue[$place][$key]);

      if($in_content)
        return $this->getAdCode($id, $content);

      echo $this->getAdCode($id, $content);
    }

    if($in_content)
      return $content;
  }



 /*
  * Determines which ads are visible in the current context and queues them for display
  *
  * @since 1.0
  */
  public function run(){

    foreach($this->getRegisteredAds() as $id => $properties){

      extract($properties);

      if(!$this->isVisible($properties) || $location === 'shortcode') continue;

      // custom action
      if($location !== 'action'){

        // determine if the location needs an index
        if(($index_pos = strpos($location, ':index')) !== false){
          $location = substr($location, 0, $index_pos);

          // ignore the index conditions for posts if the page is set to singular-type
          if(!(strpos($location, 'p:') === 0 && strpos($page_visibility, 'singular:') === 0))
            $this->location_index_conditions[$id] = $index;
        }

        // drop "theme:" prefix if we have it
        if(strpos($location, 'theme:') === 0)
          $action = substr($location, 6);

        elseif(strpos($location, 'p:') === 0)
          $action = 'the_content';

        elseif(strpos($location, 'c:') === 0)
          $action = 'comment_text';

      }

      if(!isset($this->queue[$action]))
        $this->queue[$action] = array();

      $this->queue[$action][] = $id;
    }

    foreach($this->queue as $place => $code)
      add_filter($place, array($this, 'output'));

  }




  public function autoImportFromAtom(){

    if(!class_exists('Atom') || !function_exists('atom'))
      return;

    $theme_options = atom()->options();
    $imported = array();

    if(empty($theme_options['advertisments']))
      return;

    foreach($theme_options['advertisments'] as $ad){

      // defaults
      $type = 'AdHTML';
      $active = true;
      $auto_scan = true;
      $action = '';

      // html code
      $html = $ad['html'];
      $index = isset($ad['n']) ? (int)$ad['n'] : 4;

      // location
      $location = "atom_{$ad['place']}";
      foreach($this->theme_ad_locations as $id => $label)
        if(strpos($id, $location) !== false)
          $location = $id;

      if(!in_array($location, array_keys(call_user_func_array('array_merge', $this->getAdLocations()))))
        $location = 'shortcode';

      // page vis.
      $page = ($ad['page'] === 'single') ? 'singular:post' : $ad['page'];
      $page_visibility = in_array($page, array_keys($this->getAdPages())) ? $page : 'any';

      // user vis.
      switch($ad['to']){
        case '0':
          $user_visibility = 'anyone';
        break;
        case '1':
          $user_visibility = 'visitors';
        break;
        default:
          $role = "role:{$ad['to']}";
          $user_visibility = in_array($role, array_keys($this->getAdUsers())) ? $role : 'anyone';
        break;
      }

      $imported[] = compact('type', 'active', 'auto_scan', 'location', 'action', 'index', 'page_visibility', 'user_visibility', 'html');

    }

    $existing_ads = $this->getRegisteredAds();
    $starting_id = empty($existing_ads) ? 1 : max(array_keys($existing_ads)) + 1;

    foreach($imported as $ad)
      $existing_ads[$starting_id++] = $ad;

    $this->updateRegisteredAds($existing_ads);

    // remove old ads from the theme
    unset($theme_options['advertisments']);
    atom()->setOptions($theme_options);

    ?>

    <div class="updated">
      <p><?php printf(_n('%d ad was imported from your theme - %s', '%d ads were mported from your theme - %s', count($imported), self::ID), count($imported), atom()->getThemeName()); ?></p>
    </div>

    <?php
  }



 /*
  * The [rate] shortcode
  *
  * @since     1.0
  * @params    array $atts     Can accept the post ID as argument; if not given, control() will use the $post global
  * @return    string
  */
  public function shortcode($atts){

    $id = 1;

    // check if a post ID was given as first argument
    if(isset($atts[0]) && is_numeric($atts[0]))
      $id = (int)$atts[0];

    // no, maybe it's the 2nd argument
    elseif(isset($atts[1]) && is_numeric($atts[1]))
      $id = (int)$atts[1];

    // check if a "force" attribute is present
    $force = array_search('force', (array)$atts) !== false;

    if($force || (!$force && $this->isVisible($id)))
      return $this->getAdCode($id);

    return '';
  }



 /*
  * Register the "Top Rated" widget
  *
  * @since 1.0
  */
  public function widget(){
    //require dirname(__FILE__).'/widget.php';
    //register_widget('AdManagerWidget');
  }
}



// a shortcut to our application
function AdManager(){
  static $app;

  // first call to app() initializes the plugin
  if(!($app instanceof AdManager))
    $app = AdManager::app();

  return $app;
}


AdManager();

