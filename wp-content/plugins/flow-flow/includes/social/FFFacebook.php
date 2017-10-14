<?php namespace flow\social;
use flow\cache\LAFacebookCacheManager;
use flow\db\FFDB;
use flow\db\LADBManager;

if ( ! defined( 'WPINC' ) ) die;
if ( ! defined('FF_FACEBOOK_RATE_LIMIT')) define('FF_FACEBOOK_RATE_LIMIT', 200);
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFFacebook extends FFHttpRequestFeed{
	const API_VERSION = 'v2.8';

    /** @var  string | bool */
    private $accessToken;
    /** @var bool */
    private $hideStatus = true;
	/** @var  bool */
	private $saveImages = false;
	private $image;
	private $media;
	private $images;
	private $youtube_api_key;

	private $hasHitLimit = false;
	private $creationTime;
	private $request_count = 0;
	private $global_request_count;
	private $global_request_array;

	private $new_post_ids;

	public function __construct() {
		parent::__construct( 'facebook' );
	}

    public function deferredInit($options, $feed) {
	    $this->images = array();
        if (isset($feed->{'timeline-type'})) {
        	$timeline = $feed->{'timeline-type'} == 'user_timeline' ? 'page_timeline' : $feed->{'timeline-type'};
	        $locale = defined('FF_LOCALE') ? 'locale=' . FF_LOCALE : 'locale=en_US';
	        $fields    = 'fields=';
	        $fields    = $fields . 'likes.summary(true),comments.summary(true),shares,';
	        $fields    = $fields . 'id,created_time,from,link,message,name,object_id,picture,full_picture,attachments{media{image}},source,status_type,story,type';
	        $api = FFFacebook::API_VERSION;
            switch ($timeline) {
	            case 'group':
		            $groupId = (string)$feed->content;
		            $this->url        = "https://graph.facebook.com/{$api}/{$groupId}/feed?{$fields}&limit={$this->getCount()}&{$locale}";
		            $this->hideStatus = false;
		            break;
                case 'page_timeline':
                    $userId = (string)$feed->content;
	                $this->url        = "https://graph.facebook.com/{$api}/{$userId}/posts?{$fields}&limit={$this->getCount()}&{$locale}";
	                $this->hideStatus = false;
                    break;
	            case 'album':
					$albumId = (string)$feed->content;
		            $this->url = "https://graph.facebook.com/{$api}/{$albumId}/photos?{$fields}&limit={$this->getCount()}&{$locale}";
		            break;
                default:
	                $this->url = "https://graph.facebook.com/{$api}/me/home?{$fields}&limit={$this->getCount()}&{$locale}";
            }
        }
	    $original = $options->original();
	    $this->youtube_api_key = @$original['google_api_key'];

	    $this->saveImages = false;//FFSettingsUtils::YepNope2ClassicStyleSafe($original, 'general-settings-save-images', false);
    }

	protected function beforeProcess() {
		global $flow_flow_context;
		/** @var LAFacebookCacheManager $facebookCache */
		$facebookCache = $flow_flow_context['facebook_cache'];
		/** @var LADBManager $db */
		$db = $flow_flow_context['db_manager'];

		$this->accessToken = $facebookCache->getAccessToken();
		if ($this->accessToken === false){
			$this->errors[] = $facebookCache->getError();
			return false;
		}

		if (FFDB::beginTransaction()){
			$limit = $db->getOption('fb_limit_counter', true, true);
			if ($limit === false){
				@$db->setOption('fb_limit_counter', array(), true, false);
				$limit = $db->getOption('fb_limit_counter', true, true);
			}

			if (!is_array($limit)){
				$this->errors[] = array( 'type' => 'facebook', 'message' => 'Can`t save `fb_limit_counter` option to mysql db.' );
				FFDB::rollback();
				return false;
			}

			$this->creationTime = time();
			$this->global_request_count = 0;
			$limitTime = $this->creationTime - 3600;
			$result = array();
			foreach ( $limit as $time => $count ) {
				if ($time > $limitTime) {
					$result[$time] = $count;
					$this->global_request_count += (int)$count;
				}
			}
			$this->global_request_array = $result;

			if ($this->global_request_count + 4 > FF_FACEBOOK_RATE_LIMIT) {
				$this->errors[] = array( 'type' => 'facebook', 'message' => 'Your site has hit the facebook api rate limit.' );
				FFDB::rollback();
				return false;
			}
		}
		else {
			$this->errors[] = array( 'type' => 'facebook', 'message' => 'Can`t get mysql transaction.' );
			FFDB::rollback();
			return false;
		}
		return true;
	}

	protected function afterProcess( $result ) {
		global $flow_flow_context;
		/** @var LADBManager $db */
		$db = $flow_flow_context['db_manager'];

		if ($this->request_count > 0) {
			$this->global_request_array[$this->creationTime] = $this->request_count;
		}
		$db->setOption('fb_limit_counter', $this->global_request_array, true, false);
		FFDB::commit();
		return parent::afterProcess( $result );
	}

	protected function getUrl() {
        return $this->getUrlWithToken($this->url);
    }

    protected function items( $request ) {
        $pxml = json_decode($request);
        if (isset($pxml->data)) {
	        global $flow_flow_context;
	        /** @var LADBManager $db */
	        $db = $flow_flow_context['db_manager'];
	        $ids = $db->getIdPosts($this->id());
	        $new_ids = array();
	        foreach ( $pxml->data as $item ) {
		        $new_ids[] = $this->getId($item);
	        }
	        $this->new_post_ids = array();
	        $diff = array_diff($new_ids, $ids);
	        foreach ( $diff as $id ) {
		        $this->new_post_ids[$id] = 1;
	        }

	        return $pxml->data;
        }
        return array();
    }

	protected function isSuitableOriginalPost( $post ) {
		if (!array_key_exists($this->getId($post), $this->new_post_ids)) {
			return false;
		}

		if (isset($post->type)){
			if ($post->type == 'status' && ($this->hideStatus || !isset($post->message))) return false;
			if ($post->type == 'photo' && isset($post->status_type) && $post->status_type == 'tagged_in_photo') return false;
		}
		if (!isset($post->created_time)) return false;

		if ($this->hasHitLimit) return false;
		if ($this->global_request_count + $this->request_count + 3 > FF_FACEBOOK_RATE_LIMIT) {
			$this->errors[] = array( 'type' => 'facebook', 'message' => 'Your site has hit the facebook api rate limit.' );
			$this->hasHitLimit = true;
			return false;
		}

		return true;
	}


	protected function prepare( $item ) {
		$this->image = null;
		$this->media = null;
		return parent::prepare( $item );
	}

	protected function getHeader($item){
		if (!isset($item->type) || (isset($item->status_type) && $item->status_type == 'added_photos')){
			return '';
		}
		if (isset($item->name)){
			return $item->name;
		}
        return '';
    }

    protected function showImage($item){
		$api = FFFacebook::API_VERSION;

		if (!isset($item->type)){
			$this->image = $this->createImage($item->source);
			$this->media = $this->createMedia($item->source);
			return true;
		}

		if ((isset($item->object_id) && (($item->type == 'photo')))){
			$url = "https://graph.facebook.com/{$api}/{$item->object_id}?fields=images";
			$original_url = $this->cache->getOriginalUrl($url);
			if ($original_url == ''){
				$objects = $this->getImageObjectByUrl($url);
				if (false !== $objects){
					$object = $objects['image'];
					$this->image = $this->createImage($object->source, $object->width, $object->height);
					$object = $objects['media'];
					$this->media = $this->createMedia($object->source, $object->width, $object->height);
				}
				else {
					//old method of get original url
					$url = "https://graph.facebook.com/{$api}/{$item->object_id}/picture?width={$this->getImageWidth()}&type=normal";
					$original_url = $this->cache->getOriginalUrl($url);
					if ($original_url == ''){
						$original_url = $this->getLocation($url);
					}
					
					$size = $this->cache->size($url, $original_url);
					$width = $size['width'];
					$height = $size['height'];
					$this->image = $this->createImage($original_url, $width, $height);
					
					$url = "https://graph.facebook.com/{$api}/{$item->object_id}/picture?width=600&type=normal";
					$original_url = $this->cache->getOriginalUrl($url);
					if ($original_url == ''){
						$original_url = $this->getLocation($url);
					}
					$size = $this->cache->size($url, $original_url);
					$width = $size['width'];
					$height = $size['height'];
					$this->media = $this->createMedia($original_url, $width, $height);
				}
			}
			return true;
		}


	    if (($item->type == 'video')
	        && (!isset($item->status_type) || $item->status_type == 'added_video' ||
	        	$item->status_type == 'shared_story' || $item->status_type == 'mobile_status_update')){
		    if (!isset($item->object_id) && isset($item->link) && strpos($item->link, 'facebook.com') > 0 && strpos($item->link, '/videos/') > 0){
			    $path = parse_url($item->link, PHP_URL_PATH);
			    $tokens = explode('/', $path);
			    if (empty($tokens[sizeof($tokens)-1])) unset($tokens[sizeof($tokens)-1]);
			    $item->object_id = $tokens[sizeof($tokens)-1];
		    }
		    if (isset($item->object_id) && trim($item->object_id) != ''){
			    $data = $this->getFeedData($this->getUrlWithToken( "https://graph.facebook.com/{$api}/{$item->object_id}?fields=embed_html,source" ));
			    $data = json_decode($data['response']);
			    preg_match("/\<iframe.+width\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/", $data->embed_html, $matches);
			    $width = $matches[1];
			    preg_match("/\<iframe.+height\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/", $data->embed_html, $matches);
			    $height = $matches[1];
			    if ($width > 600) {
				    $height = FFFeedUtils::getScaleHeight(600, $width, $height);
				    $width = 600;
				}
				$url = "https://graph.facebook.com/{$api}/{$item->object_id}/picture?width={$this->getImageWidth()}&type=normal";
				{
					if ($width == 0 || $height == 0) {
					    $this->image = $this->createImage($this->getLocation($url));
					    $width = $this->image['width'];
					    $height = $this->image['height'];
				    }
				    else {
					    $this->image = $this->createImage( $this->getLocation( $url ), $width, $height );
				    }
			    }
			    $this->media = $this->createMedia('http://www.facebook.com/video/embed?video_id=' . $item->object_id, $width, $height, 'video');
			    return true;
		    }
		    else if (isset($item->source)){
		        if (strpos($item->source, 'giphy.com') > 0) {
			        $arr = parse_url( urldecode( $item->source ) );
			        parse_str( $arr['query'], $output );
			        $this->image = $this->createImage( $output['gif_url'], $output['giphyWidth'], $output['giphy_height'] );
			        $this->media = $this->createMedia( $output['gif_url'], $output['giphyWidth'], $output['giphy_height'] );
			        return $this->image != null;
		        }
			    if (!empty($this->youtube_api_key) && (strpos($item->source, 'youtube.com') > 0)) {
				    if (strpos($item->source, 'list=') > 0){
					    $query_str = parse_url($item->source, PHP_URL_QUERY);
					    parse_str($query_str, $query_params);
					    $listId = $query_params['list'];
					    $url = "https://www.googleapis.com/youtube/v3/playlists?part=snippet&id={$listId}&key={$this->youtube_api_key}";
				    }
				    else if ((strpos($item->link, '?v=') > 0) || (strpos($item->link, '&v=') > 0)){
					    $query_str = parse_url($item->link, PHP_URL_QUERY);
					    parse_str($query_str, $query_params);
					    $listId = $query_params['v'];
					    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$listId}&key={$this->youtube_api_key}";
				    }
				    if (isset($url)){
					    $data = @$this->getFeedData($url);
					    if (is_array($data) && isset($data['response'])){
						    $response = $data['response'];
						    $pxml = json_decode($response);
						    if (isset($pxml->items)) {
							    $youtubeItem = $pxml->items[0];
							    $thumbnail = $youtubeItem->snippet->thumbnails->high;
							    if (isset($thumbnail->width)){
								    $this->image = $this->createImage($thumbnail->url, $thumbnail->width, $thumbnail->height);
							    } else {
								    $this->image = $this->createImage($thumbnail->url);
							    }
							    $this->media = $this->createMedia($item->source, 600, FFFeedUtils::getScaleHeight(600, $this->image['width'], $this->image['height']), 'video');
							    return true;
						    }
					    }
				    }
			    }
			    if (isset($item->full_picture)){
				    $this->image = $this->createImage($item->full_picture);
				    $type = (strpos($item->source, '.mp4') > 0) ? 'video/mp4' : 'video';
				    $this->media = $this->createMedia($item->source, 600, FFFeedUtils::getScaleHeight(600, $this->image['width'], $this->image['height']), $type);
				    return true;
			    }
			    if (isset($item->picture)){
				    $this->image = $this->createImage($item->picture);
				    $type = (strpos($item->source, '.mp4') > 0) ? 'video/mp4' : 'video';
				    $this->media = $this->createMedia($item->source, 600, FFFeedUtils::getScaleHeight(600, $this->image['width'], $this->image['height']), $type);
				    return true;
			    }
			    //TODO snappytv.com
			    //TODO twitch.tv swf
		    }
		}


		if ((($item->type == 'link') || ($item->type == 'event')) && isset($item->picture)){
			if (isset($item->full_picture)){
				$this->image = $this->createImage($item->full_picture);
				$this->media = $this->createMedia((pathinfo($item->link, PATHINFO_EXTENSION) === 'gif') ? $item->link : $item->full_picture, null, null, 'image', true);
				return true;
			}
			else if (isset($item->attachments->data) && sizeof($item->attachments->data) > 0){
				$media = $item->attachments->data[0];
				if (isset($media->media) && isset($media->media->image)){
					$media = $media->media->image;
					$this->image = $this->createImage($media->src, $media->width, $media->height);
					$this->media = $this->createMedia($media->src, $media->width, $media->height, 'image', true);
					return true;
				}
			}

			$image = $item->picture;
			$parts = parse_url($image);
			if (isset($parts['query'])){
			    parse_str($parts['query'], $attr);
			    if (isset($attr['url'])) {
				    $image = $attr['url'];
				    $original = $this->createImage($image, null, null, false);
				    if (150 > $original['width'] || 150 > $original['height']) return false;
				    $this->image = $this->createImage($image, $original['width'], $original['height']);
				    if (!empty($this->image['height'])) {
					    $this->media = $this->createMedia($image, null, null, 'image', true);
					    return true;
				    }
			    }
			}
			$this->image = $this->createImage($item->picture);
			$this->media = $this->createMedia($item->picture, null, null, 'image', true);
			return true;
		}
		
		if ($item->type == 'status' && isset($item->attachments->data) && sizeof($item->attachments->data) > 0){
			$media = $item->attachments->data[0];
			if (isset($media->media) && isset($media->media->image)){
				$media = $media->media->image;
				$this->image = $this->createImage($media->src, $media->width, $media->height);
				$this->media = $this->createMedia($media->src, $media->width, $media->height, 'image', true);
				return true;
			}
		}
			
		return false;
	}

    protected function getImage( $item ) {
        return $this->image;
    }

	protected function getMedia( $item ) {
		return $this->media;
	}

	protected function getScreenName($item){
        return $item->from->name;
    }

	//TODO going to use a message_tags attribute
    protected function getContent($item){
	    if (!isset($item->type) && isset($item->name)) return (string)$item->name;
	    if (isset($item->message)) return self::wrapHashTags(FFFeedUtils::wrapLinks($item->message), $item->id);
	    if (isset($item->story)) return (string)$item->story;
	    return '';
    }

    protected function getProfileImage( $item ) {
	    $url = "https://graph.facebook.com/" . FFFacebook::API_VERSION . "/{$item->from->id}/picture?width=80&height=80";
	    if (!array_key_exists($url, $this->images)){
		    if ($this->saveImages){
			    list($path, $remoteUrl, $localUrl) = $this->getInfo4Save($url);
			    if (!file_exists($path)) {
				    if ( copy( $remoteUrl, $path ) ) {
					    $this->images[$url] = $localUrl;
				    }
				    else {
					    $this->images[$url] = $this->getLocation($url);
				    }
			    }
			    else {
				    $this->images[$url] = $localUrl;
			    }
		    }
		    else $this->images[$url] = $this->getLocation($url);
	    }
        return $this->images[$url];
    }

    protected function getId( $item ) {
        return $item->id;
    }

    protected function getSystemDate( $item ) {
        return strtotime($item->created_time);
    }

    protected function getUserlink( $item ) {
        return 'https://www.facebook.com/'.$item->from->id;
    }

    protected function getPermalink( $item ) {
	    if (!isset($item->type)){
		    return $item->link;
	    }
        $parts = explode('_', $item->id);
        return 'https://www.facebook.com/'.$parts[0].'/posts/'.$parts[1];
    }

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']      = (string)@$item->likes->summary->total_count;
		$additional['comments']   = (string)@$item->comments->summary->total_count;
		$additional['shares']     = isset($item->shares) ? (string)@$item->shares->count : '0';
		return $additional;
	}

	protected function customize( $post, $item ) {
		if (isset($item->type) && $item->type == 'link' && isset($item->link) && strlen($item->link) < 300){
			$post->source = $item->link;
		}
        return parent::customize( $post, $item );
    }

	private function getInfo4Save($url){
		$fileName = hash('md5', $url);
		$path = WP_CONTENT_DIR . '/flow-flow-media-cache/' . $fileName;
		if(!file_exists(WP_CONTENT_DIR . '/flow-flow-media-cache')){
			mkdir(WP_CONTENT_DIR . '/flow-flow-media-cache', 0777);
		}
		$remoteUrl = $this->getUrlWithToken($url);
		$localUrl = content_url('flow-flow-media-cache/' . $fileName);
		return array($path, $remoteUrl, $localUrl);
	}

	/**
	 * @param string $text
	 * @param string $id
	 *
	 * @return mixed
	 */
	private function wrapHashTags($text, $id){
		//return preg_replace('/#([\\d\\w]+)/', '<a href="https://www.facebook.com/hashtag/$1?source=feed_text&story_id='.$id.'">$0</a>', $text);//old
		return preg_replace("/#([A-Za-z0-9ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ\/\.]*)/u", "<a href=\"https://www.facebook.com/hashtag/$1?source=feed_text&story_id='.$id.'\">#$1</a>", $text);
	}

	private function getPCount( $json ){
		$count = sizeof($json->data);
		if (isset($json->paging->next)){
			$data = $this->getFeedData($json->paging->next);
			$count += $this->getPCount(json_decode($data['response']));
		}
		return $count;
	}

	private function getLocation($url, $with_token = true) {
		if (defined('FF_DONT_USE_GET_HEADERS') && FF_DONT_USE_GET_HEADERS){
			$location = @$this->getCurlLocation($this->getUrlWithToken( $url ));
			if (!empty($location) && $location != 0) {
				return $location;
			}
		}
		else {
			$headers = $this->getHeadersSafe($with_token ? $this->getUrlWithToken( $url ) : $url , 1);
			if (isset($headers["Location"])) {
				return $headers["Location"];
			} else {
				$location = @$this->getCurlLocation($with_token ? $this->getUrlWithToken( $url ) : $url);
				if (!empty($location) && $location != 0) {
					return $location;
				}
			}
		}

		$location = str_replace('/v2.3/', '/', $url);
		$location = str_replace('/v2.4/', '/', $location);
		$location = str_replace('/v2.5/', '/', $location);
		$location = str_replace('/v2.6/', '/', $location);
		$location = str_replace('/v2.7/', '/', $location);
		$location = str_replace('/v2.8/', '/', $location);
		return $location;
	}

	private function getCurlLocation($url) {
		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url ) );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT_MS, 5000);
		curl_setopt( $curl, CURLOPT_TIMEOUT, 60);
		$headers = explode( "\n", curl_exec( $curl ) );
		curl_close( $curl );

		$location = '';
		foreach ( $headers as $header ) {
			if (strpos($header, "ocation:")) {
				$location = substr($header, 10);
				break;
			}
		}
		return $location;
	}

	private function getHeadersSafe($url, $format){
		if ( ini_get( 'allow_url_fopen' ) ) {
			return get_headers( $url, $format );
		} else {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, true );
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
			curl_setopt( $ch, CURLOPT_TIMEOUT, 60);
			$content = curl_exec( $ch );
			curl_close( $ch );
			return array($content);
		}
	}

	private function getUrlWithToken($url){
		$this->request_count++;
		$token = $this->accessToken;
		return $url . "&access_token={$token}";
	}
	
	private function getImageObjectByUrl($url){
		$url = $this->getUrlWithToken($url);
		$data = FFFeedUtils::getFeedData($url);
		
		if ( sizeof( $data['errors'] ) > 0 ) {
			$this->errors[] = array(
					'type'    => $this->getType(),
					'message' => $this->filterErrorMessage($data['errors']),
					'url' => $this->getUrl()
			);
			return false;
		}
		
		$result = array();
		$pxml = json_decode($data['response']);
		foreach ( $pxml->images as $item ) {
			if (empty($result)){
				$result['image'] = $item;
				$result['media'] = $item;
			}
			else if ($item->width >= $this->getImageWidth()){
				$result['image'] = $item;
			}
		}
		return $result;
	}
}