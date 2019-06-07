<?php
/*
Plugin Name: Divi Lightbox for Images
Plugin URI: https://servicios.ayudawp.com
Description: Uses Divi's native lightbox effect to every self linked image, not only for galleries. This plugin only works with Divi theme or Divi builder installed & active.
Version: 0.9.5
Author: Fernando Tellado
Author URI: https://tellado.es
License: GPLv2
Text Domain: lightbox-images-for-divi
Domain Path: /languages/
*/

// Prevent direct access to the plugin
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
/**
 * Init for translations.
 *
 * @since     1.0.0
 */
function lightbox_images_for_divi_init() {
	load_plugin_textdomain( 'lightbox-images-for-divi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'lightbox_images_for_divi_init' );
/**
 * Plugin functions
*/ 
add_action( 'wp_footer', 'ayudawp_divi_lightbox_js_function' );
function ayudawp_divi_lightbox_js_function() {
	?><script type="text/javascript">(function($){$(document).ready(function(){$('.entry-content a').children('img').parent('a').addClass(function(){return(($(this).attr("href").split("?",1)[0].match(/\.(jpeg|jpg|gif|png)$/) != null) ? "et_pb_lightbox_image" : "");});});})(jQuery)</script><?php
}