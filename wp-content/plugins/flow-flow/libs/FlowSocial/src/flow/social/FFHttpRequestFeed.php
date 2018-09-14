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
abstract class FFHttpRequestFeed extends FFBaseFeed{
	private $header = false;
	protected $url;

	function __construct( $type ) {
		parent::__construct( $type );
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function onePagePosts() {
		$result = array();
		$data = $this->getFeedData( $this->getUrl(), 200, $this->header );
		if ( sizeof( $data['errors'] ) > 0 ) {
			$message = $this->filterErrorMessage($data['errors']);
			$this->errors[] = array(
				'type'    => $this->getType(),
				'message' => $message,
				'url' => $this->getUrl()
			);
			throw new \Exception($message);
		}
		foreach ( $this->items( $data['response'] ) as $item ) {
			$item = $this->prepareItem($item);
			if (is_object( $item ) && $this->isSuitableOriginalPost( $item )) {
				$post                   = $this->prepare( $item );
				$post->id               = (string) $this->getId( $item );
				$post->type             = $this->getType();
				if ($this->isNewPost($item)) {
					$post->header           = (string) $this->getHeader( $item );
					$post->nickname         = '';
					$post->screenname       = (string) $this->getScreenName( $item );
					$post->userpic          = $this->getProfileImage( $item );
					$post->system_timestamp = $this->getSystemDate( $item );
					$post->text             = (string) $this->getContent( $item );
					$post->userlink         = (string) $this->getUserlink( $item );
					$post->permalink        = (string) $this->getPermalink( $item );
					if ( $this->showImage( $item ) ) {
						$post->img   = $this->getImage( $item );
						$post->media = $this->getMedia( $item );
						$post->carousel = $this->getCarousel( $item );
					}
				}
				$post->additional       = $this->getAdditionalInfo( $item );
				$post->comments 		= $this->getComments( $item );
				
				$post = $this->customize( $post, $item );
				if ( $this->isSuitablePost( $post ) ) {
					$result[$post->id] = $post;
				}
			}
		}
		return $result;
	}

	protected function getUrl() {
		return $this->url;
	}

    protected abstract function items($request);
    protected abstract function getId($item);
    protected abstract function getHeader($item);
    protected abstract function getScreenName($item);
    protected abstract function getProfileImage($item);
    protected abstract function getSystemDate($item);
    protected abstract function getContent($item);
    protected abstract function getUserlink($item);
    protected abstract function getPermalink($item);

    protected abstract function showImage($item);
    protected abstract function getImage($item);
    protected abstract function getMedia($item);

	/**
	 * @param $item
	 * @return \stdClass
	 */
	protected function prepareItem($item){
		return $item;
	}

	/**
	 * @param $item
	 * @return \stdClass
	 */
	protected function prepare($item){
		$post = new \stdClass();
		$post->feed_id = $this->id();
		$post->smart_order = 0;
		return $post;
	}

	/**
	 * @param $item
	 * @return array
	 */
	protected function getAdditionalInfo( $item ){
		return array();
	}

	/**
     * @param \stdClass $post
     * @param $item
     *
     * @return \stdClass
     */
    protected function customize($post, $item){
        return $post;
    }

	/**
	 * @param boolean $header
	 */
	protected function setHeader( $header ) {
		$this->header = $header;
	}

	/**
	 * @param $post
	 * @return bool
	 */
	protected function isSuitableOriginalPost( $post ) {
		return true;
	}

	/**
	 * @param $post
	 * @return bool
	 */
	protected function isNewPost( $post ) {
		return true;
	}

	protected function getCarousel( $item ){
		return array();
	}
	
	protected function getComments( $item ){
		return array();
	}
}