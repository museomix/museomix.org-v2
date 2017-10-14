<?php namespace flow\cache;
use flow\settings\FFSettingsUtils;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFFacebookCacheAdapter implements LAFacebookCacheManager{
	/** @var $manager LAFacebookCacheManager */
	private $manager = null;

	public function clean() {
		$this->get()->clean();
	}

	public function getAccessToken() {
		return $this->get()->getAccessToken();
	}

	public function getError() {
		return $this->get()->getError();
	}

	public function save( $token, $expires ) {
		$this->get()->save( $token, $expires );
	}

	/**
	 * @return LAFacebookCacheManager
	 */
	private function get(){
		if ($this->manager == null){
			global $flow_flow_context;
			$db = $flow_flow_context['db_manager'];
			$auth = $db->getOption('fb_auth_options', true);
			$fb_use_own = FFSettingsUtils::YepNope2ClassicStyleSafe($auth, 'facebook_use_own_app', true);
			$this->manager = $fb_use_own ? new FFFacebookCacheManager($flow_flow_context) : new FFFacebookCacheManager2($flow_flow_context);
		}
		return $this->manager;
	}
}