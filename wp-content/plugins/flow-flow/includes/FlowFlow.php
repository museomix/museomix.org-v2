<?php namespace flow;
use flow\cache\FFCache;
use flow\cache\FFCacheAdapter;
use flow\db\FFDB;
use flow\db\FFDBManager;
use flow\settings\FFGeneralSettings;
use flow\settings\FFSettingsUtils;
use flow\settings\FFStreamSettings;
use flow\social\FFFeedUtils;

if ( ! defined( 'WPINC' ) ) die;
if ( ! defined('FF_BY_DATE_ORDER'))   define('FF_BY_DATE_ORDER', 'compareByTime');
if ( ! defined('FF_RANDOM_ORDER'))    define('FF_RANDOM_ORDER',  'randomCompare');
if ( ! defined('FF_SMART_ORDER'))     define('FF_SMART_ORDER',   'smartCompare');
/**
 * Flow-Flow
 *
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `FlowFlowAdmin.php`
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FlowFlow {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '3.0.13';

	protected static $instance = array();

	/**
	 * @param $context
	 *
	 * @return FlowFlow|null
	 */
	public static function get_instance($context = null) {
		if (!array_key_exists('flow-flow', self::$instance)) {
			self::$instance['flow-flow'] = new FlowFlow($context, 'flow-flow', 'flow_flow');
		}
		return self::$instance['flow-flow'];
	}

	public static function get_instance_by_slug($slug) {
		return (array_key_exists($slug, self::$instance)) ? self::$instance[$slug] : null;
	}

	/** @var FFCache */
	private $cache;
	/** @var FFStreamSettings */
	private $settings;
	/** @var FFGeneralSettings */
	private $generalSettings;

	/** @var array */
	protected $context;
	protected $slug;
	protected $slug_down;

	/** @var FFDBManager */
	public $db;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 *
	 * @param array $context
	 * @param $slug
	 * @param $slug_down
	 */
	private function __construct($context, $slug, $slug_down) {
		$this->context = $context;
		$this->db = $context['db_manager'];
		$this->slug = $slug;
		$this->slug_down = $slug_down;

		add_filter('ff_build_public_response', array($this, 'buildResponse'), 1, 8);
	}

	/** $return FFGeneralSettings */
	public function getGeneralSettings(){
		return $this->generalSettings;
	}

	public function register_shortcodes()
	{
		add_shortcode('ff', array($this, 'renderShortCode'));
	}

	public function renderShortCode($attr, $text = null) {
		if (isset($attr['id'])){
			if ($this->prepareProcess()) {
				$this->db->dataInit(true);
				$stream = (object)$this->db->getStream($attr['id']);
				if (isset($stream)) {
					$stream->preview = (isset($attr['preview']) && $attr['preview']);
					$stream->gallery = $stream->preview ? 'nope' : isset($stream->gallery) ? $stream->gallery : 'nope';
					return $this->renderStream($stream, $this->getPublicContext($stream, $this->context));
				}
			} else {
				echo 'Flow-Flow message: Stream with specified ID not found or no feeds were added to stream';
			}
		}
	}

	protected function renderStream($stream, $context){
		$settings = new FFStreamSettings($stream);
		if ($settings->isPossibleToShow()){
			if ( ! in_array( 'curl', get_loaded_extensions() ) ) {
				echo "<p style='background: indianred;padding: 15px;color: white;'>Flow-Flow admin info: Your server doesn't have cURL module installed. Please ask your hosting to check this.</p>";
				return;
			}

			if (!isset($stream->layout) || empty($stream->layout)) {
				echo "<p style='background: indianred;padding: 15px;color: white;'>Flow-Flow admin info: Please choose stream layout on options page.</p>";
				return;
			}

			ob_start();
			$css_version = isset($stream->last_changes) ? $stream->last_changes : '1.0';
			$url = content_url() . '/resources/' . $context['slug'] . '/css/stream-id' . $stream->id . '.css';
			echo "<link rel='stylesheet' id='ff-dynamic-css" . $stream->id . " type='text/css' href='{$url}?ver={$css_version}'/>";

			include($context['root']  . 'views/public.php');
			$output = ob_get_clean();
            $output = str_replace("\r\n", '', $output);
			return $output;
		}
		else
			return '';
	}

	protected function getPublicContext($stream, $context){
		$context['moderation'] = false;
		if (isset($stream->feeds) && !empty($stream->feeds)){
			foreach ( $stream->feeds as $source ) {
				$moderation = FFSettingsUtils::YepNope2ClassicStyleSafe($source, 'mod', false);
				if ($moderation){
					$context['moderation'] = $moderation;
					break;
				}
			}
		}

		$settings = new FFStreamSettings($stream);
		$this->cache->setStream($settings, $context['moderation']);
		$context['stream'] = $stream;
		$context['hashOfStream'] = $this->cache->transientHash($stream->id);
		$context['seo'] = false;////$this->generalSettings->isSEOMode();
		$context['can_moderate'] = FF_USE_WP ? $this->generalSettings->canModerate() : ff_user_can_moderate();
		return $context;
	}

	public function processAjaxRequest() {
		if (isset($_REQUEST['stream-id']) && $this->prepareProcess()) {
			$this->db->dataInit(true);
			$stream = $this->db->getStream($_REQUEST['stream-id']);
			if (isset($stream)) {
				$disableCache = isset($_REQUEST['disable-cache']) ? (bool)$_REQUEST['disable-cache'] : false;
				echo $this->process(array($stream), $disableCache);
			}
		}
		die();
	}

	public function moderation_apply( ){
		if (isset($_REQUEST['stream']) && $this->prepareProcess()) {
			$this->db->dataInit();
			$stream = $this->db->getStream($_REQUEST['stream']);
			if (isset($stream)) {
				$this->cache->setStream(new FFStreamSettings($stream), true);
				$this->cache->moderate();
			}
		}
	}


	public function processAjaxRequestBackground() {
		if ($this->prepareProcess(true)) {
			$this->db->dataInit(true);
			if (isset($_REQUEST['feed_id'])){
				$sources = $this->db->sources();
				if (isset($sources[$_REQUEST['feed_id']])){
					$this->process4feeds(array($sources[$_REQUEST['feed_id']]), false, true);
				}
			}
			if (isset($_REQUEST['stream_id'])){
				$stream = $this->db->getStream($_REQUEST['stream_id']);
				if (isset($stream))
				{
					$this->process4feeds(array($stream), false, true);
				}
			}
		}
	}

	public function processRequest(){
		if (isset($_REQUEST['stream-id']) && $this->prepareProcess()) {
			$this->db->dataInit(true);
			$stream = $this->db->getStream($_REQUEST['stream-id']);
			if (isset($stream)) {
				return $this->process(array($stream), isset($_REQUEST['disable-cache']));
			}
		}
		return '';
	}

	public function refreshCache($streamId = null, $force = false, $withDisabled = false) {
		if ($this->prepareProcess(true)) {
			$enabled = $withDisabled ? FFDB::conn()->parse('`cach`.system_enabled = 0') : FFDB::conn()->parse('`cach`.enabled = 1 AND `cach`.system_enabled = 1');
			if (empty($streamId))
				$sql = FFDB::conn()->parse('SELECT `cach`.`feed_id` FROM ?n `cach` WHERE ?p AND (`cach`.last_update + `cach`.cache_lifetime * 60) < UNIX_TIMESTAMP() ORDER BY `cach`.last_update', $this->db->cache_table_name, $enabled);
			else
				$sql = FFDB::conn()->parse('SELECT `cach`.`feed_id` FROM ?n `cach` INNER JOIN ?n `ss` ON `ss`.feed_id = `cach`.feed_id WHERE ?p AND `ss`.stream_id = ?s AND (`cach`.last_update + `cach`.cache_lifetime * 60) < UNIX_TIMESTAMP() ORDER BY `cach`.last_update',
					$this->db->cache_table_name, $this->db->streams_sources_table_name, $enabled, $streamId);
			try {
				if (false !== ($feeds = FFDB::conn()->getCol($sql))){
					$useIpv4 = $this->getGeneralSettings()->useIPv4();
					$use = $this->getGeneralSettings()->useCurlFollowLocation();
					for ( $i = 0; $i < 8; $i ++ ) {
						if (isset($feeds[$i])){
							//TODO: anf: Refact enter to refresh cache
							$feed_id = $feeds[$i];
							if (FF_USE_DIRECT_WP_CRON){
								$_REQUEST['feed_id'] = $feed_id;
								$this->processAjaxRequestBackground();
							}
							else {
								//$_COOKIE['XDEBUG_SESSION'] = 'PHPSTORM';
								FFFeedUtils::getFeedData($this->getLoadCacheUrl($feed_id, $force), 1, false, false, $use, $useIpv4);
							}
						}
					}
				}
			}
			catch(\Exception $e){
				error_log($e->getMessage());
				error_log($e->getTraceAsString());
			}
		}
	}

	public function refreshCache4Disabled() {
		$this->refreshCache(null, false, true);
	}

	protected function getLoadCacheUrl($streamId = null, $force = false){
		return FF_AJAX_URL . "?action=load_cache&feed_id={$streamId}&force={$force}";
	}

	private function prepareProcess($forceLoadCache = false) {
		if ($this->db->countFeeds() > 0) {
			$this->generalSettings = $this->db->getGeneralSettings();
			$this->cache = new FFCacheAdapter($this->context, $forceLoadCache);
			return true;
		}
		return false;
	}

	private function process($streams, $disableCache = false, $background = false) {
		foreach ($streams as $stream) {
			try {
				$moderation = false;
				foreach ( $this->db->sources() as $source ) {
					$moderation = FFSettingsUtils::YepNope2ClassicStyleSafe($source, 'mod', false);
					if ($moderation){
						break;
					}
				}

				$this->settings = new FFStreamSettings($stream);
				$this->cache->setStream($this->settings, $moderation);
				$result = $this->cache->posts($this->createFeedInstances($this->settings->getAllFeeds()), $disableCache);
				if ($background) return $result;
				$errors = $this->cache->errors();
				$hash = $this->cache->hash();
				return $this->prepareResult($result, $errors, $hash);
			} catch (\Exception $e) {
				error_log($e->getMessage());
				error_log($e->getTraceAsString());
			}
		}
	}

	private function process4feeds($feeds, $disableCache = false, $background = false) {
		try {
			//$this->settings = new FFStreamSettings($stream);
			//$this->cache->setStream($this->settings);
			$result = $this->cache->posts($this->createFeedInstances($feeds), $disableCache);
			if ($background) return $result;
			$errors = $this->cache->errors();
			$hash = $this->cache->hash();
			return $this->prepareResult($result, $errors, $hash);
		} catch (\Exception $e) {
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
		}
	}

	private function createFeedInstances($feeds) {
		$result = array();
		if (is_array($feeds)) {
			foreach ($feeds as $feed) {
				$feed = (object)$feed;
				$wpt = 'type';
				if ($feed->type == 'linkedin') {
					$feed->type = 'linkedIn';
				}
				if ($feed->type == 'wordpress'){
					if (!FF_USE_WP) continue;
					$wpt = 'wordpress-type';
				}

				$clazz = new \ReflectionClass( 'flow\\social\\FF' . ucfirst($feed->$wpt) );//don`t change this line
				$instance = $clazz->newInstance();
				$instance->init($this->context, $this->generalSettings, $feed);
				$result[] = $instance;
			}
		}
		return $result;
	}

	private function prepareResult(array $all, $errors, $hash) {
		$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
		$oldHash = isset($_REQUEST['hash']) ? $_REQUEST['hash'] : $hash;
		if (isset($_REQUEST['recent']) && $hash != null){
			$oldHash = $hash;
		}
		list($status, $errors) = $this->status();
		$result = FF_USE_WP ? apply_filters('ff_build_public_response', array(), $all, $this->context, $errors, $oldHash, $page, $status, $this->settings) :
			$this->buildResponse(array(), $all, $this->context, $errors, $oldHash, $page, $status, $this->settings);
		if (($result === false) && (JSON_ERROR_UTF8 === json_last_error())){
			foreach ( $all as $item ) {
				json_encode($item);
				if (JSON_ERROR_UTF8 === json_last_error()){
					$item->text = mb_convert_encoding($item->text, "UTF-8", "auto");
				}
			}
			$result = FF_USE_WP ? apply_filters('ff_build_public_response', $result, $all, $this->context, $errors, $oldHash, $page, $status, $this->settings) :
				$this->buildResponse($result, $all, $this->context, $errors, $oldHash, $page, $status, $this->settings);
		}
		
		$result['server_time'] = time();
		$json = json_encode($result);
		if ($json === false){
			$errors = array();
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					echo ' - No errors';
					break;
				case JSON_ERROR_DEPTH:
					$errors[] = 'Json encoding error: Maximum stack depth exceeded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$errors[] = 'Json encoding error: Underflow or the modes mismatch';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$errors[] = 'Json encoding error: Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					$errors[] = 'Json encoding error: Syntax error, malformed JSON';
					break;
				case JSON_ERROR_UTF8:
					for ( $i = 0; sizeof( $result['items'] ) > $i; $i++ ) {
						if (function_exists('mb_convert_encoding'))
							$result['items'][$i]->text = mb_convert_encoding($result['items'][$i]->text, "UTF-8", "auto");
					}
					$json = json_encode($result);
					if ($json === false){
						$errors[] = 'Json encoding error:  Malformed UTF-8 characters, possibly incorrectly encoded';
					}
					else {
						return $json;
					}
					break;
				default:
					$errors[] = 'Json encoding error';
					break;
			}
			$result = FF_USE_WP ? apply_filters('ff_build_public_response', array(), array(), $this->context, $errors, $oldHash, $page, 'errors', $this->settings) :
				$this->buildResponse($result, $all, $this->context, $errors, $oldHash, $page, 'errors', $this->settings);
			$json = json_encode($result);
		}
		return $json;
	}

	/**
	 * @param $result
	 * @param $all
	 * @param $context
	 * @param $errors
	 * @param $oldHash
	 * @param $page
	 * @param $status
	 * @param FFStreamSettings $stream
	 *
	 * @return array
	 */
	public function buildResponse($result, $all, $context, $errors, $oldHash, $page, $status, $stream){
		$streamId = (int) $stream->getId();
		$countOfPages = isset($_REQUEST['countOfPages']) ? $_REQUEST['countOfPages'] : 0;
		$result = array('id' => $streamId, 'items' => $all, 'errors' => $errors,
		             'hash' => $oldHash, 'page' => $page, 'countOfPages' => $countOfPages, 'status' => $status);
		return $result;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if(version_compare(PHP_VERSION, '5.3.0') == -1){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires PHP version 5.3.0 or higher. Pls update your PHP version or ask hosting support to do this for you, you are using old and unsecure one' );
		}

		if(!function_exists('curl_version')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires curl extension for php. Please install/enable this extension or ask your hosting to help you with this.' );
		}

		if(!function_exists('mysqli_connect')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires mysqli extension for MySQL. Please install/enable this extension on your server or ask your hosting to help you with this. <a href="http://php.net/manual/en/mysqli.installation.php">Installation guide</a>' );
		}

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();
					restore_current_blog();
				}
			}
			else self::single_activate();
		}
		else self::single_activate();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();
					restore_current_blog();
				}
			}
			else self::single_deactivate();
		}
		else self::single_deactivate();
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) )  return;
		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;
		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";
		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		$context = ff_get_context();
		/** @var FFDBManager $db */
		$db = $context['db_manager'];
		$db->migrate();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		wp_clear_scheduled_hook( 'flow_flow_load_cache' );
		wp_clear_scheduled_hook( 'flow_flow_load_cache_4disabled' );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$opts =  $this->get_options();
        $plugins_url = plugins_url();
		$js_opts = array(
            'streams' => new \stdClass(),
            'open_in_new' => $opts['general-settings-open-links-in-new-window'],
			'filter_all' => __('All', 'flow-flow'),
			'filter_search' => __('Search', 'flow-flow'),
			'expand_text' => __('Expand', 'flow-flow'),
			'collapse_text' => __('Collapse', 'flow-flow'),
			'posted_on' => __('Posted on', 'flow-flow'),
			'show_more' => __('Show more', 'flow-flow'),
			'date_style' => $opts['general-settings-date-format'],
			'dates' => array(
				'Yesterday' => __('Yesterday', 'flow-flow'),
				's' => __('s', 'flow-flow'),
				'm' => __('m', 'flow-flow'),
				'h' => __('h', 'flow-flow'),
				'ago' => __('ago', 'flow-flow'),
				'months' => array(
					__('Jan', 'flow-flow'), __('Feb', 'flow-flow'), __('March', 'flow-flow'),
					__('April', 'flow-flow'), __('May', 'flow-flow'), __('June', 'flow-flow'),
					__('July', 'flow-flow'), __('Aug', 'flow-flow'), __('Sept', 'flow-flow'),
					__('Oct', 'flow-flow'), __('Nov', 'flow-flow'), __('Dec', 'flow-flow')
				),
			),
			'lightbox_navigate' => __('Navigate with arrow keys', 'flow-flow'),
			'server_time' => time(),
			'forceHTTPS' => $opts['general-settings-https'],
            'isAdmin' => function_exists('current_user_can') && current_user_can( 'manage_options' ),
            'ajaxurl' => FF_AJAX_URL,
            'isLog' => isset($_REQUEST['fflog']) && $_REQUEST['fflog'] == 1,
            'plugin_base' => $plugins_url . '/' . $this->slug ,
			'plugin_ver' => self::VERSION
		);

		wp_enqueue_script($this->slug . '-plugin-script', $plugins_url . '/' . $this->slug . '/js/require-utils.js', array('jquery'), self::VERSION);
		wp_localize_script($this->slug . '-plugin-script', $this->getNameJSOptions(), $js_opts);
	}

	public function get_options() {
		$options = $this->db->getOption('options', true);
		return $options;
	}

	public function get_auth_options() {
		$options = $this->db->getOption('fb_auth_options', true);
		return $options;
	}

	protected function getNameJSOptions(){
		return 'FlowFlowOpts';
	}

	private function status() {
		$status_info = FFDB::getStatusInfo($this->db->cache_table_name, (int)$this->settings->getId(), false);
		if ($status_info['status'] == '0'){
            return array('errors', isset($status_info['error']) ? $status_info['error'] : '');
		}
		if ($status_info['status'] == '1'){
			$feed_count = sizeof($this->settings->getAllFeeds());
			$status = ($feed_count == (int)$status_info['feeds_count']) ? 'get' : 'building';
			return array($status, array());
		}
		throw new \Exception('Was received the unknown status');
	}
}
