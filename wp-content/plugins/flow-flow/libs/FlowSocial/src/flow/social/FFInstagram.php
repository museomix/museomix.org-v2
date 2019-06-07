<?php namespace flow\social;
use InstagramScraper\Endpoints;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Media;
use Unirest\Request;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFInstagram extends FFBaseFeed implements LAFeedWithComments{
    private $url;
	private $size = 0;
	private $comments = null;
	private $userMeta = null;
	private $pagination = true;
	private $alternative = false;
	private $timeline;
	private $accounts = [];

	public function __construct() {
		parent::__construct( 'instagram' );
	}

	public function init( $context, $feed ) {
		parent::init( $context, $feed );
//		require_once $context['root'] . 'libs/InstagramScraper.php';
//		require_once $context['root'] . 'libs/Unirest.php';
//		require_once $context['root'] . 'libs/phpFastCache.php';
		Request::verifyPeer(false);
		Request::verifyHost(false);
		Request::curlOpt( CURLOPT_IPRESOLVE, $feed->use_ipv4 ? CURL_IPRESOLVE_V4 : CURL_IPRESOLVE_V6);
		Request::curlOpt( CURLOPT_FOLLOWLOCATION, true);
	}

	public function getCount() {
		return 50;
	}

	public function deferredInit($feed) {
		$accessToken = $feed->instagram_access_token;
		$this->url = "https://api.instagram.com/v1/users/self/media/recent/?access_token={$accessToken}&count={$this->getCount()}&hl=en";
		if (isset($feed->{'timeline-type'})) {
			$this->timeline = $feed->{'timeline-type'};
			switch ($this->timeline) {
				case 'user_timeline':
					$content = FFFeedUtils::preparePrefixContent($feed->content, '@');
					//$userId = $this->getUserId($content, $accessToken);
					//$this->userMeta = $this->getUserMeta($userId, $accessToken);
					//$this->url = "https://api.instagram.com/v1/users/self/media/recent/?access_token={$accessToken}&count={$this->getCount()}";
					if (empty($content)){
						$this->url = "https://api.instagram.com/v1/users/self/media/recent/?access_token={$accessToken}&count={$this->getCount()}&hl=en";
					}
					else {
						$this->url = $content;
						$this->alternative = true;
					}
					break;
				case 'tag':
					$tag = FFFeedUtils::preparePrefixContent($feed->content, '#');
//					$tag = urlencode($tag);
					$this->url = $tag;//"https://api.instagram.com/v1/tags/{$tag}/media/recent?access_token={$accessToken}&count={$this->getCount()}";
					$this->alternative = true;
					break;
				case 'location':
//					$locationID = $feed->content;
//					$this->url = "https://api.instagram.com/v1/locations/{$locationID}/media/recent?access_token={$accessToken}&count={$this->getCount()}";
					$this->alternative = true;
					break;
				case 'coordinates':
					$coordinates = explode(',', $feed->content);
					$lat = trim($coordinates[0]);
					$lng = trim($coordinates[1]);
					$this->url = "https://api.instagram.com/v1/media/search?lat={$lat}&lng={$lng}&access_token={$accessToken}&count={$this->getCount()}&hl=en";
					break;
			}
		}
	}

	public function onePagePosts() {
		$result = array();
		if ($this->alternative){
			$instagram = new Instagram();
			$instagram->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");
			$medias = [];
			$forced_loading_of_post = false;
			switch ($this->timeline){
				case 'user_timeline':
					$account = $this->getAccount($this->url);
					$this->userMeta = $this->fillUser($this->url);
					$account_id = $account->getId();
					$medias = $instagram->getMediasByUserId($account_id, $this->getCount());
					break;
				case 'tag':
					$medias = $instagram->getMediasByTag($this->url, $this->getCount());
					$forced_loading_of_post = true;
					break;
				case 'location':
					$locationID = $this->feed->content;
					$medias = $instagram->getMediasByLocationId($locationID, $this->getCount());
			}

			foreach ( $medias as $media ) {
				try {
					$post      = $instagram->getMediaByUrl( $media->getLink() );
					$post      = $this->altParsePost($post, $forced_loading_of_post);
					if(!empty($this->userMeta)){
						$post->userMeta = $this->userMeta;
					}
					if ($this->isSuitablePost($post)) $result[$post->id] = $post;
				} catch ( InstagramNotFoundException $e ) {
				}
			}
			$this->pagination = false;
		}
		else {
			try {
				$data = $this->getFeedData($this->url);
				if (isset($data['response']) && is_string($data['response'])){
					$response = $data['response'];
					//fix malformed
					//http://stackoverflow.com/questions/19981442/decoding-instagram-reply-php
					//In case of a problem, comment out this line
					$response = html_entity_decode($response);
					$page = json_decode($response);
					if (isset($page->pagination) && isset($page->pagination->next_url))
						$this->url = $page->pagination->next_url;
					else
						$this->pagination = false;
					foreach ($page->data as $item) {
						$post = $this->parsePost($item);

						if(!empty($this->userMeta)){
							$post->userMeta = $this->userMeta;
						}

						if ($this->isSuitablePost($post)) $result[$post->id] = $post;
					}
				} else {
					throw new LASocialException('FFInstagram has returned the empty data.', array('url' => $this->url));
				}
			} catch ( LASocialRequestException $sre ) {
				$this->print2log($sre->getMessage());
				$this->print2log($sre);
				$request_errors = $sre->getRequestErrors();
				$message = is_object($request_errors) ? 'Error getting data from instagram server' : $this->filterErrorMessage($request_errors);
				throw new LASocialException($message);
			}
		}
		return $result;
	}

	private function parsePost($post) {
		$tc = new \stdClass();
		$tc->feed_id = $this->id();
		$tc->smart_order = 0;
		$tc->id = (string)$post->id;
		$tc->header = '';
		$tc->type = $this->getType();
		$tc->nickname = (string)$post->user->username;
		if (!isset($post->user->full_name)) $post->user = $this->fillUser($post->user->username);
		$tc->screenname = FFFeedUtils::removeEmoji((string)$post->user->full_name);
		if (function_exists('mb_convert_encoding')){
			$tc->screenname = mb_convert_encoding($tc->screenname, 'HTML-ENTITIES', 'UTF-8');
		}
		else if (function_exists('iconv')){
			$tc->screenname = iconv('UTF-8', 'HTML-ENTITIES', $tc->screenname);
		}
		$tc->userpic = (string)$post->user->profile_picture;
		$tc->system_timestamp = $post->created_time;
		$tc->img = $this->createImage($post->images->low_resolution->url,
				$post->images->low_resolution->width, $post->images->low_resolution->height);
		$tc->text = isset($post->caption->text) ? $this->getCaption($post->caption->text) : '';
		$tc->userlink = 'http://instagram.com/' . $tc->nickname;
		$tc->permalink = (string)$post->link;
		$tc->location = $post->location;
        $tc->additional = array('likes' => (string)$post->likes->count, 'comments' => (string)$post->comments->count);
		
		$tc->carousel= array();
		if (isset($post->type) && $post->type == 'carousel' && isset($post->carousel_media)){
			$tc->carousel = $this->getCarousel($post);
			$tc->media = sizeof($tc->carousel) > 0 ? $tc->carousel[0] : $tc->img;
		}
		else {
			$tc->media = $this->getMediaContent($post);
		}
		return $tc;
	}

	/**
	 * @param Media $post
	 * @param bool $forced_loading_of_post
	 *
	 * @return \stdClass
	 */
	private function altParsePost($post, $forced_loading_of_post = false) {

//		if ($forced_loading_of_post || Media::TYPE_IMAGE != $post->getType()) {
//			$instagram = new Instagram();
//
//			try{
//				$post      = $instagram->getMediaById( $post->getId() );
//				$account = $post->getOwner();
//			}catch(\Exception $e){
//				error_log($e->getMessage());
//				error_log($e->getTraceAsString());
//			}
//		}
//		else {
//			$account = $this->getAccountById($post->getOwnerId());
//		}
//		$account = $post->getOwner();
		$account = $this->getAccount($post->getOwner()->getUsername());

		$tc = new \stdClass();
		$tc->feed_id = $this->id();
		$tc->smart_order = 0;
		$tc->id = $post->getId();
		$tc->type = $this->getType();
		$tc->header = '';
		$tc->nickname = $account->getUsername();
		$tc->screenname = FFFeedUtils::removeEmoji($account->getFullName());
		$tc->userpic = $account->getProfilePicUrlHd();
		$tc->system_timestamp = $post->getCreatedTime();
		$tc->carousel = [];

		$min_thumbnail = 1000;
		$max_thumbnail = 0;
		foreach ( $post->getThumbnailResources() as $width => $thumbnail ) {
			if ($min_thumbnail > $width && $width >= 200) $min_thumbnail = $width;
			if ($max_thumbnail < $width && $width <= 1000) $max_thumbnail = $width;
		}
		$min_thumbnail = $post->getThumbnailResources()[$min_thumbnail];
		$max_thumbnail = $post->getThumbnailResources()[$max_thumbnail];

		$tc->img = $this->createImage($min_thumbnail->url, $min_thumbnail->width, $min_thumbnail->height);

		if (Media::TYPE_VIDEO == $post->getType()){
			$tc->media = $this->createMedia($post->getVideoStandardResolutionUrl(), $max_thumbnail->width, $max_thumbnail->height, 'video/mp4', true);
		}
		else if (Media::TYPE_SIDECAR == $post->getType() && !empty($post->getSidecarMedias())){
			foreach ($post->getSidecarMedias()  as $item ) {
				$width = max(array_keys($item->getThumbnailResources()));
				$max_thumbnail = $item->getThumbnailResources()[$width];
				$tc->carousel[] = $this->createMedia($max_thumbnail->url, $max_thumbnail->width, $max_thumbnail->height, Media::TYPE_IMAGE == $item->getType() ? Media::TYPE_IMAGE : 'video/mp4', true);
			}
			$tc->media = $tc->carousel[0];
		}
		else if (Media::TYPE_CAROUSEL == $post->getType() && !empty($post->getCarouselMedia())){
			foreach ($post->getCarouselMedia()  as $item ) {
				$width = max(array_keys($item->getThumbnailResources()));
				$max_thumbnail = $item->getThumbnailResources()[$width];
				$tc->carousel[] = $this->createMedia($max_thumbnail->url, $max_thumbnail->width, $max_thumbnail->height, Media::TYPE_IMAGE == $item->getType() ? Media::TYPE_IMAGE : 'video/mp4', true);
			}
			$tc->media = $tc->carousel[0];
		}
		else {
			$tc->media = $this->createMedia($max_thumbnail->url, $max_thumbnail->width, $max_thumbnail->height, Media::TYPE_IMAGE, true);
		}
		$tc->text = $this->getCaption($post->getCaption());
		$tc->userlink = 'http://instagram.com/' . $tc->nickname;
		$tc->permalink = $post->getLink();
		$tc->location = empty($post->getLocation()) ? null : (object)$post->getLocation();
		$tc->additional = array('likes' => (string)$post->getLikesCount(), 'comments' => (string)$post->getCommentsCount());

		return $tc;
	}

	private function getCarousel($post){
		$carousel = array();
		foreach ($post->carousel_media as $item){
			$carousel[] = $this->getMediaContent($item);
		}
		return $carousel;
	}
	
	private function getMediaContent($item){
        if (isset($item->type) && $item->type == 'video' && isset($item->videos)){
			return array('type' => 'video/mp4', 'url' => $item->videos->standard_resolution->url,
				'width' => 600,
				'height' => FFFeedUtils::getScaleHeight(600, $item->videos->standard_resolution->width, $item->videos->standard_resolution->height));
		} else {
			return $this->createMedia($item->images->standard_resolution->url,
				$item->images->standard_resolution->width, $item->images->standard_resolution->height);
		}
	}
	
    private function getCaption($text){
		return $this->hashtagLinks($text);
    }

//    private function getUserMeta($userId, $accessToken){
//        $request = $this->getFeedData("https://api.instagram.com/v1/users/{$userId}/?access_token={$accessToken}");
//        $json = json_decode($request['response']);
//        if (!is_object($json) || (is_object($json) && sizeof($json->data) == 0)) {
//            if (isset($request['errors']) && is_array($request['errors'])){
//                foreach ( $request['errors'] as $error ) {
//                    $error['type'] = 'instagram';
//                    //TODO $this->filterErrorMessage
//                    $this->errors[] = $error;
//                    throw new \Exception();
//                }
//            }
//            else {
//                $this->errors[] = array('type'=>'instagram', 'message' => 'Bad request, access token issue', 'url' => "https://api.instagram.com/v1/users/search?q={$userId}&access_token={$accessToken}");
//                throw new \Exception();
//            }
//            return $userId;
//        }
//        else {
//            if($json->data){
//                return $json->data;
//            }else{
//                $this->errors[] = array(
//                    'type' => 'instagram',
//                    'message' => 'User not found',
//                    'url' => "https://api.instagram.com/v1/users/{$userId}&access_token={$accessToken}"
//                );
//                throw new \Exception();
//            }
//        }
//    }

	/**
	 * @param $content
	 * @param $accessToken
	 *
	 * @return string
	 * @throws \Exception
	 */
//	private function getUserId($content, $accessToken){
//		$request = $this->getFeedData("https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}");
//		$json = json_decode($request['response']);
//		if (!is_object($json) || (is_object($json) && sizeof($json->data) == 0)) {
//			if (isset($request['errors']) && is_array($request['errors'])){
//				foreach ( $request['errors'] as $error ) {
//					$error['type'] = 'instagram';
//					//TODO $this->filterErrorMessage
//					$this->errors[] = $error;
//					throw new \Exception();
//				}
//			}
//			else {
//				$this->errors[] = array('type'=>'instagram', 'message' => 'Bad request, access token issue. <a href="http://docs.social-streams.com/article/55-400-bad-request" target="_blank">Troubleshooting</a>.', 'url' => "https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}");
//				throw new \Exception();
//			}
//			return $content;
//		}
//		else {
//            $lowerContent = strtolower($content);
//			foreach($json->data as $user){
//				if (strtolower($user->username) == $lowerContent) return $user->id;
//			}
//			$this->errors[] = array(
//				'type' => 'instagram',
//				'message' => 'Username not found',
//                'url' => "https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}"
//			);
//			throw new \Exception();
//		}
//	}

	/**
	 * @param $item
	 *
	 * @return array
	 */
	public function getComments($item) {
		if (empty($item) || is_object($item)){
			return [];
		}

		$result = [];
		$objectId = $item;
		$instagram = new Instagram();
		$code = $this->getCodeFromId($objectId);
		$mediaLink = Endpoints::getMediaPageLink($code);
		$media = $instagram->getMediaByUrl($mediaLink);
//		$media = $instagram->getMediaById($objectId);
		//$comments = $instagram->getMediaCommentsById($objectId);
		$comments = array_slice($media->getComments(), 0, 5);
		foreach ( $comments as $comment ) {
			$from = new \stdClass();
			$from->id = $comment->getOwner()->getId();
			$from->username = $comment->getOwner()->getUsername();
			$from->full_name = $comment->getOwner()->getFullName();
			$from->profile_picture = $comment->getOwner()->getProfilePicUrl();

			$c = new \stdClass();
			$c->id = $comment->getId();
			$c->text = $comment->getText();
			$c->created_time = $comment->getCreatedAt();
			$c->from = $from;
			$result[] = $c;
		}
		return $result;

//		$accessToken = $this->feed->instagram_access_token;
//		$url = "https://api.instagram.com/v1/media/{$objectId}/comments?access_token={$accessToken}";
//		$request = $this->getFeedData($url);
//		$json = json_decode($request['response']);
//
//		if (!is_object($json) || (is_object($json) && sizeof($json->data) == 0)) {
//			if (isset($request['errors']) && is_array($request['errors'])){
//				if (!empty($request['errors'])){
//					foreach ( $request['errors'] as $error ) {
//						$error['type'] = 'instagram';
//						//TODO $this->filterErrorMessage
//						$this->errors[] = $error;
//						throw new \Exception();
//					}
//				}
//			}
//			else {
//				$this->errors[] = array('type'=>'instagram', 'message' => 'Bad request, access token issue. <a href="http://docs.social-streams.com/article/55-400-bad-request" target="_blank">Troubleshooting</a>.', 'url' => $url);
//				throw new \Exception();
//			}
//			return array();
//		}
//		else {
//			if($json->data){
//				// return first 5 comments
//				return array_slice($json->data, 0, 5);
//			}else{
//				$this->errors[] = array(
//					'type' => 'instagram',
//					'message' => 'User not found',
//					'url' => $url
//				);
//				throw new \Exception();
//			}
//		}
	}

	protected function nextPage( $result ) {
		if ($this->pagination){
			$size = sizeof($result);
			if ($size == $this->size) {
				return false;
			}
			else {
				$this->size = $size;
				return $this->getCount() > $size;
			}
		}
		return false;
	}

	private function hashtagLinks($text) {
		$result = preg_replace('~(\#)([^\s!,. /()"\'?]+)~', '<a href="https://www.instagram.com/explore/tags/$2">#$2</a>', $text);
		return $result;
	}

	private function fillUser($username){
		$account = $this->getAccount($username);
		$result = new \stdClass();
		$result->username = $account->getUsername();
		$result->full_name = $account->getFullName();
		$result->id = $account->getId();
		$result->bio = $account->getBiography();
		$result->website = $account->getExternalUrl();
		$result->counts = new \stdClass();
		$result->counts->media = $account->getMediaCount();
		$result->counts->follows = $account->getFollowsCount();
		$result->counts->followed_by = $account->getFollowedByCount();
		$result->profile_picture = $account->getProfilePicUrlHd();
		return $result;
	}

	/**
	 * @param string $username
	 *
	 * @return \InstagramScraper\Model\Account
	 * @throws LASocialException
	 * @throws \InstagramScraper\Exception\InstagramException
	 */
	private function getAccount($username){
		if (!array_key_exists($username, $this->accounts)){
			$i = new Instagram();
			try {
				$this->accounts[$username] = $i->getAccount($username);
			} catch ( InstagramNotFoundException $e ) {
				throw new LASocialException('Username not found', array(), $e);
			}
		}
		return $this->accounts[$username];
	}

	/**
	 * @param string $id
	 *
	 * @return \InstagramScraper\Model\Account
	 * @throws LASocialException
	 * @throws \InstagramScraper\Exception\InstagramException
	 */
	private function getAccountById($id){
		if (!array_key_exists($id, $this->accounts)){
			$i = new Instagram();
			try {
				$this->accounts[$id] = $i->getAccountById($id);
			} catch ( InstagramNotFoundException $e ) {
				throw new LASocialException('Username not found', array(), $e);
			}
		}
		return $this->accounts[$id];
	}

	private function getCodeFromId($id) {
		$parts = explode('_', $id);
		$id = $parts[0];
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
		$code = '';
		while ($id > 0) {
			if (PHP_INT_SIZE === 4 && function_exists('bcmod')){
				$remainder = bcmod($id, 64);
				$t = bcsub($id, $remainder);
				$id = bcdiv($t, 64);
			}
			else {
				$remainder = $id % 64;
				$id = ($id - $remainder) / 64;
			}
			$code = $alphabet{$remainder} . $code;
		};
		return $code;
	}
}