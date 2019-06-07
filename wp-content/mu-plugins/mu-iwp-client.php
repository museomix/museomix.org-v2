<?php

/*
Plugin Name: InfiniteWP - Client Loader
Plugin URI: https://infinitewp.com/
Description: This plugin will be created automatically when you activate your InfiniteWP Client plugin to improve the performance. And it will be deleted when you deactivate the client plugin.
Author: Revmakx
Author URI: https://infinitewp.com/
*/

if (!function_exists('untrailingslashit') || !defined('WP_PLUGIN_DIR')) {
    // WordPress is probably not bootstrapped.
    exit;
}

if (file_exists(untrailingslashit(WP_PLUGIN_DIR).'/iwp-client/init.php')) {
    if (in_array('iwp-client/init.php', (array) get_option('active_plugins')) ||
        (function_exists('get_site_option') && array_key_exists('iwp-client/init.php', (array) get_site_option('active_sitewide_plugins')))) {
        $GLOBALS['iwp_is_mu'] = true;
        include_once untrailingslashit(WP_PLUGIN_DIR).'/iwp-client/init.php';
    }
}
