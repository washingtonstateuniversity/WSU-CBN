<?php

class wsuwp_cache__simpleads {

    var $retain_time;
    var $crt_name;

    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        $this->crt_name = $this->prefix . '-cache_retain_time';

        // 0 is an acceptable param, so we can't just do a boolean check
        $this->retain_time = get_option($this->crt_name);
    }

    function initialize_options() {

        $this->settings->add_section(array(
                'name' => 'Cache Settings',
                'description' => "<p>Your cache directory can be found at: <code>".
                    $this->path."</code></p>",
                'start_collapsed' => true
            )
        );

        $this->settings->add_item(
            'Cache Settings',
            'Enable Caching',
            'cache_enable',
            'checkbox'
        );
        $this->settings->add_item('Cache Settings',
            'Retain Time',
            'cache_retain_time',
            'custom',
            false,
            null,
            "<select name=\"". $this->crt_name ."\">
<option value=\"0\"".((get_option($this->crt_name) == 0) ? ' selected' : '').">None</option>
<option value=\"60\"".((get_option($this->crt_name) == 60) ? ' selected' : '').">1 Minute</option>
<option value=\"300\"".((get_option($this->crt_name) == 300) ? ' selected' : '').">5 Minutes</option>
<option value=\"3600\"".((get_option($this->crt_name) == 3600) ? ' selected' : '').">1 Hour</option>
<option value=\"18000\"".((get_option($this->crt_name) == 18000) ? ' selected' : '').">5 Hours</option>
<option value=\"86400\"".((get_option($this->crt_name) == 86400) ? ' selected' : '').">1 Day</option>
<option value=\"604800\"".((get_option($this->crt_name) == 604800) ? ' selected' : '').">1 Week</option>
</select>"
        );
    }

    function load($filename) {
        $cache_file = $this->path . '/' . $filename;

        if (!$this->enabled || !file_exists($cache_file) || ((time() - filemtime($cache_file)) >= $this->retain_time) ) {
            return false;
        }

        if (file_exists($cache_file)) {
            $contents = file_get_contents($cache_file);
            return unserialize($contents);
        }

        return false;
    }

    function save($filename, $data) {
        $cache_file = $this->path . '/' . $filename;

        if ( !$this->enabled || (file_exists($cache_file) && ((time() - filemtime($cache_file)) >= $this->retain_time) ) ){
            return false;
        }

        return file_put_contents($cache_file, serialize($data));
    }

    function check_cache() {
        if ( isset($this->enabled)) return;

        $is_cachable = false;
        if (get_option($this->prefix.'-cache_enable')) {

            if (!file_exists($this->path)) {
                if (isset($this->notifications)) {
                    $this->notifications->add_notice(
                        2,
                        "You do not have a cache directory<br>
                         If you would like to implement caching, please create
                         the cache directory: <code>" .
                         $this->path . "</code>",
                        "options-general.php?page={$this->prefix}-options#cache_settings"
                    );
                }
            } else if (!is_writable($this->path)) {
                if (isset($this->notifications)) {
                    $this->notifications->add_notice(2,
                        "Your cache directory is not writable<br>
                         If you would like to implement caching, please change
                         the permissions on the cache directory: <code>" . $this->path .
                        "</code>",
                        "options-general.php?page={$this->prefix}-options#cache_settings"
                    );
                }
            } else {
                $is_cachable = true; // looks like we can cache stuff
            }
        }

        $this->enabled = $is_cachable;

        return (isset($notices)) ? $notices : false;
    }
}

?>
