<?php
/*
Plugin Name: Connections Emailer
Plugin URI: 
Description: add emailing to the Connections Plugin by www.connections-pro.net
Version: 0.1
Author: jeremy.bass@wsu.edu
Author URI: http://wsu.edu
*/

if (!class_exists('connectionsROT13Load')) {
	class connectionsROT13Load
	{
		
		public function __construct()
		{
			if ( !is_admin() ) add_action( 'plugins_loaded', array(&$this, 'start') );
			if ( !is_admin() ) add_action( 'wp_print_scripts', array(&$this, 'loadScripts') );
		}
		
		public function start()
		{
			define('CNROT13_CURRENT_VERSION', '0.1');
			define('CNROT13_ANCHOR_PATTERN', '/(<a.*?mailto)(.*?)(<\/a>)/i');
			define('CNROT13_EMAIL_PATTERN', '/(<a.*?mailto:.*?>)(.*?)(<\/a>)/i');
			
			define('CN_ROT13_BASE_PATH', WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__)));
			define('CN_ROT13_BASE_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__)));
			
			//add_filter('cn_email_address', array(&$this, 'applyROT13'));
			add_filter('cn_output_email_addresses', array(&$this, 'outputROT13'));
		}
		
		public function applyROT13($string)
		{
			return str_replace("/","\\057", str_replace('"', '\\"', str_replace(".", "\\056", str_replace("@", "\\100", str_rot13( $string )))));
		}
		
		public function createNoScript($string)
		{
			return '<noscript><span style="unicode-bidi: bidi-override; direction:rtl;">' . antispambot( strrev($string) ) . '</span></noscript>'; 
		}
		
		public function outputROT13($out)
		{
			return preg_replace_callback(CNROT13_ANCHOR_PATTERN, array(&$this, 'ROT13_callback'), $out);
		}
		
		public function ROT13_callback($matches)
		{
			preg_match(CNROT13_EMAIL_PATTERN, $matches[0], $match);
			$matches[] = $match[2];
			
			return '<script type="text/javascript">
					/* <![CDATA[ */
	    				cnROT13.writeROT13("' . $this->applyROT13( $matches[0] ) . '");
					/* ]]-> */
	   				</script>' . $this->createNoScript($matches[4]);
		}
		
		/**
		 * Loads the Connections javascripts on the WordPress frontend.
		 */
		public function loadScripts()
		{
			wp_enqueue_script('jquery');
			
			wp_register_script('cn_rot13_js', CN_ROT13_BASE_URL  . '/cn_rot13.js', array('jquery'), CNROT13_CURRENT_VERSION, FALSE);
			wp_enqueue_script('cn_rot13_js');
		}

	}
	
	/*
	 * Checks for PHP 5 or greater as required by Connections Pro and display an error message
	 * rather that havinh PHP thru an error.
	 */
	if (version_compare(PHP_VERSION, '5.0.0', '>'))
	{
		/*
		 * Initiate the plug-in.
		 */
		global $connectionsROT13;
		$connectionsROT13 = new connectionsROT13Load();
	}
	else
	{
		add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>Connections ROT13 requires at least PHP5. You are using version: ' . PHP_VERSION . '</strong></p></div>\';') );
	}
	
}