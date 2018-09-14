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
class FFGoogle extends FFHttpRequestFeed implements LAFeedWithComments {
    private $apiKey;
    private $content;
	private $media;
	private $image;
	private $source;
	private $profileImage;

	public function __construct() {
		parent::__construct( 'google' );
	}

	public function deferredInit($feed){
		$this->content = $feed->content;
		$this->apiKey = $feed->google_api_key;
	}

	protected function getUrl(){
        return "https://www.googleapis.com/plus/v1/people/{$this->content}/activities/public?key={$this->apiKey}&maxResults={$this->getCount()}&prettyprint=false&fields=items(id,actor,object(attachments(displayName,fullImage,id,image,objectType,url,thumbnails),id,content,objectType,url,replies(totalItems),plusoners(totalItems),resharers(totalItems)),published,title,url)";
    }

    protected function items($request){
        $pxml = json_decode($request);
        if (isset($pxml->items)) {
            return $pxml->items;
        }
        return array();
    }

    protected function prepare( $item ) {
	    $this->image = null;
	    $this->media = null;
	    $this->source = null;
        return parent::prepare( $item );
    }


    protected function getId($item){
        return $item->id;
    }

    protected function getScreenName($item){
        return $item->actor->displayName;
    }

    protected function getProfileImage($item){
	    if (empty($this->profileImage)) {
		    $url = $item->actor->image->url;
		    $parts = parse_url($url);
		    if (isset($parts['query'])){
			    parse_str($parts['query'], $attr);
			    if (isset($attr['sz'])) {
				    $url = str_replace('sz='.$attr['sz'], 'sz=100', $url);
			    }
		    }
		    $this->profileImage = $url;
	    }
        return $this->profileImage;
    }

    protected function getSystemDate($item){
        return strtotime($item->published);
    }

    protected function getContent($item){
        if (isset($item->object->originalContent) && !empty($item->object->originalContent)) {
            return $item->object->originalContent;
        }
        return $item->object->content;
    }

    protected function getHeader($item){
	    if (isset($item->object->attachments)
	        && sizeof($item->object->attachments) > 0 ) {
		    $attach = $item->object->attachments[0];
		    if ($attach->objectType == 'article' || $attach->objectType == 'album' || $attach->objectType == 'video' || $attach->objectType == 'event'){
			    return $attach->displayName;
		    }
	    }
        return '';
    }

    protected function getUserlink($item){
        return $item->actor->url;
    }

    protected function getPermalink($item){
        return $item->url;
    }

    protected function showImage($item){
	    if (isset($item->object->attachments)
	        && sizeof($item->object->attachments) > 0 ) {
		    $attach = $item->object->attachments[0];
		    if ($attach->objectType == 'article') {
			    if (isset($attach->fullImage)){
				    $url = $attach->fullImage->url;
				    $this->media = $this->createMedia($url, null, null, 'image', true);
				    if ($this->media['width'] == null || $this->media['height'] == null){
					    $url = $attach->image->url;
					    $this->media = $this->createMedia($url, $attach->image->width, $attach->image->height, 'image', true);
				    }
				    $this->image = $this->createImage($url, $this->media['width'], $this->media['height']);
				    $this->source = $attach->url;
				    return $this->media['width'] > 260;
			    }
			    return false;
		    }
		    else if ($attach->objectType == 'photo'){
			    $this->media = $this->createMedia($attach->fullImage->url, $attach->fullImage->width, $attach->fullImage->height, 'image', true);
			    $this->image = $this->createImage($attach->fullImage->url, $this->media['width'], $this->media['height']);
			    return true;
		    }
	        else if ($attach->objectType == 'video'){
		        $url = $attach->url;
		        $this->image = $this->createImage($attach->image->url, $attach->image->width, $attach->image->height);
		        if (strpos($url, 'youtube.com') > 0) {
		            if ((strpos($url, '?v=') > 0 || strpos($url, '&v=') > 0)) {
			            $query_str = parse_url( $url, PHP_URL_QUERY );
			            parse_str( $query_str, $query_params );
			            $videoId = $query_params['v'];
		            }
		            else if (strpos($url, 'www.youtube.com/attribution_link') > 0) {
			            $url = urldecode($url);
			            $query_string = @end(explode('?',$url));
			            parse_str($query_string, $query_params);
			            $videoId = $query_params['v'];
		            }
			        $height = FFFeedUtils::getScaleHeight(600, $this->image['width'], $this->image['height']);
			        $this->media = $this->createMedia("http://www.youtube.com/v/{$videoId}?version=3&f=videos&autoplay=0", 600, $height, "application/x-shockwave-flash");
			        $this->source = $attach->url;
			        return true;
		        }
		        else {
			        $this->source = $attach->url;
			        $this->media = $this->createMedia($attach->url, 600, FFFeedUtils::getScaleHeight(600, $this->image['width'], $this->image['height']), 'video');//application/x-shockwave-flash
			        return true;
			        //TODO
		        }
	        }
		    else if ($attach->objectType == 'album'){
			    $thumbnail = $attach->thumbnails[0]->image;
			    $this->image = $this->createImage($thumbnail->url, $thumbnail->width, $thumbnail->height);
			    $this->media = $this->createMedia($thumbnail->url, $thumbnail->width, $thumbnail->height, 'image', true);
			    return true;
		    }
		    else if ($attach->objectType == 'event'){
			    $this->source = $attach->url;
			    return false;
		    }
		    else {
			    return false;
		    }
	    }
        return false;
    }

	protected function getImage($item) {
		return $this->image;
    }

	protected function getMedia( $item ) {
		return $this->media;
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']      = (string)@$item->object->plusoners->totalItems;
		$additional['comments']   = (string)@$item->object->replies->totalItems;
		$additional['shares']     = (string)@$item->object->resharers->totalItems;
		return $additional;
	}

	protected function customize( $post, $item ) {
		$post->nickname = substr($this->content, 0, 1) == '+' ? $this->content : '';
		if (!empty($this->source)) $post->source = $this->source;
        return parent::customize( $post, $item );
    }
	
	public function getComments($item) {
		if (is_object($item)){
			return array();
		}
		
		$objectId = $item;
		$this->apiKey = $this->feed->google_api_key;
        $url = "https://www.googleapis.com/plus/v1/activities/{$objectId}/comments?key={$this->apiKey}&maxResults={$this->getCount()}";
        $request = $this->getFeedData($url);
        $json = json_decode($request['response']);

        if (!is_object($json) || (is_object($json) && sizeof($json->items) == 0)) {
            if (isset($request['errors']) && is_array($request['errors'])){
                if (!empty($request['errors'])){
                    foreach ( $request['errors'] as $error ) {
                        $error['type'] = 'google';
                        //TODO $this->filterErrorMessage
                        $this->errors[] = $error;
                        throw new \Exception();
                    }
                }
            }
            else {
                $this->errors[] = array('type'=>'google', 'message' => 'Bad request, access token issue. <a href="http://docs.social-streams.com/article/55-400-bad-request" target="_blank">Troubleshooting</a>.', 'url' => $url);
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
                    $obj->id = $item->id;
                    $obj->from = array(
                        'id' => $item->actor->id,
                        'full_name' => $item->actor->displayName
                    );
                    $obj->text = $item->object->content;
                    $obj->created_time = $item->published;
                    $result[] = $obj;
                }
                return $result;
            }else{
                $this->errors[] = array(
                    'type' => 'google',
                    'message' => 'User not found',
                    'url' => $url
                );
                throw new \Exception();
            }
        }
    }
}