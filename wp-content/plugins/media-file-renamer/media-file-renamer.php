<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://meowapps.com
Description: Auto-rename the files when titles are modified and update and the references (links). Manual Rename is a Pro option. Please read the description.
Version: 4.2.7
Author: Jordy Meow
Author URI: http://meowapps.com
Text Domain: media-file-renamer
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html

Originally developed for two of my websites:
- Jordy Meow (http://offbeatjapan.org)
- Haikyo (http://haikyo.org)
*/

if ( class_exists( 'Meow_MFRH_Core' ) ) {
	function mfrh_admin_notices() {
		echo '<div class="error"><p>Thanks for installing the Pro version of Media File Renamer :) However, the free version is still enabled. Please disable or uninstall it.</p></div>';
	}
	add_action( 'admin_notices', 'mfrh_admin_notices' );
	return;
}

// In admin or Rest API request (REQUEST URI begins with '/wp-json/')
if ( is_admin() || strpos( trailingslashit( $_SERVER['REQUEST_URI'] ), '/' . trailingslashit( rest_get_url_prefix() ) ) === 0 ) {

	global $mfrh_version, $mfrh_core;
	$mfrh_version = '4.2.7';

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

	/*******************************************************************************
	 * TODO: OLD PRO,  THIS FUNCTION SHOULD BE REMOVED IN THE FUTURE
	 ******************************************************************************/

	add_action( 'admin_notices', 'mfrh_meow_old_version_admin_notices' );

	function mfrh_meow_old_version_admin_notices() {
		if ( isset( $_POST['mfrh_reset_sub'] ) ) {
			delete_transient( 'mfrh_validated' );
			delete_option( 'mfrh_pro_serial' );
			delete_option( 'mfrh_pro_status' );
		}
		$subscr_id = get_option( 'mfrh_pro_serial', "" );
		if ( empty( $subscr_id ) )
			return;
		$forever = strpos( $subscr_id, 'F-' ) !== false;
		$yearly = strpos( $subscr_id, 'I-' ) !== false;
		if ( !$forever && !$yearly )
			return;
		?>
		<div class="error">
		<p>
			<h2>IMPORTANT MESSAGE ABOUT MEDIA FILE RENAMER</h2>
			In order to comply with WordPress.org, BIG CHANGES in the code and how the plugin was sold were to be made. The plugin needs requires to be purchased and updated through the new <a target='_blank' href="https://store.meowapps.com">Meow Apps Store</a>. This store is also more robust (keys, websites management, invoices, etc). Now, since WordPress.org only accepts free plugins on its repository, this is the one currently installed. Therefore, you need to take an action. <b>Please click here to know more about your license and to learn what to do: <a target='_blank' href='https://meowapps.com/?mkey=<?php echo $subscr_id ?>'>License <?php echo $subscr_id ?></a></b>.
		</p>
			<p>
			<form method="post" action="">
				<input type="hidden" name="mfrh_reset_sub" value="true">
				<input type="submit" name="submit" id="submit" class="button" value="Got it. Clear this!">
				<br /><small><b>Make sure you followed the instruction before clicking this button.</b></small>
			</form>
		</p>
		</div>
		<?php
	}
}
