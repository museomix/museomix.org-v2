<?php namespace flow;
use flow\settings\FFSettingsUtils;
use flow\settings\FFStreamSettings;

if ( ! defined( 'WPINC' ) ) die;
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
class FlowFlow extends LABase{
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
	protected function __construct($context, $slug, $slug_down) {
		parent::__construct($context, $slug, $slug_down);
	}
	
	protected function getShortcodePrefix(){
		return 'ff';
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

	protected function getLoadCacheUrl($streamId = null, $force = false){
		$ajax_url = $this->context['ajax_url'];
		return $ajax_url . "?action=load_cache&feed_id={$streamId}&force={$force}";
	}

	protected function enqueueStyles(){
	}

	protected function enqueueScripts(){
		$version = $this->context['version'];
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
            'followers' => __('Followers', 'flow-flow'),
            'following' => __('Following', 'flow-flow'),
            'posts' => __('Posts', 'flow-flow'),
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
            'ajaxurl' => $this->context['ajax_url'],
            'isLog' => isset($_REQUEST['fflog']) && $_REQUEST['fflog'] == 1,
            'plugin_base' => $plugins_url . '/' . $this->slug ,
			'plugin_ver' => $this->context['version']
		);

		wp_enqueue_script($this->slug . '-plugin-script', $plugins_url . '/' . $this->slug . '/js/require-utils.js', array('jquery'), $version);
		wp_localize_script($this->slug . '-plugin-script', $this->getNameJSOptions(), $js_opts);
	}

	protected function getNameJSOptions(){
		return 'FlowFlowOpts';
	}
}
