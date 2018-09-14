<?php namespace flow\cache;
use flow\settings\FFSettingsUtils;
use flow\social\cache\LAFacebookCacheManager;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFFacebookCacheAdapter implements LAFacebookCacheManager {
	private $context;
	
	public function __construct(){
	}
	
	/** @var $manager LAFacebookCacheManager */
	private $manager = null;

	public function setContext($context){
		$this->context = $context;
	}
	
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
			$db = $this->context['db_manager'];
			$auth = $db->getOption('fb_auth_options', true);
			$fb_use_own = FFSettingsUtils::YepNope2ClassicStyleSafe($auth, 'facebook_use_own_app', true);
			$this->manager = $fb_use_own ? new FFFacebookCacheManager($this->context) : new FFFacebookCacheManager2($this->context);
		}
		return $this->manager;
	}

	public function startCounter() {
		$this->get()->startCounter();
	}

	public function stopCounter() {
		$this->get()->stopCounter();
	}

	public function hasLimit() {
		return $this->get()->hasLimit();
	}

	public function addRequest() {
		$this->get()->addRequest();
	}

	public function getIdPosts( $feedId ) {
		return $this->get()->getIdPosts($feedId);
	}
}