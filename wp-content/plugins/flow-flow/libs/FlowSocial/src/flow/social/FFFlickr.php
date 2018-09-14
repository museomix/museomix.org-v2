<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFFlickr extends FFRss {
	private static $authors = array();
	private $user_id;

	public function __construct() {
		parent::__construct( 'flickr' );
	}

	public function deferredInit( $feed ) {
		parent::deferredInit( $feed );

		$content = $feed->content;
		switch ($feed->{'timeline-type'}) {
			case 'user_timeline':
				$this->user_id = $content;
				$this->prepareAuthorData($content);
				break;
			case 'tag':
				$this->url = "https://api.flickr.com/services/feeds/photos_public.gne?tags={$content}&format=rss_200";
//				$num = $this->getCount();
//				$tags = str_replace(',', '+', $content);
//				$this->url = "http://www.degraeve.com/flickr-rss/rss.php?tags={$tags}brasilia+architecture&tagmode=all&sort=relevance&num={$num}";
				break;
		}
	}

	protected function prepare($item) {
		$tm = parent::prepare($item);
		$this->prepareAuthorData($this->getNickname($item));
		$this->prepareMediaData($item);
		return $tm;
	}

	protected function getHeader( $item ) {
		return '';
	}

	protected function getUserlink( $item ) {
		$id = $this->getNickname($item);
		return "https://www.flickr.com/photos/{$id}/";
	}

	protected function getContent( $item ) {
		$text = FFFeedUtils::wrapLinks(strip_tags($item->title));
		if ($text == 'Untitled') return '';
		return $text;
	}

	protected function showImage( $item ) {
		return true;
	}

	protected function isSuitablePost($post){
		if (true === parent::isSuitablePost($post)){
			if (strpos($post->img['url'], '.swf') > 0){
				return false;
			}
			return true;
		}
		return false;
	}
	
	private function prepareAuthorData($user_name){
		if (array_key_exists($user_name, self::$authors)){
			$this->profileImage = self::$authors[$user_name][0];
			$this->screenName = self::$authors[$user_name][1];
			$this->url = self::$authors[$user_name][2];
		}
		else {
			$content = $this->getFeedData("https://www.flickr.com/photos/{$user_name}/");
			libxml_use_internal_errors(true);
			$doc = new \DOMDocument();
			@$doc->loadHTML($content['response']);
			$finder = new \DOMXPath($doc);
			$result = $finder->query('//div[contains(@class, "avatar")]/@style');
			preg_match_all('/background(-image)??\s*?:.*?url\(["|\']??(.+)["|\']??\)/', $result->item( 0 )->textContent, $matches, PREG_SET_ORDER);
			$avatar_url = 'http:' . $matches[0][2];
			$this->profileImage = ($result->length > 0) ? $avatar_url : $this->context['plugin_url'] . '/' . $this->context['slug'] . '/assets/avatar_default.png';

			$result = $finder->query("//meta[@name = 'title']/@content");
			$this->screenName = ($result->length > 0) ? trim(strip_tags($result->item( 0 )->textContent)) : $user_name;

			$id = substr($avatar_url, strpos($avatar_url, '#') + 1);
			$this->url = "https://api.flickr.com/services/feeds/photos_public.gne?id={$id}&format=rss_200";
			self::$authors[$user_name] = array($this->profileImage, $this->screenName, $this->url);
		}
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return void
	 */
	private function prepareMediaData( $item ) {
		$media = $item->children('media', true);
		foreach($media->content as $thumbnail) {
			$attributes = $thumbnail[0]->attributes();
			$url = (string)$attributes->url;
			$height = (string)$attributes->height;
			$width = (string)$attributes->width;
		}
		$this->image = $this->createImage($url, $width, $height);
		$this->media = $this->createMedia($url, $width, $height);
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	private function getNickname($item){
		if (isset($this->user_id))
			return $this->user_id;
		$result = explode('/', $item->author->attributes('flickr', true)->profile);
		return $result[4];
	}
}
