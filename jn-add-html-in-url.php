<?php
/**
 * Plugin Name: Add .html in all url
 * Plugin URI: http://www.jnext.co.in/
 * Description: Adds .html to url of all pages,post,custom post.
 * Author: jNext
 * Version: 1.1
 * Author URI: http://www.jnext.co.in/
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

define( 'JN_FILE',       __FILE__ );
define( 'JN_BASENAME',   plugin_basename( __FILE__ ) );

require_once( plugin_dir_path( __FILE__ ) . 'class-add-html-in-url.php' );
$instance = new JN_Html_In_Url;
require_once( plugin_dir_path( __FILE__ ) . 'jn-html-in-url-settings.php' );
$instance = new Jn_Html_In_Url_Settings;
