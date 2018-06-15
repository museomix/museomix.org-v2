<?php namespace flow\cache;
if ( ! defined( 'WPINC' ) ) die;

use flow\db\FFDB;
use flow\db\FFDBManager;
use flow\settings\FFSettingsUtils;
use flow\social\FFBaseFeed;
use flow\social\FFFeedUtils;
use flow\settings\FFGeneralSettings;
use la\core\social\LAFeedWithComments;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 *
 * @property \flow\settings\FFStreamSettings stream
 */
class FFCacheManager implements FFCache{
	/** @var  FFDBManager */
	protected $db;
	private $force;
	private $feeds;
	private $stream;
	private $hash = '';
	private $errors = array();
	
	function __construct($context = null, $force = false){
		$this->force = $force;
		$this->db = $context['db_manager'];
	}
	
	/**
	 * @param array $feeds
	 * @param bool $disableCache
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function posts($feeds, $disableCache){
		if (isset($_REQUEST['clean']) && $_REQUEST['clean']) $this->db->clean();
		if (isset($_REQUEST['clean-stream']) && $_REQUEST['clean-stream']) $this->db->clean(array($this->stream->getId()));
		
		$this->feeds = $feeds;
		if ($this->force){
			$hasNewItems = false;
			$this->hash = time();
			/** @var FFBaseFeed $feed */
			foreach ( $feeds as $feed_id => $feed ) {
				try{
					if ($this->expiredLifeTime($feed_id)) {
						$exist_feed_ids = $this->db->getIdPosts($feed_id);
						
						$posts = $feed->posts(empty($exist_feed_ids));
						
						$errors = $feed->errors();
						$countGotPosts = sizeof( $posts );
						$criticalError = ($countGotPosts == 0 && sizeof($errors) > 0 && $feed->hasCriticalError());
						$status = array('last_update' => $criticalError ? 0 : time(), 'errors' => $errors, 'status' => (int)(!$criticalError));
						
						if (!$criticalError){
							list($new_posts, $existed_posts) = $this->separation($exist_feed_ids, $posts);
							$countPosts4Insert = sizeof($new_posts);
							if (FFDB::beginTransaction()){
								if ($countPosts4Insert > 0) {
									$hasNewItems = true;
									$smart_order = 0;
									usort( $new_posts, array( $this, 'compareByTime' ) );
									foreach ( $new_posts as $post ) {
										$post->smart_order = $smart_order;
										$smart_order++;
									}
									$this->db->setSmartOrder($feed_id, $countPosts4Insert);
								}

								if ('facebook' == $feed->getType()){
									if ($hasNewItems) {
										$this->save( $feed, $new_posts);
										$this->db->setRandomOrder($feed_id);
									}
									$this->updateAdditionalInfo($existed_posts);
								}
								else {
									$this->save( $feed, $posts);
									$this->db->setRandomOrder($feed_id);
								}
							}
						}
						if (isset($feed->feed->system_enabled) && $feed->feed->system_enabled != (int)!$criticalError) {
							$this->db->systemDisableSource($feed_id, (int)!$criticalError);
						}
						$this->setCacheInfo($feed_id, $status);
						FFDB::commit();
					}
				}
				catch(\Exception $e){
					FFDB::rollback();
					$hasNewItems = false;
					$errors = array();
					$errors[] = array(
						'type' => $feed->getType(),
						'message' => $e->getMessage(),
						'code' => $e->getCode()
					);
					$status = array('last_update' => 0, 'errors' => $errors, 'status' => 0);
					$this->db->systemDisableSource($feed->id(), (int)false);
					$this->setCacheInfo($feed_id, $status);
					FFDB::commit();
				}
			}
			
			if ($hasNewItems){
				$this->removeOldRecords();
				FFDB::commit();
			}
			
			FFDB::rollbackAndClose();
			return array();
		} else {
			if (empty($_REQUEST['hash']) || $disableCache){
				$this->force = true;
				$_REQUEST['force'] = true;
				$this->posts($feeds, $disableCache);
				unset($_REQUEST['force']);
				$_REQUEST['hash'] = $this->hash();
			}
			return $this->get();
		}
	}
	
	public function hash(){
		return $this->encodeHash($this->hash);
	}

	public function transientHash($streamId){
		$hash = $this->db->getLastUpdateHash($streamId);
		return (false !== $hash) ? $this->encodeHash($hash) : '';
	}

	public function errors(){
		return $this->errors;
	}

	public function moderate(){
	}

	/**
	 * @param \flow\settings\FFStreamSettings $stream
	 * @param bool $moderation
	 *
	 * @return void
	 */
	public function setStream($stream, $moderation = false) {
		$this->stream = $stream;
	}

	protected function getGetFields(){
		$select  = "post.post_id as id, post.post_type as type, post.user_nickname as nickname, ";
		$select .= "post.user_screenname as screenname, post.user_pic as userpic, ";
		$select .= "post.post_timestamp as system_timestamp, ";
		$select .= "post.location as location, ";
		$select .= "post.post_text as text, post.user_link as userlink, post.post_permalink as permalink, ";
		$select .= "post.image_url, post.image_width, post.image_height, post.media_url, post.media_type, ";
		$select .= "post.user_bio, post.user_counts_media, post.user_counts_follows, post.user_counts_followed_by, ";
		$select .= "post.media_width, post.media_height, post.post_header, post.post_source, post.post_additional, post.feed_id, ";
		$select .= "post.carousel_size ";
		return $select;
	}

	protected function getGetFilters(){
		$args[] = FFDB::conn()->parse('stream.stream_id = ?s', $this->stream->getId());
		$args[] = FFDB::conn()->parse('cach.enabled = 1');
		if ($this->stream->showOnlyMediaPosts()) $args[] = "post.image_url IS NOT NULL";
		if (isset($_REQUEST['hash']))
			if (isset($_REQUEST['recent'])){
				$args[] = FFDB::conn()->parse('post.creation_index > ?s', $this->decodeHash($_REQUEST['hash']));
			} else {
				$args[] = FFDB::conn()->parse('post.creation_index <= ?s', $this->decodeHash($_REQUEST['hash']));
			}
		return $args;
	}

	protected function getOnlyNew($moderation){
		return array();
	}

    /**
     * @return mixed
     */
    private function get(){
	    $where = implode(' AND ', $this->getGetFilters());

	    $order = 'post.smart_order, post.post_timestamp DESC';
	    if ($this->stream->order() == FF_RANDOM_ORDER)  $order = 'post.rand_order, post.post_id';
	    if ($this->stream->order() == FF_BY_DATE_ORDER) $order = 'post.post_timestamp DESC, post.post_id';

	    $moderation = array();
	    foreach ( $this->stream->getAllFeeds() as $feed ) {
		    $moderation[$feed['id']] = FFSettingsUtils::YepNope2ClassicStyleSafe($feed, 'mod', false);
	    }

	    $limit = null;
	    $offset = null;
	    $result = array();
	    if (!isset($_REQUEST['recent'])){
		    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 0;
		    $limit = $this->stream->getCountOfPostsOnPage();
		    $offset = $page * $limit;

		    if ($page == 0){
			    $result = $this->getOnlyNew($moderation);
			    if (!isset($_REQUEST['countOfPages'])){
				    $totalCount = $this->db->countPostsIf($where);
				    if ($totalCount === false) $totalCount = 0;
				    $countOfPages = ($limit > $totalCount) ? 1 : ceil($totalCount / $limit);
				    $_REQUEST['countOfPages'] = $countOfPages;
			    }
		    }
	    }
	    $resultFromDB = $this->db->getPostsIf($this->getGetFields(), $where, $order, $offset, $limit);
	    if (false === $resultFromDB) $resultFromDB = array();

	    foreach ( $resultFromDB as $row ) {
		    $result[] = $this->buildPost($row, $moderation[$row['feed_id']]);
	    }

	    //$this->errors = FFDB::getError($this->db->cache_table_name, $this->db->streams_sources_table_name, $this->stream->getId());
	    $this->hash = $this->db->getHashIf($where);
	    FFDB::close();
	    return $result;
    }

	/**
	 * @param $feed
	 * @param $value
	 *
	 * @throws \Exception
	 * @return void
	 */
	private function save( $feed, $value ) {
		if (sizeof($value) > 0) {
		    $only4insertPartOfSqlTemplate =
			    FFDB::conn()->parse('`creation_index`=?i', $this->hash);

		    $status = $this->getDefaultStreamStatus($feed);
	        foreach ($value as $id => $post){
		        $feed_id = $post->feed_id;

		        $imagePartOfSql = (isset($post->img) && sizeof($post->img) == 3) ?
			        FFDB::conn()->parse('`image_url`=?s, `image_width`=?i, `image_height`=?i,',
			        $post->img['url'], $post->img['width'], $post->img['height']) : '';
		        $mediaPartOfSql = (isset($post->media) && sizeof($post->media) == 4) ?
			        FFDB::conn()->parse('`media_url`=?s, `media_width`=?i, `media_height`=?i, `media_type`=?s,',
			        $post->media['url'], $post->media['width'], $post->media['height'], $post->media['type']) : '';
				
				$only4insertPartOfSql = FFDB::conn()->parse('?p, ?u', $only4insertPartOfSqlTemplate, array(
					'feed_id' => $feed_id,
					'post_id' => $post->id,
					'post_type' => $post->type,
					'post_permalink' => $post->permalink,
					'user_nickname' => $post->nickname,
					'user_screenname' => $post->screenname,
					'user_pic' => $post->userpic,
					'user_bio' => (isset($post->userMeta->bio) ? json_encode( $post->userMeta->bio . (isset($post->userMeta->website) ? ' ' . $post->userMeta->website : '')) : ''),
					'user_counts_media' => isset($post->userMeta->counts->media) ? $post->userMeta->counts->media : 0,
					'user_counts_follows' => isset($post->userMeta->counts->follows) ? $post->userMeta->counts->follows : 0,
					'user_counts_followed_by' => isset($post->userMeta->counts->followed_by) ? $post->userMeta->counts->followed_by : 0,
					'user_link' => $post->userlink,
					'post_source' => isset($post->source) ? $post->source : '',
					'location' => isset($post->location) ? json_encode($post->location) : '',
					'smart_order' => $post->smart_order,
					'post_status' => $status
				));
				
				if (!isset($post->additional)) $post->additional = array();
				$common = array(
					'post_header' => @FFDB::conn()->conn->real_escape_string(trim($post->header)),
					'post_text'   => $this->prepareText($post->text),
					'post_timestamp' => FFFeedUtils::correctionTimeZone($post->system_timestamp),
					'post_additional' => json_encode($post->additional),
					'carousel_size' => 0
				);
				
				if (isset($post->carousel) && sizeof($post->carousel) > 1){
					$this->db->deleteCarousel4Post($feed_id, $post->id, $post->type);
					foreach ($post->carousel as $media){
						$mediaPartOfSql4carousel = FFDB::conn()->parse('`media_url`=?s, `media_width`=?i, `media_height`=?i, `media_type`=?s,', 
							$media['url'], $media['width'], $media['height'], $media['type']);
						$this->db->addCarouselMedia($feed_id, $post->id, $media['type'], $mediaPartOfSql4carousel);
					}
					$common['carousel_size'] = sizeof($post->carousel);
				}
				
				if (isset($post->comments) && sizeof($post->comments) > 1){
					foreach ($post->comments as $comment){
						$this->db->addComments($post->id, $comment);
					}
				}
				
				$this->db->addOrUpdatePost($only4insertPartOfSql, $imagePartOfSql, $mediaPartOfSql, $common);
			}
		}
	}
	
	private function updateAdditionalInfo($posts){
		$this->db->updateAdditionalInfo($posts);
	}
	
	/**
	 * @param $feedId
	 *
	 * @return bool
	 */
	private function expiredLifeTime($feedId){
		if (isset($_REQUEST['force']) && $_REQUEST['force']) return true;
		
		$sql = FFDB::conn()->parse('SELECT `cach`.`feed_id` FROM ?n `cach` WHERE `cach`.`feed_id`=?s AND (`cach`.last_update + `cach`.cache_lifetime * 60) < UNIX_TIMESTAMP()', $this->db->cache_table_name, $feedId);
		return (false !== FFDB::conn()->getOne($sql));
	}

	/**
	 * @param array $row
	 * @param bool $moderation
	 *
	 * @return stdClass
	 */
	protected function buildPost($row, $moderation = false){
		$post = new \stdClass();
		$post->id = $row['id'];
		$post->type = $row['type'];
		$post->nickname = $row['nickname'];
		$post->screenname = $row['screenname'];
		$post->userpic = $row['userpic'];
		$post->system_timestamp = $row['system_timestamp'];
		$post->timestamp = FFFeedUtils::classicStyleDate($row['system_timestamp'], FFGeneralSettings::get()->dateStyle());
		$post->text = stripslashes($row['text']);
		$post->location = json_decode($row['location']);
		$post->userlink = $row['userlink'];
		$post->user_bio = json_decode($row['user_bio']);
		$post->user_counts_media = $row['user_counts_media'];
		$post->user_counts_follows = $row['user_counts_follows'];
		$post->user_counts_followed_by = $row['user_counts_followed_by'];
		$post->permalink = $row['permalink'];
		$post->header = stripslashes($row['post_header']);
		$post->mod = $moderation;
		$post->feed = $row['feed_id'];
		$post->with_comments = $this->feeds[$post->feed] instanceof LAFeedWithComments;
		if (!empty($row['post_source'])) $post->source = $row['post_source'];
		if ($row['image_url'] != null){
			$url = $row['image_url'];
			$width = $row['image_width'];
			$tWidth = $this->stream->getImageWidth();
			$height = FFFeedUtils::getScaleHeight($tWidth, $width, $row['image_height']);
			if (($post->type != 'posts') && $this->db->getGeneralSettings()->useProxyServer() && ($width + 50) > $tWidth) $url = FFFeedUtils::proxy($url, $tWidth);
			if (($row['image_width'] == '-1') && ($row['image_height'] == '-1')) {
				$post->img = array('url' => $url, 'type' => 'image');
			}
			else $post->img = array('url' => $url, 'width' => $tWidth, 'height' => $height, 'type' => 'image');
			$post->media = $post->img;

			if ($post->type == 'twitter') {
				$post->text = str_replace('%WIDTH%', $post->img['width'], $post->text);
				$post->text = str_replace('%HEIGHT%', $post->img['height'], $post->text);
			}
		}
		if ($row['media_url'] != null){
			$post->media = array('url' => $row['media_url'], 'width' => $row['media_width'], 'height' => $row['media_height'], 'type' => $row['media_type']);
		}
		$post->additional = json_decode($row['post_additional']);
		$post->carousel_size = $row['carousel_size'];
		return $post;
	}

	/**
	 * @param $feedId
	 * @param $status
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	private function setCacheInfo( $feedId, $status ) {
		if (isset($status['errors']) && !is_string($status['errors'])) {
			$status['errors'] = serialize($status['errors']);
		}
		if ( false == $this->db->setCacheInfo($feedId, $status)) {
			throw new \Exception('Can`t save the cache info');
		}
	}
	
	private function separation( $exist_feed_ids, $posts ){
		$existed_posts = array();
		foreach ( $exist_feed_ids as $id ) {
			if (isset($posts[$id])) {
				$existed_posts[] = $posts[$id];
				unset($posts[$id]);
			}
		}
		return array($posts, $existed_posts);
	}
	
	private function encodeHash($hash){
		if (!empty($hash)){
			$postfix  = hash('md5', serialize($this->stream->original()));
			$postfix .= hash('md5', serialize(FFGeneralSettings::get()->original()));
			$postfix .= hash('md5', serialize(FFGeneralSettings::get()->originalAuth()));
			return $hash . "." . $postfix;
		}
		return $hash;
	}

	protected function decodeHash($hash){
		$pos = strpos($hash, ".");
		if ($pos === false) return $hash;
		if ($pos == 0) return '';
		return substr($hash, 0, $pos);
	}

	private function compareByTime($a, $b) {
		$a_system_date = $a->system_timestamp;
		$b_system_date = $b->system_timestamp;
		return ($a_system_date == $b_system_date) ? 0 : ($a_system_date < $b_system_date) ? 1 : -1;
	}

	private function getDefaultStreamStatus($feed) {
		if ($this->moderation($feed)){
			if (defined('FF_SOFT_MODERATION_POLICY') && FF_SOFT_MODERATION_POLICY){
				return 'approved';
			}
			return 'new';
		}
		return 'approved';
	}

	private function moderation($feed) {
		if (isset($feed->feed->mod)){
			return FFSettingsUtils::YepNope2ClassicStyle($feed->feed->mod, false);
		}
		return false;
	}

	private function removeOldRecords() {
		$settings = $this->db->getGeneralSettings();
		$this->db->removeOldRecords($settings->getCountOfPostsByFeed());
	}

	private function prepareText( $text ) {
		$text = str_replace("\r\n", "<br>", $text);
		$text = str_replace("\n", "<br>", $text);
		$text = trim($text);
		return @FFDB::conn()->conn->real_escape_string($text);
	}
}