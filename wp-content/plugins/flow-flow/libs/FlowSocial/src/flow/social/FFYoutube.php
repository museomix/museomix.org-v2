<?php namespace flow\social;
use SimpleXMLElement;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFYoutube extends FFHttpRequestFeed implements LAFeedWithComments {
	private $profile = null;
	private $profiles = array();
	private $userlink = null;
	private $apiKeyPart = '';
	private $image;
	private $media;
	private $videoId;
	private $isSearch = false;
	private $isPlaylist = false;
	private $statistics;
	private $pagination = true;
	private $nextPageToken = '';
	private $pageIndex = 0;
	private $order = false;

	public function __construct() {
		parent::__construct( 'youtube' );
	}

	public function deferredInit($feed) {
		$this->apiKeyPart = '&key=' . $feed->google_api_key;

		if (isset($feed->{'timeline-type'})) {
			$content = urlencode($feed->content);
			switch ( $feed->{'timeline-type'} ) {
				case 'user_timeline':
					$this->userlink = "https://www.youtube.com/user/{$content}";
					$profileUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet%2CcontentDetails&forUsername={$content}" . $this->apiKeyPart;
					$this->profile = $this->getProfile($profileUrl, $content);
					$this->url = "https://www.googleapis.com/youtube/v3/playlistItems?part=id%2Csnippet&playlistId={$this->profile->uploads}&maxResults=50" . $this->apiKeyPart;
					break;
				case 'channel':
					$this->profile = $this->getProfile4Search($content);
					$this->userlink = $this->getUserlink4Search($content);
					$this->url = "https://www.googleapis.com/youtube/v3/playlistItems?part=id%2Csnippet&playlistId={$this->profile->uploads}&maxResults=50" . $this->apiKeyPart;
					break;
				case 'playlist':
					$this->isSearch = true;
					$this->isPlaylist = true;
					$this->url = "https://www.googleapis.com/youtube/v3/playlistItems?part=id%2Csnippet&playlistId={$content}&maxResults=50" . $this->apiKeyPart;
					$this->order = $feed->{'playlist-order'};
					break;
				case 'search':
					$this->isSearch = true;
					$this->url = "https://www.googleapis.com/youtube/v3/search?part=id%2Csnippet&q={$content}&type=video&order=date&maxResults=50" . $this->apiKeyPart;
					break;
			}
		}
	}

	protected function getUrl() {
		return parent::getUrl() . $this->nextPageToken;
	}

	protected function items($request){
		$items = array();
		$pxml = json_decode($request);
		if ($this->isSuitablePage($pxml)) {
			$videoResults = array();
			$this->statistics = array();
			foreach ($pxml->items as $item) {
				if ((!isset($item->id->videoId) && !isset($item->snippet->resourceId->videoId)) || !isset($item->snippet->thumbnails)) {
					continue;//TODO fix this case
				}
				$videoId = is_object($item->id) ? $item->id->videoId : $item->snippet->resourceId->videoId;
				array_push($videoResults, $videoId);
			}
			$videoIds = join('%2C', $videoResults);
			$url = "https://www.googleapis.com/youtube/v3/videos?part=id%2Cstatistics&id={$videoIds}" . $this->apiKeyPart;
			$data = $this->getFeedData($url);
			if ( sizeof( $data['errors'] ) > 0 ) {
				$this->errors[] = array(
					'type'    => $this->getType(),
					'message' => $this->filterErrorMessage($data['errors']),
					'url' => $url
				);
			}
			else {
				$statistics = json_decode($data['response']);
				foreach ( $statistics->items as $stat ) {
					$this->statistics[$stat->id] = $stat->statistics;
				}
			}
			$items = $pxml->items;
		}
		$this->pageIndex++;
		return $items;
	}

	protected function isSuitableOriginalPost( $post ) {
		if ((!isset($post->id->videoId) && !isset($post->snippet->resourceId->videoId)) || !isset($post->snippet->thumbnails)) {
			return false;//TODO fix this case
		}
		return parent::isSuitableOriginalPost( $post );
	}

	protected function prepare( $item ) {
		$this->image = null;
		$this->media = null;

		$this->videoId = is_object($item->id) ? $item->id->videoId : $item->snippet->resourceId->videoId;
		if ($this->isSearch) {
			$channelId      = $item->snippet->channelId;
			$this->userlink = $this->getUserlink4Search( $channelId );
			$this->profile  = $this->getProfile4Search( $channelId );
		}
		return parent::prepare( $item );
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getId( $item ) {
		return $this->videoId;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getScreenName($item){
		return $this->profile->nickname;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getHeader($item){
		return FFFeedUtils::wrapLinks(strip_tags((string)$item->snippet->title));
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getContent( $item ) {
		return FFFeedUtils::wrapLinks(strip_tags( (string) $item->snippet->description ) );
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return bool
	 */
	protected function showImage($item){
		$thumbnail = null;
		$thumbnails = $item->snippet->thumbnails;
		if (property_exists($thumbnails, 'maxres')){
			$thumbnail = $this->isSuitableThumbnail( $thumbnails->maxres);
		}

		if (is_null($this->image) && isset( $thumbnails->standard)) {
			$thumbnail = $this->isSuitableThumbnail( $thumbnails->standard, $thumbnail);
		}

		if (is_null($this->image) && isset( $thumbnails->high)) {
			$thumbnail = $this->isSuitableThumbnail( $thumbnails->high, $thumbnail);
		}

		if (is_null($this->image) && isset( $thumbnails->medium)) {
			$thumbnail = $this->isSuitableThumbnail( $thumbnails->medium, $thumbnail);
		}

		if (is_null($this->image) && isset( $thumbnails->default)) {
			$thumbnail = $this->isSuitableThumbnail( $thumbnails->default, $thumbnail);
		}

		if (is_null($this->image)){
			$this->image = $this->createImage($thumbnail->url, $thumbnail->width, $thumbnail->height);
		}

		$height = FFFeedUtils::getScaleHeight(600, $thumbnail->width, $thumbnail->height);
		$this->media = $this->createMedia("http://www.youtube.com/v/{$this->videoId}?version=3&f=videos&autoplay=0", 600, $height, "application/x-shockwave-flash");

		return true;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return array
	 */
	protected function getImage( $item ) {
		return $this->image;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return array
	 */
	protected function getMedia( $item ) {
		return $this->media;
	}

	/**
	 * @param SimpleXMLElement $item
	 * @return string
	 */
	protected function getProfileImage( $item ) {
		return $this->profile->profileImage;
	}

	protected function getSystemDate( $item ) {
		return strtotime($item->snippet->publishedAt);
	}

	protected function getUserlink( $item ) {
		return $this->userlink;
	}

	protected function getPermalink( $item ) {
		return "https://www.youtube.com/watch?v={$this->videoId}";
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		if (array_key_exists($this->videoId, $this->statistics)){
			$stat = $this->statistics[$this->videoId];
			$additional['views']      = (string)@$stat->viewCount;
			$additional['likes']      = (string)@$stat->likeCount;
			$additional['dislikes']   = (string)@$stat->dislikeCount;
			$additional['comments']   = (string)@$stat->commentCount;
		}
		return $additional;
	}

	protected function nextPage( $result ) {
		return $this->pagination;
	}

	private function getProfile4Search($channelId){
		if (!array_key_exists($channelId, $this->profiles)){
			$profileUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet%2CcontentDetails&id={$channelId}" . $this->apiKeyPart;
			$profile = $this->getProfile($profileUrl);
			$this->profiles[$channelId] = $profile;
			return $profile;
		}
		return $this->profiles[$channelId];
	}

	private function getProfile($profileUrl){
		$profile = new \stdClass();
		$data = $this->getFeedData($profileUrl);
		if ( sizeof( $data['errors'] ) > 0 ) {
			$this->errors[] = array(
				'type'    => $this->getType(),
				'message' => $this->filterErrorMessage($data['errors']),
				'url' => $profileUrl
			);
			throw new \Exception();
		}
		$pxml = json_decode($data['response']);
		$item = $pxml->items[0];
		$profile->nickname = $item->snippet->title;
		$profile->profileImage = $item->snippet->thumbnails->high->url;
		$profile->uploads = $item->contentDetails->relatedPlaylists->uploads;
		return $profile;
	}

	private function getUserlink4Search($channelId){
		return "https://www.youtube.com/channel/{$channelId}";
	}

	private function isSuitablePage($pxml){
		$needCountPage = ceil($this->getCount() / 50);
		if ($this->isPlaylist && $this->order){
			$totalResult = intval($pxml->pageInfo->totalResults);
			$countPage = ceil($totalResult / 50);
			$additionalPage = fmod($totalResult, 50) > fmod($this->getCount(),50) ? 0 : 1;
			if (fmod($this->getCount(),50) == 0) $additionalPage = 1;
			$needCountPage = $needCountPage + $additionalPage;
			$isSuitablePage = $this->pageIndex >= ($countPage - $needCountPage);
		}
		else {
			$this->pagination = $needCountPage > $this->pageIndex + 1;
			$isSuitablePage = $needCountPage > $this->pageIndex;
		}

		if (isset($pxml->nextPageToken))
			$this->nextPageToken = "&pageToken=" . $pxml->nextPageToken;
		else
			$this->pagination = false;

		return $isSuitablePage && isset($pxml->items);
	}

	private function isSuitableThumbnail($thumbnail, $current_thumbnail = null){
		if ( round( $thumbnail->width / $thumbnail->height, 2) == 1.78) {
			$this->image = $this->createImage($thumbnail->url, $thumbnail->width, $thumbnail->height);
		}
		if (is_null($current_thumbnail)){
			return $thumbnail;
		}
		return $current_thumbnail;
	}
	
	public function getComments($item) {
		if (is_object($item)){
			return array();
		}

		$objectId = $item;
		$accessToken = $this->feed->google_api_key;
		$url = "https://www.googleapis.com/youtube/v3/commentThreads?videoId={$objectId}&maxResults={$this->getCount()}&part=snippet&key={$accessToken}";
		$request = $this->getFeedData($url);
		$json = json_decode($request['response']);

		if (!is_object($json) || (is_object($json) && sizeof($json->items) == 0)) {
			if (isset($request['errors']) && is_array($request['errors'])){
				if (!empty($request['errors'])){
					foreach ( $request['errors'] as $error ) {
						$error['type'] = 'youtube';
						//TODO $this->filterErrorMessage
						$this->errors[] = $error;
						throw new \Exception();
					}
				}
			}
			else {
				$this->errors[] = array('type'=>'youtube', 'message' => 'Bad request, access token issue. <a href="http://docs.social-streams.com/article/55-400-bad-request" target="_blank">Troubleshooting</a>.', 'url' => $url);
				throw new \Exception();
			}
			return array();
		}
		else {
			if($json->items){
				// return first 5 comments
				$data = array_slice($json->items, 0, 5);
				$result = array();
				foreach ($data as $item){
					$obj = new \stdClass();
					$obj->id = $item->snippet->topLevelComment->id;
					$obj->from = array(
						'id' => $item->snippet->topLevelComment->snippet->authorChannelId->value,
						'full_name' => $item->snippet->topLevelComment->snippet->authorDisplayName
					);
					$obj->text = $item->snippet->topLevelComment->snippet->textDisplay;
					$obj->created_time = $item->snippet->topLevelComment->snippet->publishedAt;
					$result[] = $obj;
				}
				return $result;
			}else{
				$this->errors[] = array(
					'type' => 'instagram',
					'message' => 'User not found',
					'url' => $url
				);
				throw new \Exception();
			}
		}
	}
}