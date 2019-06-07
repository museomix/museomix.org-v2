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
class FFPinterest extends FFRss {
	private $pin;
	private $pins = array();
	private $nickname;
	private $additionalUrl;
	private $originalContent;

	public function __construct() {
		parent::__construct( 'pinterest' );
	}

	public function deferredInit($feed) {
		parent::deferredInit($feed);
		$sp = explode('/', $feed->content);
		if (sizeof($sp) < 2){
			$this->nickname = $feed->content;
			$this->url = "https://www.pinterest.com/{$feed->content}/feed.rss";
			$this->additionalUrl = "https://api.pinterest.com/v3/pidgets/users/{$feed->content}/pins/";
		}
		else {
			$this->nickname = $sp[0];
			$content = $sp[0] . '/' . urlencode($sp[1]);
			$this->url = "https://www.pinterest.com/{$content}.rss";
			$this->additionalUrl = "https://api.pinterest.com/v3/pidgets/boards/{$content}/pins/";
		}
		$this->profileImage = $this->context['plugin_url'] . '/' . $this->context['slug'] . '/assets/avatar_default.png';
	}

	protected function items($request){
		$this->setAdditionalInfo();
		return parent::items($request);
	}

	protected function prepare($item){
		$this->originalContent = (string)$item->description;
		$key = str_replace('https:', 'http:', (string)$item->guid);
		array_key_exists($key, $this->pins) ? $this->pin = $this->pins[$key] : $this->pin = null;
		return parent::prepare($item);
	}

	protected function getScreenName($item){
		return is_null($this->pin) ? parent::getScreenName($item) : $this->pin->pinner->full_name;
	}

	protected function getProfileImage($item){
		$url = is_null($this->pin) ? parent::getProfileImage($item) : $this->pin->pinner->image_small_url;
		$url = str_replace('30x30_', '140x140_', $url);
		return $url;
	}

	protected function getContent($item){
		return is_null($this->pin) ? parent::getHeader($item) : $this->pin->description;
	}

	protected function getHeader($item){
		return '';
	}

	protected function getPermalink($item){
		return (string)$item->guid;
	}

	protected function getUserlink($item){
		return is_null($this->pin) ? 'http://www.pinterest.com/' . $this->nickname : $this->pin->pinner->profile_url;
	}

	protected function customize($post, $item){
		$post = parent::customize($post, $item);
		$post->nickname = $this->nickname;
		return $post;
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']  = (string)@$this->pin->board->pin_count;
		$additional['shares'] = (string)@$this->pin->repin_count;
		return $additional;
	}

	protected function showImage($item){
		if (!is_null($this->pin) && isset($this->pin->images->{'237x'})){
			$x237 = $this->pin->images->{'237x'};
			$this->image = $this->createImage($x237->url, $x237->width, $x237->height);
			if (isset($this->pin->embed)){
				if ($this->pin->is_video == 'true'){
					$this->media = $this->createMedia($this->pin->embed->src, 600, FFFeedUtils::getScaleHeight(600, $x237->width, $x237->height), 'video');
				}
				else {
					$width = $this->pin->embed->width;
					$height = $this->pin->embed->height;
					if ($width > 600){
						$height = FFFeedUtils::getScaleHeight(600, $width, $height);
						$width = 600;
					}
					$this->media = $this->createMedia($this->pin->embed->src, $width, $height);
				}
			}
			else {
				$this->media = $this->createMedia(str_replace('237x', '736x', $x237->url));
			}
		} else {
			$this->image = $this->createImage(FFFeedUtils::getUrlFromImg($this->originalContent));
			$this->media = $this->createMedia($this->image['url'], $this->image['width'], $this->image['height']);
		}
		return true;
	}

	private function setAdditionalInfo(){
		$data = $this->getFeedData($this->additionalUrl);
		if (sizeof($data['errors']) > 0){
			$this->errors[] = array(
				'type'    => 'pinterest',
				'message' =>  print_r(isset($data['errors']['msg']) ? $data['errors']['msg'] : 'No error message', true),
				'url' => $this->additionalUrl
			);
		} else {
			$response = json_decode($data['response']);
			foreach ($response->data->pins as $pin){
				$this->pins["http://www.pinterest.com/pin/{$pin->id}/"] = $pin;
			}
		}
	}
}