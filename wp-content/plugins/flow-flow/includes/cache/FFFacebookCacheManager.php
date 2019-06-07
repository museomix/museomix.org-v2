<?php namespace flow\cache;
if ( ! defined( 'WPINC' ) ) die;

use flow\db\FFDB;
use flow\db\LADBManager;
use flow\social\cache\LAFacebookCacheManager;
use flow\social\FFFeedUtils;
use flow\social\LASocialException;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFFacebookCacheManager implements LAFacebookCacheManager {
    protected static $postfix_at = 'la_facebook_access_token';
	protected static $postfix_at_expires = 'la_facebook_access_token_expires';

	/** @var LADBManager  */
	protected $db = null;
	private $auth = null;
	private $error = null;
	private $access_token = null;

	private $hasHitLimit;
	private $creationTime;
	private $request_count;
	private $global_request_count;
	private $global_request_array;

    public function __construct($context) {
		$this->db = $context['db_manager'];
    }

	public function getError(){
		return $this->error;
	}

	public function clean(){
		$this->deleteOption($this->getNameExtendedAccessToken());
		$this->deleteOption($this->getNameExtendedAccessToken(true));
		$this->db->deleteOption('facebook_access_token');
	}

	public function getAccessToken(){
		if ($this->access_token != null) return $this->access_token;

		$token = null;
		if (false != ($token = $this->getStoredToken())){
			if ($this->isExpiredToken($token)){
				list($token, $expires, $error) = $this->refreshToken($token);
				if ($error == null) $this->save($token, $expires);
				$this->error = $error;
			}
			$this->access_token = $token;
		}
		else {
			$this->error = array(
				'type'    => 'facebook',
				'message' => 'Facebook access token is empty.'
			);
		}
		return $token;
	}

	protected function isExpiredToken( $token ) {
		$expires = $this->getOption($this->getNameExtendedAccessToken(true));
		return $expires === false || time() > ($expires - 2629743);
	}

	protected function getStoredToken() {
		$at = $this->getNameExtendedAccessToken();
		if (false !== ($access_token_transient = $this->getOption($at))){
			$access_token = $access_token_transient;
		}
		else{
			$auth = $this->getAuth();
			$access_token = @$auth['facebook_access_token'];
			if(!isset($access_token) || empty($access_token)){
				return false;
			}
		}
		return $access_token;
	}

	protected function refreshToken( $oldToken ) {
		$token_url = $this->getRefreshTokenUrl($oldToken);
		$settings = $this->db->getGeneralSettings();
		$response = FFFeedUtils::getFeedData($token_url, 20, false, true, $settings->useCurlFollowLocation(), $settings->useIPv4());
		if (false !== $response['response']){
			$response = (string)$response['response'];
			$response = (array)json_decode($response);
			$expires = (sizeof($response) > 2) ? (int)$response['expires_in'] : time() + 2629743*2;
			$access_token = $response['access_token'];
			return array($access_token, $expires, null);
		}
		else if (isset($response['errors'])) {
			$error = $response['errors'][0];
			return array(null, null, array(
				'type'    => 'facebook',
				'message' => $this->filter_error_message($error['msg']),
				'url' => $token_url
			));
		}
		return array(null, null, false);
	}

	public function save($token, $expires){
		$this->updateOption($this->getNameExtendedAccessToken(), $token);
		$this->updateOption($this->getNameExtendedAccessToken(true), time() + ( isset($expires) ? $expires : 2629743 ));
	}

	protected function getRefreshTokenUrl($access_token){
		$auth = $this->getAuth();
		$facebookAppId = $auth['facebook_app_id'];
		$facebookAppSecret = $auth['facebook_app_secret'];
		return "https://graph.facebook.com/oauth/access_token?client_id={$facebookAppId}&client_secret={$facebookAppSecret}&grant_type=fb_exchange_token&fb_exchange_token={$access_token}";
	}

	protected function getNameExtendedAccessToken($expires = false){
		$auth = $this->getAuth();
		$facebookAppId = $auth['facebook_app_id'];
		$facebookAppSecret = $auth['facebook_app_secret'];
		$name = $expires ? self::$postfix_at_expires : self::$postfix_at;
		return $name . substr(hash('md5', $facebookAppId . $facebookAppSecret), 0, 6);
	}

	protected function getAuth(){
		if (empty($this->auth)){
			$this->auth = $this->db->getOption('fb_auth_options', true);
		}
		return $this->auth;
	}

	private function getOption($name){
		return FF_USE_WP ? get_option($name) : $this->db->getOption($name);
	}

	private function updateOption($name, $value){
		FF_USE_WP ? update_option($name, $value) : $this->db->setOption($name, $value);
	}

	private function deleteOption($name){
		FF_USE_WP ? delete_option($name) : $this->db->deleteOption($name);
	}

	private function filter_error_message($message){
		if (is_array($message)){
			if (sizeof($message) > 0 && isset($message[0]['msg'])){
				return stripslashes(htmlspecialchars($message[0]['msg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
			}
			else {
				return '';
			}
		}
		return stripslashes(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
	}

	public function startCounter() {
		$this->request_count = 0;
		$this->hasHitLimit = false;
		if (FFDB::beginTransaction()){
			$limit = $this->db->getOption('fb_limit_counter', true, true);
			if ($limit === false){
				@$this->db->setOption('fb_limit_counter', array(), true, false);
				$limit = $this->db->getOption('fb_limit_counter', true, true);
			}

			if (!is_array($limit)){
				FFDB::rollback();
				throw new LASocialException('Can`t save `fb_limit_counter` option to mysql db.');
			}

			$this->creationTime = time();
			$this->global_request_count = 0;
			$limitTime = $this->creationTime - 3600;
			$result = array();
			foreach ( $limit as $time => $count ) {
				if ($time > $limitTime) {
					$result[$time] = $count;
					$this->global_request_count += (int)$count;
				}
			}
			$this->global_request_array = $result;

			if ($this->global_request_count + 4 > FF_FACEBOOK_RATE_LIMIT) {
				FFDB::rollback();
				throw new LASocialException('Your site has hit the Facebook API rate limit. <a href="http://docs.social-streams.com/article/133-facebook-app-request-limit-reached" target="_blank">Troubleshooting</a>.');
			}
		}
		else {
			FFDB::rollback();
			throw new LASocialException('Can`t get mysql transaction.');
		}
		return true;
	}

	public function stopCounter() {
		if ($this->request_count > 0) {
			$this->global_request_array[$this->creationTime] = $this->request_count;
		}
		$this->db->setOption('fb_limit_counter', $this->global_request_array, true, false);
		FFDB::commit();
	}

	public function hasLimit() {
		if ($this->hasHitLimit) return false;
		if ($this->global_request_count + $this->request_count + 3 > FF_FACEBOOK_RATE_LIMIT) {
			$this->hasHitLimit = true;
			return false;
		}
		return true;
	}

	public function addRequest() {
		$this->request_count++;
	}

	public function getIdPosts($feedId) {
		return $this->db->getIdPosts($feedId);
	}
}