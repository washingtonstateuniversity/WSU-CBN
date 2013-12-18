<?php


/*
 * Extend this class to create new types.
 * See examples below...
 *
 * @since 1.0
 */
abstract class AdManagerType{

  // you can use this constant for localization (text domain)
  const ID = AdManager::ID;

  // label of the ad type
  abstract function getLabel();

  // default options of this ad tyoe
  abstract function getDefaults();

  // form fields (HTML)
  abstract function generateFormFields($id, $properties);

  // generate final code here
  abstract function getCode($id, $properties);

  // validate your options here (they are passed by reference);
  abstract function validate($id, &$input, &$error);

  // optional; match HTML from the "HTML ad type" against your code,
  // to see if the user can change the HTML ad type with yours
  public function match($html){
    return false;
  }

  // optional, hook into visibility checks
  public function isVisible($properties){
    return true;
  }
}



/*
 * The HTML Ad type
 * Provides only a simple textarea field for HTML code
 *
 * @since 1.0
 */
class AdHTML extends AdManagerType{

  public function getLabel(){
    return __('HTML Code', self::ID);
  }

  public function getDefaults(){
    return array(
      'html' => '<div class="ad">'."\n  ".__('Add your HTML code here...', self::ID)."\n".'</div>',
    );
  }

  public function generateFormFields($id, $properties){
    ?>
    <textarea class="widefat ad-html code" name="html" id="ad-html-<?php echo $id; ?>" class="code" rows="1"><?php echo esc_textarea($properties['html']); ?></textarea>
    <?php if(!current_user_can('unfiltered_html')): ?>
    <p class="notice">
      <?php _e('Some HTML tags are disallowed for security reasons (only super-administrators can post unfiltered code)'); ?>
    </p>
    <?php endif; ?>
    <?php
  }

  public function getCode($id, $properties){
    return $properties['html'];
  }

  public function validate($id, &$input, &$error){
    $input['html'] = current_user_can('unfiltered_html') ? $input['html'] : stripslashes(wp_filter_post_kses(addslashes($input['html'])));
  }

}



/*
 * Image w/ link Ad type.
 *
 * @since 1.0
 */
class AdImageLink extends AdManagerType{

  public function getLabel(){
    return __('Image w/ link', self::ID);
  }

  public function getDefaults(){
    return array(
      'link'       => 'http://google.com/',
      'title'      => __('Advertisment', self::ID),
      'image'      => 'http://dummyimage.com/468x60/333/fff/',
      'track'      => true,
      'clicks'     => 0,
      'max_clicks' => 0,
    );
  }

  public function generateFormFields($id, $properties){
    ?>
    <div class="block">
      <div class="row clear-block">
        <label class="left" for="ad-link-<?php echo $id; ?>"><?php _e('Link URI', self::ID); ?></label>
        <input name="link" size="60" id="ad-link-<?php echo $id; ?>" value="<?php echo esc_url($properties['link']); ?>" />
      </div>
      <div class="row clear-block">
        <label class="left" for="ad-title-<?php echo $id; ?>"><?php _e('Title', self::ID); ?></label>
        <input name="title" size="60" id="ad-title-<?php echo $id; ?>" value="<?php echo esc_attr($properties['title']); ?>" />
      </div>
      <div class="row clear-block">
        <label class="left" for="ad-image-<?php echo $id; ?>"><?php _e('Image URI', self::ID); ?></label>
        <input name="image" size="60" id="ad-image-<?php echo $id; ?>" value="<?php echo esc_url($properties['image']); ?>" />
      </div>
      <div class="row-offset clear-block">
        <label for="ad-track-<?php echo $id; ?>">
          <input type="hidden" name="track" value="0" />
          <input type="checkbox" id="ad-track-<?php echo $id; ?>" name="track" <?php checked($properties['track']); ?> />
          <?php _e('Track click count', self::ID); ?>
        </label>
        /
        <?php ob_start(); ?>
        <input type="text" size="3" id="ad-max-clicks-<?php echo $id; ?>" name="max_clicks" value="<?php echo $properties['max_clicks']; ?>" />
        <?php $input = ob_get_clean(); ?>
        <label for="ad-max-clicks-<?php echo $id; ?>">
          <?php printf(__('and auto-disable after %s clicks (0 = no limit)', self::ID), $input); ?>
        </label>
      </div>
    </div>
    <?php
  }

  public function getCode($id, $properties){

    $code = '';
    extract($properties);

    if($image)
      $code = '<img src="'.$image.'" title="'.$title.'" />';

    if($track)
      $link = add_query_arg('adtrack', $id);

    $code = '<a class="ad ad-'.sanitize_title($title).'" href="'.$link.'" title="'.$title.'">'.$code.'</a>';

    return $code;
  }

  public function validate($id, &$input, &$error){
    $input['link'] = esc_url_raw($input['link']);
    $input['title'] = esc_attr(strip_tags($input['title']));
    $input['image'] = esc_url_raw($input['image']);
    $input['track'] = (bool)$input['track'];
    $input['max_clicks'] = (int)$input['max_clicks'];

    if(empty($input['link']))
      $error = __('You must provide a valid link URI', self::ID);
  }

  public function isVisible($properties){
    extract($properties);
    return $max_clicks === 0 ? true : !($clicks > $max_clicks);
  }

}



/*
 * @todo
 *
 * @since 1.0
 */
class AdAdsense extends AdHTML{

  public function getLabel(){
    return 'AdSense';
  }

  public function match($html){
    return (strpos($html, 'google_ad_client') !== false);
  }

}
