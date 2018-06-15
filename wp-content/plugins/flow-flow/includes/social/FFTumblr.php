<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;

use flow\settings\FFSettingsUtils;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 *
 * https://www.tumblr.com/docs/en/api/v1
 */
class FFTumblr extends FFHttpRequestFeed{
	private $blog_name;
	private $media;
	private $isRichText;

	public function __construct() {
		parent::__construct( 'tumblr' );
	}

	public function deferredInit( $options, $feed) {
		$num = $this->getCount();
		$this->blog_name = (string) $feed->content;
		$this->isRichText = FFSettingsUtils::YepNope2ClassicStyle($feed->{'rich-text'});
		$this->url = "http://{$this->blog_name}.tumblr.com/api/read/json?debug=1&num={$num}&type=photo";
	}

	protected function getUrl() {
		return $this->url;
	}

	protected function items( $response ) {
		$response = trim(preg_replace('/^var tumblr_api_read = /', '', trim($response)), ';');
		$pxml = json_decode($response);
		return $pxml->posts;
	}

	protected function prepare( $item ) {
		$this->media = null;
		return parent::prepare( $item );
	}


	protected function getId( $item ) {
		return $item->id;
	}

	protected function getHeader( $item ) {
		return '';
	}

	protected function getScreenName( $item ) {
		return $this->blog_name;
	}

	protected function getProfileImage( $item ) {
		return "http://api.tumblr.com/v2/blog/{$this->blog_name}.tumblr.com/avatar/64";
	}

	protected function getSystemDate( $item ) {
		$date = date("Y-m-d\TH:i:s\Z",$item->{'unix-timestamp'});
		return strtotime($date);
	}

	protected function getContent( $item ) {
		$text = '';
		switch ($item->type) {
//			case 'answer':
//				$text = 'question: ' . $item->question . '<br/>answer: ' . $item->answer . '<br/>';
//				break;
//			case 'audio':
//				$text .= $item->{'audio-caption'} . '<br/>';
//  			id3-artist
//  		    id3-album
//  		    id3-title
//				$this->media = $this->createMedia($item->{'audio-embed'}, null, null, 'html');
//				break;
//			case 'video':
//				http://stackoverflow.com/questions/17481898/video-posts-with-auto-thumbnaillike-in-facebook-in-tumblr-com/17544279#17544279
//              break;
			case 'photo':
				$exclude = array("(previous)", "(next)", "(more)", "(via)");
				$text .= $this->isRichText ? $item->{'photo-caption'} : str_replace($exclude, '', strip_tags($item->{'photo-caption'}));
				break;
		}
		if (isset($item->tags)) $text .= ' ' . $this->wrapHashTag($item->tags);
		return $text;
	}

	protected function getUserlink( $item ) {
		return "http://{$this->blog_name}.tumblr.com";
	}

	protected function getPermalink( $item ) {
		return $item->url;
	}

	protected function showImage( $item ) {
		if ($item->type == 'photo'){
			//TODO Add support gallery
			$this->media = $this->createMedia($item->{'photo-url-500'}, $item->width, $item->height);
		}
		return true;
	}

	protected function getImage( $item ) {
		return $this->createImage($item->{'photo-url-250'}, $item->width, $item->height);
	}

	protected function getMedia( $item ) {
		return $this->media;
	}

	/**
	 * @param $tags
	 *
	 * @return mixed
	 */
	private function wrapHashTag($tags){
		$text = '';
		foreach($tags as $tag){
			//$tagEncode = str_replace(' ', '-', $tag);
			$tagEncode = urlencode($tag);
			$text = $text . "<a href=\"https://www.tumblr.com/tagged/{$tagEncode}\">#{$tag}</a> ";
		}
		return $text;
	}
}