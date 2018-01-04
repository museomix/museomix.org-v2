<?php namespace flow\social\timelines;
if ( ! defined( 'WPINC' ) ) die;

use flow\settings\FFSettingsUtils;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFListTimeline implements FFTimeline{
	const URL = 'https://api.twitter.com/1.1/lists/statuses.json';
	
	private $count;
	private $screenName;
	private $include_rts;
	private $listName;
	
	public function init($twitter, $feed) {
		$this->count = $twitter->getCount();
		$this->listName = $feed->{'list-name'};
		$this->screenName = $feed->content;
		$this->include_rts = (string)FFSettingsUtils::YepNope2ClassicStyle($feed->retweets);
	}
	
	public function getUrl() {
		return self::URL;
	}
	
	public function getField() {
		$getfield = "?slug={$this->listName}&owner_screen_name={$this->screenName}&count={$this->count}&include_rts={$this->include_rts}&include_entities=true&tweet_mode=extended";
		return $getfield;
	}
}