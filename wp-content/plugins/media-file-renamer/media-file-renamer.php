<?php
/*
Plugin Name: Media File Renamer (Auto Rename)
Plugin URI: https://meowapps.com
Description: Renames automatically the files depending on Media titles and updates the links.
Version: 4.5.4
Author: Jordy Meow
Author URI: https://jordymeow.com
Text Domain: media-file-renamer
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
https://www.opensource.org/licenses/mit-license.php
https://www.gnu.org/licenses/gpl.html

Originally developed for two of my websites:
- Jordy Meow (https://offbeatjapan.org)
- Haikyo (https://haikyo.org)
*/

if ( class_exists( 'Meow_MFRH_Core' ) ) {
	function mfrh_admin_notices() {
		echo '<div class="error"><p>Thanks for installing the Pro version of Media File Renamer :) However, the free version is still enabled. Please disable or uninstall it.</p></div>';
	}
	add_action( 'admin_notices', 'mfrh_admin_notices' );
	return;
}

require( 'helpers.php');

// In admin or Rest API request (REQUEST URI begins with '/wp-json/')
if ( is_admin() || is_rest() ) {

	global $mfrh_version, $mfrh_core;
	$mfrh_version = '4.5.4';

	// Admin
	require( 'mfrh_admin.php');
	$mfrh_admin = new Meow_MFRH_Admin( 'mfrh', __FILE__, 'media-file-renamer' );

	// Core
	require( 'core.php' );
	global $mfrh_core;
	$mfrh_core = new Meow_MFRH_Core( $mfrh_admin );

	// UI
	require( 'ui.php' );
	new Meow_MFRH_UI( $mfrh_core, $mfrh_admin );
}
