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
class FFInstagram extends FFBaseFeed {
    private $url;
	private $size = 0;
	private $pagination = true;

	public function __construct() {
		parent::__construct( 'instagram' );
	}

	public function deferredInit($options, $feed) {
        $original = $options->original();
        $accessToken = $original['instagram_access_token'];
        if (isset($feed->{'timeline-type'})) {
            switch ($feed->{'timeline-type'}) {
                case 'user_timeline':
                    $userId = $this->getUserId($feed->content, $accessToken);
                    $this->url = "https://api.instagram.com/v1/users/{$userId}/media/recent/?access_token={$accessToken}&count={$this->getCount()}";
                    break;
                case 'likes':
                    $this->url = "https://api.instagram.com/v1/users/self/media/liked?access_token={$accessToken}&count={$this->getCount()}";
                    break;
                case 'licked':
                    $this->url = "https://api.instagram.com/v1/users/self/media/liked?access_token={$accessToken}&count={$this->getCount()}";
                    break;
                case 'tag':
	                $tag = urlencode($feed->content);
                    $this->url = "https://api.instagram.com/v1/tags/{$tag}/media/recent?access_token={$accessToken}&count={$this->getCount()}";
                    break;
	            case 'location':
	            	$location = $feed->content;
		            $this->url = "https://api.instagram.com/v1/locations/{$location}/media/recent?access_token={$accessToken}&count={$this->getCount()}";
		            break;
                default:
                    $this->url = "https://api.instagram.com/v1/users/self/feed?access_token={$accessToken}&count={$this->getCount()}";
            }
        }
    }

    public function onePagePosts() {
        $result = array();
        $data = $this->getFeedData($this->url);
        if (sizeof($data['errors']) > 0){
	        $this->errors[] = array(
		        'type'    => $this->getType(),
	        	'message' => is_object($data['errors']) ? 'Error getting data from instagram server' : $this->filterErrorMessage($data['errors']),
		        'url' => $this->url
	        );
	        error_log(print_r($data['errors'], true));
	        throw new \Exception();
        }
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
	            if ($this->isSuitablePost($post)) $result[$post->id] = $post;
            }
        } else {
	        $this->errors[] = array(
		        'type'    => 'instagram',
		        'message' => 'FFInstagram has returned the empty data.',
		        'url' => $this->url
	        );
        }
        return $result;
    }

    private function parsePost($post) {
        $tc = new \stdClass();
	    $tc->feed_id = $this->id();
        $tc->id = (string)$post->id;
	    $tc->header = '';
        $tc->type = $this->getType();
        $tc->nickname = (string)$post->user->username;
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
        $tc->text = $this->getCaption($post);
        $tc->userlink = 'http://instagram.com/' . $tc->nickname;
        $tc->permalink = (string)$post->link;

	    if (isset($post->type) && $post->type == 'video' && isset($post->videos)){
		    $tc->media = array('type' => 'video/mp4', 'url' => $post->videos->standard_resolution->url,
			      'width' => 600,
			      'height' => FFFeedUtils::getScaleHeight(600, $post->videos->standard_resolution->width, $post->videos->standard_resolution->height));
	    } else {
		    $tc->media = $this->createMedia($post->images->standard_resolution->url,
			    $post->images->standard_resolution->width, $post->images->standard_resolution->height);
	    }
	    @$tc->additional = array('likes' => (string)$post->likes->count, 'comments' => (string)$post->comments->count);
        return $tc;
    }

    private function getCaption($post){
        if (isset($post->caption->text)) {
	        $text = FFFeedUtils::removeEmoji( (string) $post->caption->text );
	        return $this->hashtagLinks($text);
        }
	    return '';
    }

	/**
	 * @param $content
	 * @param $accessToken
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function getUserId($content, $accessToken){
		$request = $this->getFeedData("https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}");
		$json = json_decode($request['response']);
		if (!is_object($json) || (is_object($json) && sizeof($json->data) == 0)) {
			if (isset($request['errors']) && is_array($request['errors'])){
				foreach ( $request['errors'] as $error ) {
					$error['type'] = 'instagram';
					//TODO $this->filterErrorMessage
					$this->errors[] = $error;
					throw new \Exception();
				}
			}
			else {
				$this->errors[] = array('type'=>'instagram', 'message' => 'Bad request, access token issue', 'url' => "https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}");
				throw new \Exception();
			}
			return $content;
		}
		else {
            $lowerContent = strtolower($content);
			foreach($json->data as $user){
				if (strtolower($user->username) == $lowerContent) return $user->id;
			}
			$this->errors[] = array(
				'type' => 'instagram',
				'message' => 'Username not found',
                'url' => "https://api.instagram.com/v1/users/search?q={$content}&access_token={$accessToken}"
			);
			throw new \Exception();
		}
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
}