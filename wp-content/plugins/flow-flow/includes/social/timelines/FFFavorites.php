<?php namespace flow\social\timelines;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFFavorites implements FFTimeline{
	const URL = 'https://api.twitter.com/1.1/favorites/list.json';

	private $screenName;
	private $count;

	public function init($feed){
		$this->count = isset($feed->posts) ? $feed->posts : 10;
		$this->screenName = $feed->content;
	}

	public function getUrl(){
		return self::URL;
	}

	public function getField(){
		$getfield = "?screen_name={$this->screenName}&count={$this->count}&include_entities=true&tweet_mode=extended";
		return $getfield;
	}
}