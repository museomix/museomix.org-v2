<?php namespace flow\settings;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFGeneralSettings {
	private static $instance = null;

	/**
	 * @return FFGeneralSettings
	 */
	public static function get(){
		return self::$instance;
	}

    private $options;
    private $auth_options;

    public function __construct($options, $auth_options) {
	    $this->options = $options;
	    $this->auth_options = $auth_options;
	    self::$instance = $this;
    }

    public function isAgoStyleDate(){
        return $this->dateStyle() == 'agoStyleDate';
    }

    public function dateStyle() {
        $value = $this->options["general-settings-date-format"];
        if (isset($value)) {
            return $value;
        }
        return 'agoStyleDate';
    }

    public function original() {
        return $this->options;
    }

    public function originalAuth(){
        return $this->auth_options;
    }

    public function useProxyServer(){
        $value = $this->options["general-settings-disable-proxy-server"];
        return FFSettingsUtils::notYepNope2ClassicStyle($value, true);
    }

    public function useCurlFollowLocation(){
        $value = $this->options["general-settings-disable-follow-location"];
        return FFSettingsUtils::notYepNope2ClassicStyle($value, true);
    }

    public function useIPv4(){
        $value = $this->options["general-settings-ipv4"];
        return FFSettingsUtils::YepNope2ClassicStyle($value, true);
    }

    public function getCountOfPostsByFeed(){
	    if (isset($this->options["general-settings-feed-post-count"]) && ctype_digit(strval(($this->options["general-settings-feed-post-count"])))){
		    if (($count = (int)$this->options["general-settings-feed-post-count"]) > 0) {
			    return $count;
		    }
	    }
	    return FF_FEED_POSTS_COUNT;
    }

	public function isSEOMode(){
		$value = $this->options["general-settings-seo-mode"];
		return FFSettingsUtils::YepNope2ClassicStyle($value, false);
	}

	public function canModerate() {
		foreach ( $this->roles() as $role ) {
			if (function_exists('current_user_can') && current_user_can($role)) return true;
		}
		return false;
	}

	public function roles(){
		$roles = array();
		foreach ( $this->options as $key => $value ) {
			if (strpos($key, 'mod-role-') === 0 && $value == 'yep'){
				$roles[] = str_replace('mod-role-', '', $key);
			}
		}
		if (empty($roles)) return array('administrator');
		return $roles;
	}
}