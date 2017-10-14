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
class FFVine extends FFHttpRequestFeed{
	private $page = 1;
	private $template_url;
	private $size = 0;
	private $content;
	private $needUserId = true;
	private $warn = null;

	public function __construct() {
		parent::__construct( 'vine' );
	}

	public function deferredInit( $options, $feed ) {
		$this->content = $feed->content;
		if (isset($feed->{'timeline-type'})) {
			switch ( $feed->{'timeline-type'} ) {
				case 'user_timeline':
					$this->template_url = "https://api.vineapp.com/timelines/users/%s?page=%u";
					break;
				case 'liked':
					$this->template_url = "https://api.vineapp.com/timelines/users/%s/likes?page=%u";
					break;
				case 'tag':
					$this->needUserId = false;
					$this->template_url = "https://api.vineapp.com/timelines/tags/%s?page=%u";
					break;
			}
		}

		$this->prepareAuthorData($this->content);
		$this->url = sprintf($this->template_url, $this->content, $this->page);
	}

	protected function items( $request ) {
		$pxml = json_decode($request);
		return $pxml->data->records;
	}

	protected function getId( $item ) {
		return $item->postId;
	}

	protected function getHeader( $item ) {
		return '';
	}

	protected function getScreenName( $item ) {
		return $item->username;
	}

	protected function getProfileImage( $item ) {
		return $item->avatarUrl;
	}

	protected function getSystemDate( $item ) {
		return strtotime($item->created);
	}

	protected function getContent( $item ) {
		return $this->wrapHashTags(FFFeedUtils::removeEmoji(FFFeedUtils::wrapLinks($item->description)));
	}

	protected function getUserlink( $item ) {
		return 'https://vine.co/u/' . $item->userId;
	}

	protected function getPermalink( $item ) {
		return $item->permalinkUrl;
	}

	protected function showImage( $item ) {
		return true;
	}

	protected function getImage( $item ) {
		return $this->createImage($item->thumbnailUrl, $this->getImageWidth(),
			FFFeedUtils::getScaleHeight($this->getImageWidth(), 535, 535));
	}

	protected function getMedia( $item ) {
		return $this->createMedia($item->videoUrl, 535, 535, 'video');
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']      = (string)@$item->likes->count;
		$additional['comments']   = (string)@$item->comments->count;
		$additional['shares']    = (string)@$item->reposts->count;
		return $additional;
	}

	protected function nextPage( $result ) {
		$size = sizeof($result);
		if ($size == $this->size) {
			return false;
		}
		else {
			$this->size = $size;
			$this->page = $this->page + 1;
			$this->url = sprintf($this->template_url, $this->content, $this->page);
			return $this->getCount() >= $size;
		}
	}

	protected function afterProcess( $result ) {
		if (!is_null($this->warn)) $this->errors[] = $this->warn;
		return parent::afterProcess( $result );
	}


	/**
	 * @param string $text
	 * @return mixed
	 */
	private function wrapHashTags($text){
		return preg_replace('/#([\\d\\w]+)/', '<a href="https://vine.co/tags/$1">$0</a>', $text);
	}

	/**
	 * @param string $author
	 * @return void
	 */
	private function prepareAuthorData( $author ) {
		if ($this->needUserId && !ctype_digit($author)){
			$data = $this->getFeedData('https://api.vineapp.com/users/search/' . urlencode($author));
			if (sizeof($data['errors']) > 0){
				$this->errors[] = array(
					'type'=>'author',
					'url'=>'https://api.vineapp.com/users/search/' . $author,
					'msg'=> $this->filterErrorMessage($data['errors'])
				);
			}
			$pxml = json_decode($data['response']);
			if (isset($pxml->data->records)) {
				if (sizeof($pxml->data->records) > 0)
					$this->warn = array('type' => 'author', 'msg' => 'Warn: Found more than one user id with nickname ' . $author);
				foreach ( $pxml->data->records as $record ) {
					$this->content = $record->userId;
					break;
				}
			}
			else{
				$this->errors[] = array('type' => 'author', 'msg' => 'User id not found');
			}
		}
	}
}