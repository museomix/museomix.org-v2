<?php namespace flow\settings;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */

class FFStreamSettings
{
    private $lifeTimeCache;
    private $stream;

    function __construct($stream) {
        $this->stream = (array)$stream;
    }

    public function getId() {
        return $this->stream['id'];
    }

    /**
     * @return string
     */
    public function getCountOfPostsOnPage() {
	    if (isset($this->stream["page-posts"]) && $this->stream["page-posts"] != ''){
		    return $this->stream["page-posts"];
	    }
        return '20';
    }

    public function getAllFeeds() {
	    return $this->stream['feeds'];
        //return json_decode($this->stream['feeds']);
    }

    public function original() {
        return $this->stream;
    }

    public function isPossibleToShow(){
        $mobile = (bool)$this->is_mobile();
        $hideOnMobile = FFSettingsUtils::YepNope2ClassicStyleSafe($this->stream, 'hide-on-mobile', false);
        if ($hideOnMobile && $mobile) return false;
        $hideOnDesktop = FFSettingsUtils::YepNope2ClassicStyleSafe($this->stream, 'hide-on-desktop', false);
        if ($hideOnDesktop && !$mobile) return false;
        $private = FFSettingsUtils::YepNope2ClassicStyleSafe($this->stream, 'private', false);
        if ($private && !is_user_logged_in()) return false;
        return true;
    }

	public function showOnlyMediaPosts(){
		if (!isset($this->stream["show-only-media-posts"])) return false;
		$showOnlyMediaPosts = FFSettingsUtils::YepNope2ClassicStyle($this->stream["show-only-media-posts"], false);
		return $showOnlyMediaPosts;
	}

    public function getImageWidth() {
        $value = isset($this->stream["theme"]) ? $this->stream["theme"] : 'custom';
	    $width = isset($this->stream["width"]) ? $this->stream["width"] : 300;
	    $width = intval($width);
        return ($value == 'classic') ? $width - 30 : $width;
    }

    /**
     * @return string
     */
    public function order() {
	    if (isset($this->stream["order"]) && !empty($this->stream["order"])) {
	        $value = $this->stream["order"];
	        return $value;
        }
        return FF_BY_DATE_ORDER;
    }

    private function is_mobile(){
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER["HTTP_USER_AGENT"] : "unknown"));
    }
}