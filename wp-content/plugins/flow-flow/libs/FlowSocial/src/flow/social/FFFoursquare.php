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
class FFFoursquare extends FFHttpRequestFeed{
	private $url_part;
	private $only_text;

	public function __construct() {
		parent::__construct( 'foursquare' );
	}

	public function deferredInit( $feed ) {
		$venue = $feed->content;
		$token = $feed->foursquare_access_token;
		$this->url_part = $feed->{'content-type'};
		$this->only_text = $feed->{'only-text'};
		if (empty($token)){
			$clientId = $feed->foursquare_client_id;
			$clientSecret = $feed->foursquare_client_secret;
			$this->url = "https://api.foursquare.com/v2/venues/{$venue}/{$this->url_part}?sort=recent&client_id={$clientId}&client_secret={$clientSecret}&v=20141210&limit={$this->getCount()}";
		}
		else {
			$this->url = "https://api.foursquare.com/v2/venues/{$venue}/{$this->url_part}?sort=recent&oauth_token={$token}&v=20141210&limit={$this->getCount()}";
		}
	}

	protected function items( $request ) {
		$pxml = json_decode($request);
		if ($this->only_text){
			$tmp = array();
			foreach ($pxml->response->{$this->url_part}->items as $item) {
				if (trim($item->text) != '') {
					$tmp[] = $item;
				}
			}
			return $tmp;
		}
		return $pxml->response->{$this->url_part}->items;
	}

	protected function getId( $item ) {
		return $item->id;
	}

	protected function getHeader( $item ) {
		return '';
	}

	protected function getScreenName( $item ) {
		$firstName = isset($item->user->firstName) ? $item->user->firstName : '';
		$lastName = isset($item->user->lastName) ? $item->user->lastName : '';
		return trim($firstName . ' ' . $lastName);
	}

	protected function getProfileImage( $item ) {
		return $item->user->photo->prefix . '256x256' . $item->user->photo->suffix;
	}

	protected function getSystemDate( $item ) {
		return $item->createdAt;
	}

	protected function getContent( $item ) {
		return (isset($item->text)) ? $item->text : '';
	}

	protected function getUserlink( $item ) {
		return 'https://foursquare.com/user/' . $item->user->id;
	}

	protected function getPermalink( $item ) {
		return isset($item->canonicalUrl) ? $item->canonicalUrl : '';
	}

	protected function showImage( $item ) {
		return ((!$this->only_text && isset($item->photo)) || $this->url_part == 'photos');
	}

	protected function getImage( $item ) {
		$width = $this->getImageWidth();
		$height = FFFeedUtils::getScaleHeight($width, $this->getOWidth($item), $this->getOHeight($item));
		$url = $this->getImageUrl($item, $width, $height);
		return $this->createImage($url, $width, $height);
	}

	protected function getMedia( $item ) {
		$width = $this->getOWidth($item);
		$height = $this->getOHeight($item);
		$url = $this->getImageUrl($item, $width, $height);
		return $this->createMedia($url, $width, $height);
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['saves']   = (string)@$item->todo->count;
		$additional['likes']   = (string)@$item->likes->count;
		return $additional;
	}

	private function getImageUrl($item, $width, $height){
		if ($this->url_part == 'photos'){
			return $item->prefix . "{$width}x{$height}" .$item->suffix;
		}
		return $item->photo->prefix . "{$width}x{$height}" .$item->photo->suffix;
	}

	private function getOWidth($item){
		return ($this->url_part == 'photos') ? $item->width : $item->photo->width;
	}

	private function getOHeight($item){
		return ($this->url_part == 'photos') ? $item->height : $item->photo->height;
	}
}