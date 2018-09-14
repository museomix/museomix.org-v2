<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */

class FFSoundcloud extends FFHttpRequestFeed{
	private $client_id;

	function __construct() {
		parent::__construct( 'soundcloud' );
	}

	protected function deferredInit( $feed ) {
		$this->client_id = $feed->soundcloud_api_key;
		$playlistUri = $this->getPlaylistUri($feed->username, $feed->content);
		$this->url = $playlistUri . ".json?client_id={$this->client_id}&limit={$this->getCount()}";
	}

	protected function items( $request ) {
		if (!empty($this->client_id)) {
			$pxml = json_decode($request);
			if (isset($pxml->tracks)) {
				return $pxml->tracks;
			}
		}
		return array();
	}

	protected function getId( $item ) {
		return $item->id;
	}

	protected function getHeader( $item ) {
		return $item->title;
	}

	protected function getScreenName( $item ) {
		return $item->user->username;
	}

	protected function getProfileImage( $item ) {
		return $item->user->avatar_url;
	}

	protected function getSystemDate( $item ) {
		return strtotime($item->created_at);
	}

	protected function getContent( $item ) {
		return $item->description;
	}

	protected function getUserlink( $item ) {
		return $item->user->permalink_url;
	}

	protected function getPermalink( $item ) {
		return $item->permalink_url;
	}

	protected function showImage( $item ) {
		return true;
	}

	protected function getImage( $item ) {
		if (isset($item->artwork_url) && !empty($item->artwork_url)){
			$img_url = str_replace('-large.', '-t500x500.', $item->artwork_url);
			return $this->createImage($img_url, 500, 500);
		}
		return '';
	}

	protected function getMedia( $item ) {
		$params = array(
			'url' => $item->uri,
			'color' => '015C8C',
			'auto_play' => 'false', //Whether to start playing the widget after it’s loaded
			'show_artwork' => 'true', //Show/hide artwork
			'buying' => 'false', //Show/hide buy buttons
			'download' => 'false', //Show/hide download buttons
			'sharing' => 'false', //Show/hide share buttons/dialogues
			'liking' => 'true', //Show/hide like buttons
			'show_comments' => 'true', //Show/hide comments
			'show_playcount' => 'true', //Show/hide number of sound plays
			'show_user' => 'true', //Show/hide the uploader name
			'start_track' => '0' // Preselects a track in the playlist, given a number between 0 and the length of the playlist
		);
		$src = 'https://w.soundcloud.com/player/?' . http_build_query($params, '', '&amp;');
		return $this->createMedia($src, 600, 200, 'sound');
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo($item);
		$additional['views'] = (string)@$item->playback_count;
		$additional['comments']= (string)@$item->comment_count;
		$additional['likes'] = (string)@$item->favoritings_count;
		$additional['downloads'] = (string)@$item->download_count;
		return $additional;
	}

	private function getPlaylistUri( $userName, $playlistName ) {
		$url  = "https://api.soundcloud.com/users/{$userName}/playlists.json?client_id={$this->client_id}";
		$data = $this->getFeedData($url);
		$data = json_decode($data['response']);
		if (!$data) return false;
		foreach ( $data as $playlist ) {
			if (isset($playlist->permalink) && $playlist->permalink == $playlistName) {
				return $playlist->uri;
			}
		}
		return false;
	}
}