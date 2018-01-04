<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Flow_Flow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Check to enable uninstall plugin
$value = get_option('flow_flow_general_uninstall');
if ($value == 'yep') {
	delete_transients();
	delete_options();
	delete_custom_file_directory();
	clean_db();
}

function delete_options() {
	delete_option('ff_db_version');//old version option
	delete_option('flow_flow_db_version');//old version option
	delete_option('flow_flow_options');
	delete_option('flow_flow_fb_auth_options');
	delete_option('flow_flow_general_uninstall');
}

function delete_transients() {
	//delete_transient( 'TRANSIENT_NAME' );
}

/**
 * Remove custom file directory for main site
 */
function delete_custom_file_directory() {
	$directory = WP_CONTENT_DIR . '/resources/flow-flow/css/';
	if (is_dir($directory)) {
		foreach(glob($directory.'*.*') as $v){
			unlink($v);
		}
		rmdir($directory);
	}
	$directory = WP_CONTENT_DIR . '/resources/flow-flow/';
	if (is_dir($directory)) {
		foreach(glob($directory.'*.*') as $v){
			unlink($v);
		}
		rmdir($directory);
	}
}

function clean_db() {
	global $wpdb;
	$prefix = $wpdb->base_prefix . 'ff_';
	$table_name = $prefix . 'cache';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'image_cache';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'options';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'posts';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'streams';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'streams_sources';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'snapshots';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'comments';
	$wpdb->query("DROP TABLE {$table_name}");
	$table_name = $prefix . 'post_media';
	$wpdb->query("DROP TABLE {$table_name}");
}