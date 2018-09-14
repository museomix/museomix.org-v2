<?php namespace flow\social\timelines;
if ( ! defined( 'WPINC' ) ) die;

use flow\social\FFFeedUtils;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFUserTimeline implements FFTimeline{
	const URL = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

	private $count;
	private $screenName;
	private $include_rts;
	private $exclude_replies;

	public function init($twitter, $feed){
		$this->count = $twitter->getCount();
		$this->screenName = FFFeedUtils::preparePrefixContent($feed->content, '@');
		$this->exclude_replies = (string)$feed->replies;
		$this->include_rts = (string)$feed->retweets;
	}

	public function getUrl(){
		return self::URL;
	}

	public function getField(){
		$getfield = "?screen_name={$this->screenName}&count={$this->count}&exclude_replies={$this->exclude_replies}&include_rts={$this->include_rts}&tweet_mode=extended";
		return $getfield;
	}
}