<?php namespace flow\social\timelines;
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
class FFSearch implements FFTimeline{
	const URL = 'https://api.twitter.com/1.1/search/tweets.json';
	
	private $count;
	private $searchQuery;
	private $resultType;
	private $lang;
	private $geo = '';
	
	public function init($twitter, $feed){
		$this->count = $twitter->getCount();
		$this->searchQuery = $feed->content;
		$this->resultType = 'mixed';
		if (isset($feed->lang)) $this->lang = $feed->lang;
		if (isset($feed->{'use-geo'}) && $feed->{'use-geo'} == FFSettingsUtils::YEP){
			$this->geo = "&geocode={$feed->latitude},{$feed->longitude},{$feed->radius}km";
		}
	}
	
	public function getUrl(){
		return self::URL;
	}
	
	public function getField() {
		$lang = (empty($this->lang) || $this->lang == 'all') ? '' : '&lang=' . $this->lang;
		$getfield = "?q={$this->searchQuery}&count={$this->count}&result_type={$this->resultType}&include_entities=true&tweet_mode=extended" . $lang . $this->geo;
		return $getfield;
	}
}