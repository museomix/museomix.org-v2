<?php namespace flow\db\migrations;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFMigration_2_1 implements ILADBMigration{

	public function version() {
		return '2.1';
	}

	public function execute($conn, $manager) {
		$options = $manager->getOption('options', true);
		if ($options === false) $options = array();
		unset($options['last_submit']);
		unset($options['feeds_changed']);
		$options = $this->setDefaultValueIfNeeded($options);
		$manager->setOption('options', $options, true);

		$options = $manager->getOption('fb_auth_options', true);
		if ($options === false) $options = array();
		if (!isset($options['facebook_access_token'])) $options['facebook_access_token'] = '';
		if (!isset($options['facebook_app_id'])) $options['facebook_app_id'] = '';
		if (!isset($options['facebook_app_secret'])) $options['facebook_app_secret'] = '';
		if (!isset($options['facebook_app_secret'])) $options['facebook_use_own_app'] = '';
		$manager->setOption('fb_auth_options', $options, true);
	}

	private function setDefaultValueIfNeeded($options) {
		if (!isset($options['oauth_access_token'])) $options['oauth_access_token'] = '';
		if (!isset($options['oauth_access_token_secret'])) $options['oauth_access_token_secret'] = '';
		if (!isset($options['consumer_secret'])) $options['consumer_secret'] = '';
		if (!isset($options['consumer_key'])) $options['consumer_key'] = '';
		if (!isset($options['instagram_access_token'])) $options['instagram_access_token'] = '';
		if (!isset($options['google_api_key'])) $options['google_api_key'] = '';
		if (!isset($options['foursquare_access_token'])) $options['foursquare_access_token'] = '';
		if (!isset($options['foursquare_client_id'])) $options['foursquare_client_id'] = '';
		if (!isset($options['foursquare_client_secret'])) $options['foursquare_client_secret'] = '';
		if (!isset($options['general-settings-date-format'])) $options['general-settings-date-format'] = 'agoStyleDate';
		if (!isset($options['general-settings-open-links-in-new-window'])) $options['general-settings-open-links-in-new-window'] = 'nope';
		if (!isset($options['general-settings-disable-proxy-server'])) $options['general-settings-disable-proxy-server'] = 'nope';
		if (!isset($options['general-settings-disable-follow-location'])) $options['general-settings-disable-follow-location'] = 'nope';
//		if (!array_key_exists('general-settings-seo-mode', $options)) $options['general-settings-seo-mode'] = 'yep';
		return $options;
	}
}