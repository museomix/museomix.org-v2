<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;

use \SimpleXMLElement;
use flow\settings\FFSettingsUtils;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFRss extends FFHttpRequestFeed {
	protected $image;
	protected $media;
	protected $userLink;
	protected $screenName;
	protected $profileImage;
	/** @var bool */
	private $isRichText = false;
	/** @var bool */
	private $hideCaption = false;
	/** @var SimpleXMLElement | null $xml */
	private $xml = null;
	/** @var  mixed $mrss */
	private $mrss = false;

	public function __construct( $type = null ) {
		if (is_null($type)) $type = 'rss';
		parent::__construct( $type );
	}

	public function deferredInit($options, $feed) {
		$this->url = $feed->content;
		$this->isRichText = isset($feed->{'rich-text'}) ? FFSettingsUtils::YepNope2ClassicStyle($feed->{'rich-text'}) : false;
		$this->hideCaption = isset($feed->{'hide-caption'}) ? FFSettingsUtils::YepNope2ClassicStyle($feed->{'hide-caption'}) : false;
		if (isset($feed->{'channel-name'})) $this->screenName = $feed->{'channel-name'};
		$this->profileImage = isset($feed->{'avatar-url'}) && trim($feed->{'avatar-url'}) != ''?
			$feed->{'avatar-url'} : $this->context['plugin_url'] . '/' . $this->context['slug'] . '/assets/avatar_default_rss.png';
	}

	protected function getUrl(){
		return $this->url;
	}

	protected function items($request){
		libxml_use_internal_errors(true);
		$pxml = new SimpleXMLElement($request);
		$this->xml = $pxml;
		$this->mrss = false;
		foreach ( $pxml->getNamespaces(true) as $key => $url ) {
			if ($url === 'http://search.yahoo.com/mrss/'){
				$this->mrss = $key;
				break;
			}
		}
		$result = array();
		if ($pxml && isset($pxml->channel)) {
			if (!isset($this->screenName) || strlen($this->screenName) == 0) {
				$this->screenName = (string)$pxml->channel->title;
			}
			if (isset($pxml->channel->link)){
				$this->userLink = $pxml->channel->link;
			}
			if (sizeof($pxml->channel->item) > $this->getCount())
				for ($i=0; $i < $this->getCount(); $i++)  $result[] = $pxml->channel->item[$i];
			else
				$result = $pxml->channel->item;
		}
		libxml_clear_errors();
		libxml_use_internal_errors(false);

		return $result;
	}

	protected function prepare( $item ) {
		$this->image = null;
		$this->media = null;
		return parent::prepare( $item );
	}


	protected function getId($item){
        	return hash('md5', isset($item->guid) ? (string)$item->guid : (string) $item->link);
    	}

	protected function getScreenName($item){
		return $this->screenName;
	}

	protected function getProfileImage($item){
		return $this->profileImage;
	}

	protected function getSystemDate($item){
		if (isset($item->pubDate)) return strtotime($item->pubDate);
		$d = new \DateTime(); return $d->getTimestamp();
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getContent($item){
		if ($this->isRichText){
			$content = $item->children('content', true);
			foreach ($content->encoded as $encoded) {
				$content = $this->getRichText((string)$encoded);
				if (trim($content) != ''){
					return $content;
				}
			}
			return $this->getRichText((string)$item->description);
		}
		return FFFeedUtils::wrapLinks(strip_tags((string)$item->description));
	}

	protected function getHeader($item){
		return $this->hideCaption ? '' : $item->title;
	}

	protected function getUserlink($item){
		return $this->userLink;
	}

	protected function getPermalink($item){
		return $item->link;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return bool
	 */
	protected function showImage($item){
		if (isset($item->enclosure) && 'image/jpeg' == (string)$item->enclosure['type']){
			$this->image = $this->createImage((string)$item->enclosure['url']);
			$this->media = $this->createMedia($this->image['url'], $this->image['width'], $this->image['height']);
			return true;
		}
		else if ($this->mrss !== false){
			//$this->xml->channel->item[2]->children($namespaces['media'])->content->attributes()->height
			$namespaces = $this->xml->getNamespaces(true);
			$imageNode = isset($item->children($namespaces[$this->mrss])->thumbnail) ? $item->children($namespaces[$this->mrss])->thumbnail :
				(isset($item->children($namespaces[$this->mrss])->content) ? $item->children($namespaces[$this->mrss])->content : null);
			if ($imageNode) {
				/** @var SimpleXMLElement $attributes */
				$attributes = $imageNode->attributes();
				if (!is_null($attributes) && isset($attributes->url)){
					$url = (string) $attributes->url;
					$url = (string) $attributes->url;
					if (isset($attributes->height) && isset($attributes->width)){
						$height = (string) $attributes->height;
						$width = (string) $attributes->width;
						$this->image = $this->createImage($url, $width, $height);
					}
					else
						$this->image = $this->createImage($url);
					return true;
				}
			}
		}
		return false;
	}

	protected function getImage($item){
		return $this->image;
	}

	protected function getMedia( $item ) {
		return $this->media;
	}

	private function getRichText($text){
		$text = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $text);
		$text = preg_replace('/(<[^>]+) class=".*?"/i', '$1', $text);
		if (FF_USE_WP) $text = strip_shortcodes( $text );
		try {
			libxml_use_internal_errors(true);
			$doc = new \DOMDocument();
			$doc->encoding = 'utf-8';
			$text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
			if (!empty($text) && $doc->loadHTML($text)){
				//$forRemove = array();
				//$images = $doc->getElementsByTagName('img');
				/*foreach ($images as $image){
					$objImage = ($image->hasAttribute('width') && $image->hasAttribute('height')) ?
						$this->createImage($image->getAttribute('src'), $image->getAttribute('width'), $image->getAttribute('height'), false) :
						$this->createImage($image->getAttribute('src'), null, null, false);
					if ($objImage['width'] > $this->getImageWidth()){
						$height = FFFeedUtils::getScaleHeight($this->getImageWidth(), $objImage['width'], $objImage['height']);
						$image->setAttribute('width', $this->getImageWidth());
						$image->setAttribute('height', $height);
						continue;
					}
					$forRemove[] = $image;
				}*/

				while (($r = $doc->getElementsByTagName("script")) && $r->length) {
					$r->item(0)->parentNode->removeChild($r->item(0));
				}

				/*foreach ($forRemove as $image){
					$parent = $image->parentNode;
					$parent->removeChild($image);
					if (($parent->tagName == 'a') && $parent->childNodes->length == 0){
						$grandParent = $parent->parentNode;
						$grandParent->removeChild($parent);
					}
				}*/

				$text = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $doc->saveHTML()));
			}
		} catch (\Exception $e){
			$this->errors[] = array(
				'type'    => 'pinterest',
				'message' =>  $this->filterErrorMessage($e->getMessage())
			);
		}
		return $text;
	}
}
