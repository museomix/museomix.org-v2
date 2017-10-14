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
class FFDribbble extends FFHttpRequestFeed{
	private $page = 1;
	private $template_url;
	private $size = 0;
	private $player;

	public function __construct() {
		parent::__construct( 'dribbble' );
	}

	public function deferredInit( $options, $feed ) {
		$original = $options->original();
		$token = $original['dribbble_access_token'];
		$username = $feed->content;
		$partOfUrl = ($feed->{'timeline-type'} == 'liked') ? 'likes' : 'shots';
		$this->template_url = "https://api.dribbble.com/v1/users/{$username}/{$partOfUrl}?access_token={$token}&sort=recent&page=";
		$this->url = $this->template_url . $this->page;

		$data = $this->getFeedData("https://api.dribbble.com/v1/users/{$username}?access_token={$token}");
		$this->player = json_decode($data['response']);
	}

	protected function items( $request ) {
		$pxml = json_decode($request);
		return $pxml;
	}

	protected function prepareItem( $item ) {
		if (isset($item->shot)){
			return $item->shot;
		}
		$item->user = $this->player;
		return $item;
	}

	protected function getId( $item ) {
		return $item->id;
	}

	protected function getHeader( $item ) {
		return $item->title;
	}

	protected function getScreenName( $item ) {
		return $item->user->name;
	}

	protected function getProfileImage( $item ) {
		return $item->user->avatar_url;
	}

	protected function getSystemDate( $item ) {
		return strtotime($item->created_at);
	}

	protected function getContent( $item ) {
		return $this->dribbble($item->description);
	}

	protected function getUserlink( $item ) {
		return $item->user->html_url;
	}

	protected function getPermalink( $item ) {
		return $item->html_url;
	}

	protected function showImage( $item ) {
		return true;
	}

	protected function getImage( $item ) {
		$width = $this->getImageWidth();
		$height = FFFeedUtils::getScaleHeight($width, $item->width, $item->height);
		$url = $item->images->normal;
		return $this->createImage($url, $width, $height);
	}

	protected function getMedia( $item ) {
		return $this->createMedia($item->images->hidpi, $item->width, $item->height);
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['views'] = (string)@$item->views_count;
		$additional['likes'] = (string)@$item->likes_count;
		$additional['comments'] = (string)@$item->comments_count;
		$additional['shares'] = (string)@$item->rebounds_count;
		return $additional;
	}

	protected function customize( $post, $item ) {
		$post->nickname = $item->user->username;
		return parent::customize( $post, $item );
	}

	protected function nextPage( $result ) {
		$size = sizeof($result);
		if ($size == $this->size) {
			return false;
		}
		else {
			$this->size = $size;
			$this->page = $this->page + 1;
			$this->url = $this->template_url . $this->page;
			return $this->getCount() >= $size;
		}
	}

	private function dribbble($text){
		$text = FFFeedUtils::wrapLinks(strip_tags($text));
		$matches = array();
		@preg_match_all("(@\[(\d*):([^\]]+)\])", $text, $matches);
		if (!empty($matches)){
			$count = sizeof($matches[0]);
			for ( $i = 0; $count > $i; $i ++ ) {
				$id = $matches[1][$i];
				$name = $matches[2][$i];
				$url = "<a href='https://dribbble.com/{$id}'>@{$name}</a>";
				$text = str_replace($matches[0][$i], $url, $text);
			}
		}
		return $text;
	}
}