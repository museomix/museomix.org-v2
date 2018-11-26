<?php if ( ! defined( 'WPINC' ) ) die;
/**
 * @package   Flow_Flow
 * @author    Looks Awesome <hello@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2018 Looks Awesome
 *
 * @wordpress-plugin
 * Plugin Name:       Flow-Flow
 * Plugin URI:        social-streams.com
 * Description:       Awesome social streams on your site
 * Version:           4.1.2
 * Author:            Looks Awesome
 * Author URI:        looks-awesome.com
 * Text Domain:       flow-flow
 * Domain Path:       /languages
 */
if ( ! defined( 'FF_USE_WP' ) )  define( 'FF_USE_WP', true );
if ( ! defined( 'FF_USE_WPDB' ) )  define( 'FF_USE_WPDB', false );
if ( ! defined( 'FF_USE_WP_CRON' ) ) define('FF_USE_WP_CRON', true);
if ( ! defined( 'FF_USE_DIRECT_WP_CRON' ) ) define('FF_USE_DIRECT_WP_CRON', false);
if ( ! defined( 'FF_FORCE_FIT_MEDIA' ) ) define('FF_FORCE_FIT_MEDIA', false);
if ( ! defined( 'FF_FEED_POSTS_COUNT' ) ) define('FF_FEED_POSTS_COUNT', 100);
if ( ! defined( 'FF_LOCALE'))  define('FF_LOCALE', get_locale());//TODO add a slash to the end
if ( ! defined( 'FF_DB_CHARSET')) define('FF_DB_CHARSET', defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8');
if ( ! defined( 'PFC_IGNORE_COMPOSER_WARNING' ) ) define('PFC_IGNORE_COMPOSER_WARNING', true);

if (!class_exists('LAClassLoader')){
	/** @noinspection PhpIncludeInspection */
    require_once( plugin_dir_path( __FILE__ ) . 'LAClassLoader.php' );
    LAClassLoader::get(plugin_dir_path( __FILE__ ))->register();
}

$facade = la\core\LAActivatorFacade::get();
$facade->registry_activator(new flow\FlowFlowActivator(__FILE__));