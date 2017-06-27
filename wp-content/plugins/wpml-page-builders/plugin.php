<?php
/*
Plugin Name: WPML Page Builders
Plugin URI: https://wpml.org/
Description: Adds integration support for Page Builder Plugins
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
Version: 1.1.3
Plugin Slug: wpml-page-builders
*/

if ( defined( 'WPML_PAGE_BUILDERS_VERSION' ) || get_option( '_wpml_inactive' ) ) {
	return;
}

define( 'WPML_PAGE_BUILDERS_VERSION', '1.1.3' );

define( 'WPML_PAGE_BUILDERS_PATH', dirname( __FILE__ ) );

$autoloader_dir = WPML_PAGE_BUILDERS_PATH . '/vendor';
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
	$autoloader = $autoloader_dir . '/autoload.php';
} else {
	$autoloader = $autoloader_dir . '/autoload_52.php';
}
require_once $autoloader;

add_action( 'wpml_loaded', 'wpml_pb_bootstrap', PHP_INT_MAX );

function wpml_pb_bootstrap() {
	if ( ! defined( 'WPML_ST_VERSION' ) || version_compare( WPML_ST_VERSION, '2.5.3', '<' ) ) {
		add_action( 'admin_notices', 'wpml_pb_required_st_version_2_5_3' );
	}
}

$app = new WPML_Page_Builders_App( new WPML_Page_Builders_Defined() );
$app->add_hooks();

function wpml_pb_required_st_version_2_5_3() {
	?>
	<div class="message error">
		<p><?php esc_html_e( "WPML Page Builders can't be functional because it requires an updated version of WPML String Translation!", 'wpml-page-builders' ); ?></p>

		<p>
			<strong>
		  <?php esc_html_e( 'Please install WPML String Translation 2.5.3 or greater.', 'wpml-page-builders' ); ?>
			</strong>
		</p>
	</div>
	<?php
}
