<?php namespace flow\social;
use flow\social\timelines\FFFavorites;
use flow\social\timelines\FFHomeTimeline;
use flow\social\timelines\FFListTimeline;
use flow\social\timelines\FFSearch;
use flow\social\timelines\FFTimeline;
use flow\social\timelines\FFUserTimeline;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFTwitter extends FFBaseFeed{
	private static $GET = "GET";

	/** @var  FFTimeline */
	private $timeline;
	/** @var FFTwitterAPIExchange */
	private $restService;
	private $image;
	private $media;
	private $carousel;

	public function __construct() {
		parent::__construct( 'twitter' );
	}

	public function deferredInit($feed){
		$this->restService = new FFTwitterAPIExchange($feed->twitter_access_settings);
		$this->timeline = $this->getTimeline($feed);
	}

	public function onePagePosts(){
		$json = json_decode($this->restService
			->setGetfield($this->timeline->getField())
			->buildOauth($this->timeline->getUrl(), self::$GET)
			->performRequest(), $assoc = TRUE);

		if (isset($json['errors'])) {
			foreach ($json['errors'] as $error) {
				$msg = $error['message'];
				$this->errors[] = array(
					'type'    => 'twitter',
					'message' => $this->filterErrorMessage($msg),
					'url' => $this->timeline->getUrl()
				);
				throw new \Exception();
			}
			return array();
		}
		return $this->parseRequest($json);
	}

	private function parseRequest($json) {
		$tmp = $json;
		$result = array();

		if (isset($json['statuses'])) {
			$tmp = $json['statuses'];
		}
		if (isset($tmp) && is_array($tmp)){
			foreach ($tmp as $t) {
				$this->image = null;
				$this->media = null;
				$this->carousel = array();
				$tc = new \stdClass();
				$tc->feed_id = $this->id();
				$tc->smart_order = 0;
				$tc->id = $t['id_str'];
				$tc->type = $this->getType();
				$tc->nickname = '@'.$t['user']['screen_name'];
				$tc->screenname = (string)$t['user']['name'];
				$tc->userpic = str_replace('.jpg', '_200x200.jpg', str_replace('_normal', '', (string)$t['user']['profile_image_url']));
				$tc->system_timestamp = strtotime($t['created_at']);
				$tc->text = $this->getText($t);
				$tc->userlink = 'https://twitter.com/'.$t['user']['screen_name'];
				$tc->permalink = $tc->userlink . '/status/' . $tc->id;
				$tc->media = $this->getMedia($t);

				if (!is_null($this->image)) {
					$tc->img = $this->image;
					if (sizeof($this->carousel) > 0 && is_null($tc->media)){
						$tc->media = $this->carousel[0];
					}
					if (is_null($tc->media)) $tc->media = $this->image;
				}
				$tc->carousel = $this->carousel;
				@$tc->additional = array('shares' => (string)$t['retweet_count'], 'likes' => (string)$t['favorite_count'], 'comments' => (string)$t['reply_count']);
				if ($this->isSuitablePost($tc)) $result[$tc->id] = $tc;
			}
		}
		return $result;
	}

	private function getTimeline($feed){
		$timeline = null;
		switch ($feed->{'timeline-type'}) {
			case 'home_timeline':
				$timeline = new FFHomeTimeline();
				break;
			case 'user_timeline':
				$timeline = new FFUserTimeline();
				break;
			case 'favorites':
				$timeline = new FFFavorites();
				break;
			case 'list_timeline':
				$timeline = new FFListTimeline();
				break;
			default:
				$timeline = new FFSearch();
		}
		$timeline->init($this, $feed);
		return $timeline;
	}

	private function getChAr($text){
		$ChAr = array();
		if (function_exists('mb_detect_encoding')){
			$encoding = mb_detect_encoding($text);
			if ($encoding === false){
				$encoding = mb_internal_encoding();
			}
			for ($i = 0; $i < mb_strlen($text, $encoding); $i++) {
				$ch = mb_substr($text, $i, 1, $encoding);
				if ($ch <> "\n") $ChAr[] = $ch; else $ChAr[] = "\n<br/>";
			}
		}
		else {
			for ($i = 0; $i < strlen($text); $i++) {
				$ch = substr($text, $i, 1);
				if ($ch <> "\n") $ChAr[] = $ch; else $ChAr[] = "\n<br/>";
			}
		}
		return $ChAr;
	}

	private function getText($tweet){
		if (!isset($tweet['entities'])){
			return isset($tweet['text']) ? (string) $tweet['text'] : $tweet['full_text'];
		}
		$text = isset($tweet['text']) ? (string) $tweet['text'] : (string) $tweet['full_text'];
		$ChAr = $this->getChAr($text);
		$entities = $tweet['entities'];
		if (isset($entities['user_mentions']))
			foreach ($entities['user_mentions'] as $entity) {
				$ChAr[$entity['indices'][0]] = "<a href='https://twitter.com/" . $entity['screen_name'] . "'>" . $ChAr[$entity['indices'][0]];
				$ChAr[$entity['indices'][1] - 1] .= "</a>";
			}
		if (isset($entities['hashtags']))
			foreach ($entities['hashtags'] as $entity) {
				$ChAr[$entity['indices'][0]] = "<a href='https://twitter.com/search?q=%23" . $entity['text'] . "'>" . $ChAr[$entity['indices'][0]];
				$ChAr[$entity['indices'][1] - 1] .= "</a>";
			}
		if (isset($entities['urls']))
			foreach ($entities['urls'] as $entity) {
				$ChAr[$entity['indices'][0]] = "<a href='" . $entity['expanded_url'] . "'>" . $entity['display_url'] . "</a>";
				for ($i = $entity['indices'][0] + 1; $i < $entity['indices'][1]; $i++) $ChAr[$i] = '';
			}
		if (isset($entities['media']))
			foreach ($entities['media'] as $entity) {
				$ChAr[$entity['indices'][0]] = "<a href='" . $entity['expanded_url'] . "'>";
				if ($entity['type'] == 'photo') {
					$sizes = $entity['sizes']['small'];
					$this->image = $this->createImage($entity['media_url_https'], $sizes['w'],$sizes['h']);
					$ChAr[$entity['indices'][0]] .= "<img src='" . $entity['media_url_https'] . "' style='width:%WIDTH%px;height:%HEIGHT%px'/>";
					$sizes = $entity['sizes']['large'];//medium or large ???
					$this->media = $this->createMedia($entity['media_url_https'], $sizes['w'],$sizes['h'], 'image', true);
				} else {
					$ChAr[$entity['indices'][0]] .= $entity['display_url'];
				}
				$ChAr[$entity['indices'][0]] .= "</a>";
				for ($i = $entity['indices'][0] + 1; $i < $entity['indices'][1]; $i++) $ChAr[$i] = '';
			}
		return implode('', $ChAr);
	}

	private function getMedia($tweet){
		$entities = null;
		if (isset($tweet['retweeted_status']['extended_entities'])) {
			if (is_null($this->image)) {
				$entities = $tweet['retweeted_status']['entities'];
				if (isset($entities['media'][0])){
					$sizes = $entities['media'][0]['sizes']['small'];
					$this->image = $this->createImage($entities['media'][0]['media_url_https'], $sizes['w'],$sizes['h']);
				}
			}
			$entities = $tweet['retweeted_status']['extended_entities'];
		}
		if (isset($tweet['entities']['media'])){
			if (isset($tweet['entities']['media'][0])){
				$entity = $tweet['entities']['media'][0];
				$sizes = $entity['sizes']['small'];
				$this->image = $this->createImage($entity['media_url_https'], $sizes['w'],$sizes['h']);
			}
		}
		if (isset($tweet['extended_entities'])){
			$entities = $tweet['extended_entities'];
		}

		if (!is_null($entities)){
			if (isset($entities['media']))
			foreach ($entities['media'] as $entity) {
				if (isset($entity['video_info']['variants']) && sizeof($entity['video_info']['variants']) > 0) {
					if ($entity['type'] == 'video' || $entity['type'] == 'animated_gif') {
						foreach ( $entity['video_info']['variants'] as $variant ) {
							if ($variant['content_type'] == 'video/mp4'){
								$width = $entity['sizes']['large']['w'];
								$height = $entity['sizes']['large']['h'];
								if ($width > 600 || FF_FORCE_FIT_MEDIA) {
									$height = FFFeedUtils::getScaleHeight(600, $width, $height);
									$width = 600;
								}
								$this->media = $this->createMedia($variant['url'], $width, $height, $variant['content_type']);
							}
						}
					}
				}else{
					$width = $entity['sizes']['large']['w'];
					$height = $entity['sizes']['large']['h'];
					if ($width > 600 || FF_FORCE_FIT_MEDIA) {
						$height = FFFeedUtils::getScaleHeight(600, $width, $height);
						$width = 600;
					}
					$this->carousel[] = $this->createMedia($entity['media_url_https'], $width, $height, $entity['type'] == 'photo' ? 'image' : $entity['type']);
				}
			}
		}
		return $this->media;
	}
}