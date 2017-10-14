<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;

use \stdClass;
use flow\cache\FFImageSizeCacheManager;
use flow\settings\FFGeneralSettings;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
abstract class FFBaseFeed implements FFFeed{
	/** @var stdClass */
	public $feed;

    private $id;
    /** @var FFImageSizeCacheManager */
    protected $cache;
    private $count;
    private $imageWidth;
    private $useProxyServer;
	private $type;
	private $filterByWords;
	private $criticalError = true;
	/** @var FFGeneralSettings */
	protected $options;
    protected $errors;
	protected $context;

	function __construct( $type ) {
		$this->type = $type;
	}

	public function getType(){
		return $this->type;
	}

	public function id(){
        return $this->id;
    }

    public function getCount(){
        return $this->count;
    }

    /**
     * @return int
     */
    public function getImageWidth(){
        return $this->imageWidth;
    }

    /**
     * @return int
     */
    public function getAllowableWidth(){
        return 200;
    }

	/**
	 * @param $context
	 * @param FFGeneralSettings $options
	 * @param $feed
	 *
	 * @return void
	 */
    public final function init($context, $options, $feed){
	    $this->context = $context;
	    $this->options = $options;
	    $this->feed = $feed;

        $this->id = $feed->id;
        $this->errors = array();
        $this->useProxyServer = $options->useProxyServer();
        $this->count = isset($feed->posts) ? intval($feed->posts) : 10;
        $this->imageWidth = defined('FF_MAX_IMAGE_WIDTH') ? FF_MAX_IMAGE_WIDTH : 300;
        $this->cache = FFImageSizeCacheManager::get();
	    if (isset($feed->{'filter-by-words'})) {
		    $this->filterByWords =  explode(',', $feed->{'filter-by-words'});
		    if ($this->filterByWords === false) $this->filterByWords = array();
	    } else {
		    $this->filterByWords = array();
	    }
    }

	public final function posts() {
		$result = array();
		try {
			if ($this->beforeProcess()) {
				$this->deferredInit($this->options, $this->feed);
				if (sizeof($this->errors) == 0){
					do {
						$result += $this->onePagePosts();
					} while ($this->nextPage($result));
					return $this->afterProcess($result);
				}
			}
		}
		catch (\Exception $e){
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
		}
		$this->criticalError = true;
		return $result;
	}

	/**
	 * @param FFGeneralSettings $options
	 * @param stdClass $feed
	 *
	 * @return void
	 */
	protected abstract function deferredInit($options, $feed);
	protected abstract function onePagePosts( );

    /**
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

	/**
	 * @param $url
	 * @param $width
	 * @param $height
	 * @param bool $scale
	 *
	 * @return array
	 */
    protected function createImage($url, $width = null, $height = null, $scale = true){
    	if ($width != -1 && $height != -1) {
		    if ($width == null || $height == null){
			    $size = $this->cache->size($url);
			    $width = $size['width'];
			    $height = $size['height'];
		    }
		    if ($scale){
			    $tWidth = $this->getImageWidth();
			    return array('url' => $url, 'width' => $tWidth, 'height' => FFFeedUtils::getScaleHeight($tWidth, $width, $height));
		    }
	    }
	    return array('url' => $url, 'width' => $width, 'height' => $height);
    }

	protected function createMedia($url, $width = null, $height = null, $type = 'image', $scale = false){
		if ($type == 'html'){
			return array('type' => $type, 'html' => $url);
		}
		if ($width == null || $height == null){
			$size = $this->cache->size($url);
			$width = $size['width'];
			$height = $size['height'];
		}
		if ($type == 'image' && $scale == true && $width > 600){
			$height = FFFeedUtils::getScaleHeight(600, $width, $height);
			$width = 600;
		}
		return array('type' => $type, 'url' => $url, 'width' => $width, 'height' => $height);
	}

    /**
     * @param string $link
     * @param string $name
     * @param mixed $image
     * @param mixed $width
     * @param mixed $height
     * @return array
     */
    protected function createAttachment($link, $name, $image = null, $width = null, $height = null){
        if ($image != null){
            if (is_string($image)) $image = $this->createImage($image, $width, $height);
            if ($image['width'] > $this->getAllowableWidth())
                return array( 'type' => 'article', 'url' => $link, 'displayName' => $name, 'image' => $image);
        }
        return array( 'type' => 'article', 'url' => $link, 'displayName' => $name);
    }

	/**
	 * @param stdClass $post
	 * @return bool
	 */
	protected function isSuitablePost($post){
		if ($post == null) return false;
		foreach ( $this->filterByWords as $word ) {
			$word = strtolower($word);
			$firstLetter = substr($word, 0, 1);
			if ($firstLetter !== false){
				switch ($firstLetter) {
					case '@':
						$word = substr($word, 1);
						if ((strpos(strtolower($post->screenname), $word) !== false) || (strpos(strtolower($post->nickname), $word) !== false)) {
							return false;
						}
						break;
					case '#':
						$word = substr($word, 1);
						if (strpos(strtolower($post->permalink), $word) !== false) {
							return false;
						}
						break;
					default:
						if (!empty($word) && ((strpos(strtolower($post->text), $word) !== false) || (isset($post->header) && strpos(strtolower($post->header), $word) !== false))) {
							return false;
						}
				}
			}
		}
		return true;
	}

	/**
	 * @return bool
	 */
	protected function beforeProcess(){
		return (sizeof($this->errors) == 0);
	}

    /**
     * @param $result array
     * @return array
     */
    protected function afterProcess($result){
        $this->cache->save();
	    $this->criticalError = empty($result) && sizeof($this->errors) > 0;
        return $result;
    }

    public function useCache(){
        return true;
    }

	public function hasCriticalError() {
		return $this->criticalError;
	}

	/**
	 * @param array $result
	 * @return bool
	 */
	protected function nextPage($result){
		return false;
	}

	protected function getFeedData($url, $timeout = 60, $header = false, $log = true){
		/** @var LADBManager $db */
		$db = $this->context['db_manager'];
		$use = $db->getGeneralSettings()->useCurlFollowLocation();
		$useIpv4 = $db->getGeneralSettings()->useIPv4();
		return FFFeedUtils::getFeedData($url, $timeout, $header, $log, $use, $useIpv4);
	}

	protected function filterErrorMessage($message){
		return FFFeedUtils::filter_error_message($message);
	}
} 