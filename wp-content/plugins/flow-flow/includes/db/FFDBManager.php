<?php namespace flow\db;
use flow\settings\FFSettingsUtils;
use flow\social\cache\LAFacebookCacheManager;

if ( ! defined( 'WPINC' ) ) die;

/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFDBManager extends LADBManager{
	private $facebook_changed;

	public function __construct($context) {
		parent::__construct($context);
	}

	//OAuth endpoint
	public final function social_auth(){
		if (isset($_REQUEST['type'])){
			if ($_REQUEST['type'] == 'facebook'){
				/** @var LAFacebookCacheManager $facebook_cache */
				$facebook_cache = $this->context['facebook_cache'];
				$facebook_cache->save($_REQUEST['facebook_access_token'], time() + $_REQUEST['expires']);
			}
			else {
				$fieldName = $_REQUEST['type'];
				$options = $this->getOption('options', true);
				$options[$fieldName] = $_REQUEST[$fieldName];
				$this->setOption('options', $options, true);
			}

			header('Location: ' . admin_url('admin.php?page=flow-flow-admin'), true, 301);
		}
		die();
	}

	protected function saveGeneralSettings($settings){
		$settings = parent::saveGeneralSettings($settings);
		//TODO move all auth settings from the general setting to other setting
		$this->setOption('fb_auth_options', $settings['flow_flow_fb_auth_options'], true);
		return $settings;
	}

	protected function customizeResponce(&$responce){
		/** @var LAFacebookCacheManager $facebookCache */
		$facebookCache = $this->context['facebook_cache'];
		if ($this->facebook_changed) {
			$facebookCache->clean();
		}
		$extendedToken = $facebookCache->getAccessToken();
		FFDB::commit();
		
		$responce['fb_extended_token'] = $extendedToken;
	}

	protected function clean_cache($options) {
		$facebook_changed = false;
		$force_load_cache = false;
		$general = $options['flow_flow_options'];
		$old = $this->getOption('options', true);

		if (sizeof($old) > 0){
			if ($general['oauth_access_token'] != $old['oauth_access_token'] ||
			    $general['oauth_access_token_secret'] != $old['oauth_access_token_secret'] ||
			    $general['consumer_secret'] != $old['consumer_secret'] ||
			    $general['consumer_key'] != $old['consumer_key']){
				$this->cleanByFeedType('twitter');
				$force_load_cache = true;
			}
		} else if (trim($general['oauth_access_token']) == '' &&
		           trim($general['oauth_access_token_secret']) == '' &&
		           trim($general['consumer_secret']) == '' &&
		           trim($general['consumer_key']) == ''){
			$this->cleanByFeedType('twitter');
			$force_load_cache = true;
		}

		if (sizeof($old) > 0){
			if ($general['foursquare_client_id'] != $old['foursquare_client_id'] ||
			    $general['foursquare_client_secret'] != $old['foursquare_client_secret']){
				$this->cleanByFeedType('foursquare');
				$force_load_cache = true;
			}
		} else if (trim($general['foursquare_client_id']) == '' && trim($general['foursquare_client_secret']) == ''){
			$this->cleanByFeedType('foursquare');
			$force_load_cache = true;
		}

		if (sizeof($old) > 0){
			if ($general['instagram_access_token'] != $old['instagram_access_token']){
				$this->cleanByFeedType('instagram');
				$force_load_cache = true;
			}
		} else if (trim($general['instagram_access_token']) == ''){
			$this->cleanByFeedType('instagram');
			$force_load_cache = true;
		}

		if (sizeof($old) > 0){
			if ($general['google_api_key'] != $old['google_api_key']){
				$this->cleanByFeedType('google');
				$force_load_cache = true;
			}
		} else if (trim($general['google_api_key']) == ''){
			$this->cleanByFeedType('google');
			$force_load_cache = true;
		}

		$fb = $options['flow_flow_fb_auth_options'];
		$old = $this->getOption('fb_auth_options', true);
		$fb_use_own = FFSettingsUtils::YepNope2ClassicStyleSafe($fb, 'facebook_use_own_app', true);
		$old_use_own = FFSettingsUtils::YepNope2ClassicStyleSafe($old, 'facebook_use_own_app', true);
		if (sizeof($old) > 0){
			if ($fb_use_own != $old_use_own){
				//$this->cleanByFeedType('facebook');
				$force_load_cache = true;
				$facebook_changed = true;
			}
			else {
				if ($fb_use_own) {
					if ($fb['facebook_access_token'] != $old['facebook_access_token'] ||
					    $fb['facebook_app_id'] != $old['facebook_app_id'] ||
					    $fb['facebook_app_secret'] != $old['facebook_app_secret'])
					{
						//$this->cleanByFeedType('facebook');
						$force_load_cache = true;
						$facebook_changed = true;
					}
				}
				else {
					if ($fb['facebook_access_token'] != $old['facebook_access_token'])
					{
						//$this->cleanByFeedType('facebook');
						$force_load_cache = true;
						$facebook_changed = true;
					}
				}
			}
		} else {
			if ((!$fb_use_own && trim($fb['facebook_access_token']) == '') ||
			    ($fb_use_own && trim($fb['facebook_access_token']) == '' && trim($fb['facebook_app_id']) == '' && trim($fb['facebook_app_secret']) == ''))
			{
				//$this->cleanByFeedType('facebook');
				$force_load_cache = true;
				$facebook_changed = true;
			}
		}
		$this->facebook_changed = $facebook_changed;
		return $force_load_cache;
	}
}
