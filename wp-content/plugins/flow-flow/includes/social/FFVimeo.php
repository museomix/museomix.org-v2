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
class FFVimeo extends FFHttpRequestFeed{
	public function __construct() {
		parent::__construct( 'vimeo' );
	}

	public function deferredInit( $options, $feed ) {
		$content = (string)$feed->content;
		$type = (string)$feed->{'timeline-type'};
		switch ($type) {
			case 'likes':
			case 'videos':
				$this->url = "https://vimeo.com/api/v2/{$content}/{$type}.json";
				break;
			case 'album':
			case 'group':
			case 'channel':
			$this->url = "http://vimeo.com/api/v2/{$type}/{$content}/videos.json";
				break;
		}
	}

	protected function getUrl() {
		return $this->url;
	}

	protected function items( $request ) {
		$pxml = json_decode($request);
		return $pxml;
	}

	protected function getId( $item ) {
		return $item->id;
	}

	protected function getHeader( $item ) {
		return '';
	}

	protected function getScreenName( $item ) {
		return $item->{'user_name'};
	}

	protected function getProfileImage( $item ) {
		return $item->{'user_portrait_medium'};
	}

	protected function getSystemDate( $item ) {
		$date = $item->{'upload_date'};
		$date = str_replace(' ', 'T', $date);
		return strtotime($date);
	}

	protected function getContent( $item ) {
		return strip_tags($item->title);
	}

	protected function getUserlink( $item ) {
		return $item->{'user_url'};
	}

	protected function getPermalink( $item ) {
		return $item->url;
	}

	protected function showImage( $item ) {
		return true;
	}

	protected function getImage( $item ) {
		return $this->createImage($item->{'thumbnail_large'}, $item->width, $item->height);
	}

	protected function getMedia( $item ) {
		return $this->createMedia('http://player.vimeo.com/video/'.$item->id, 600, FFFeedUtils::getScaleHeight(600, $item->width, $item->height), 'vimeo');
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']      = (string)@$item->stats_number_of_likes;
		$additional['comments']   = (string)$item->stats_number_of_comments;
		$additional['views']      = (string)$item->stats_number_of_plays;
		return $additional;
	}
}