<?php  namespace flow\db;

use flow\settings\FFGeneralSettings;
use flow\settings\FFSettingsUtils;
use flow\LABase;
use flow\db\FFDB;

if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
abstract class LADBManager {
	public $table_prefix;
	public $option_table_name;
	public $posts_table_name;
	public $cache_table_name;
	public $streams_table_name;
	public $image_cache_table_name;
	public $streams_sources_table_name;
	public $snapshot_table_name;
	public $comments_table_name;
	public $post_media_table_name;

	protected $context;
	protected $plugin_slug;
	protected $plugin_slug_down;
	protected $init = false;
	protected $sources = null;
	protected $streams = null;

	function __construct($context) {
		$this->context = $context;
		$this->table_prefix = $context['table_name_prefix'];
		$this->plugin_slug = $context['slug'];
		$this->plugin_slug_down = $context['slug_down'];

		$this->option_table_name = $this->table_prefix . 'options';
		$this->posts_table_name = $this->table_prefix . 'posts';
		$this->cache_table_name = $this->table_prefix . 'cache';
		$this->streams_table_name = $this->table_prefix . 'streams';
		$this->image_cache_table_name = $this->table_prefix . 'image_cache';
		$this->streams_sources_table_name = $this->table_prefix . 'streams_sources';
		$this->snapshot_table_name= $this->table_prefix . 'snapshots';
		$this->comments_table_name= $this->table_prefix . 'comments';
		$this->post_media_table_name = $this->table_prefix . 'post_media';
	}
	
	public final function dataInit($only_enable = false, $safe = false){
		$this->init = true;
		
		if ($safe && !FFDB::existTable($this->streams_sources_table_name)) {
			$this->sources = array();
			$this->streams = array();
			return;
		}
		
		$connections = FFDB::conn()->getIndMultiRow('stream_id', 'select `stream_id`, `feed_id` from ?n order by `stream_id`', $this->streams_sources_table_name);
		$this->sources = FFDB::sources($this->cache_table_name, $this->streams_sources_table_name, null, $only_enable);
		$this->streams = FFDB::streams($this->streams_table_name);
		foreach ( $this->streams as &$stream ) {
			$stream = (array)FFDB::unserializeStream($stream);
			if (!isset($stream['feeds'])) $stream['feeds'] = array();
			$stream['status'] = '1';
			if (isset($connections[$stream['id']])){
				foreach ($connections[$stream['id']] as $source){
					if (isset($this->sources[$source['feed_id']])){
						$full_source = $this->sources[$source['feed_id']];
						$stream['feeds'][] = $full_source;
						if (isset($full_source['status']) && $full_source['status'] == 0) $stream['status'] = '0';
					}
				}
			}
		}
	}

	/**
	 * Get stream settings by id endpoint
	 */
	public final function get_stream_settings(){
		$id = $_GET['stream-id'];
		$this->dataInit();
		die(json_encode( $this->streams[$id]));
	}
	
	/**
	 * Create stream endpoint
	 */
	public final function create_stream(){
		$stream = $_POST['stream'];
		$stream = (object)$stream;
		try{
			FFDB::beginTransaction();
			if (false !== ($max = FFDB::maxIdOfStreams($this->streams_table_name))){
				$newId = (string) $max + 1;
				$stream->id = $newId;
				$stream->feeds = isset($stream->feeds) ? $stream->feeds : json_encode(array());
				$stream->name = isset($stream->name) ? $stream->name : '';
				FFDB::setStream($this->streams_table_name, $this->streams_sources_table_name, $newId, $stream);
				$response = json_encode(FFDB::getStream($this->streams_table_name, $newId));
				FFDB::commit();
				
				$this->refreshCache($newId);
				echo $response;
			}
			else echo false;
		}catch (\Exception $e){
			FFDB::rollbackAndClose();
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
		FFDB::close();
		die();
	}
	
	/**
	 * Save sources endpoint
	 */	
	public final function save_sources_settings(){
		if (isset($_POST['model'])){
			$model = $_POST['model'];
			$model['id'] = 1; // DON'T DELETE, ID is always 1, this is needed to detect if model was saved
			
			if (isset($_POST['model']['feeds_changed'])){
				foreach ( $_POST['model']['feeds_changed'] as $feed ) {
					switch ($feed['state']) {
						case 'changed':
							$source = $_POST['model']['feeds'][ $feed['id'] ];
							$changed_content = $this->changedContent($source);
							if ($changed_content) $this->cleanFeed($feed['id']);
							$this->modifySource( $source, $changed_content );
							$this->refreshCache4Source($feed['id'], false);
							break;
						case 'created':
							$this->modifySource($_POST['model']['feeds'][$feed['id']]);
							$this->refreshCache4Source($feed['id'], true);
							break;
						case 'reset_cache':
							$this->cleanFeed($feed['id']);
							$this->refreshCache4Source($feed['id'], true);
							break;
						case 'deleted':
							$this->deleteFeed($feed['id']);
							break;
					}
				}
			}
			
			$this->dataInit();
			$sources = $this->sources();
			if (isset($model['feeds'])){				
				foreach ( $model['feeds'] as &$source ) {
					if (array_key_exists($source['id'], $sources)){
						$source = $sources[$source['id']];
					}
				}
			}
			echo json_encode($model);
			die();
		}
		die(1);
	}
	
	/**
	 * Save stream endpoint
	 */
	public final function save_stream_settings(){
		$stream = $_POST['stream'];
		$stream = (object)$stream;
		$id = $stream->id;
		try{
			FFDB::beginTransaction();
			$stream->last_changes = time();
			FFDB::setStream($this->streams_table_name, $this->streams_sources_table_name, $id, $stream);
			
			$this->generateCss($stream);
			
			echo json_encode($stream);
			FFDB::commit();
			
		}catch (Exception $e){
			FFDB::rollbackAndClose();
			error_log('save_stream_settings error:');
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
		}
		FFDB::close();
		die();
	}
	
	/**
	 * Save general settings endpoint
	 */
	public final function ff_save_settings_fn() {
		$serialized_settings = $_POST['settings']; // param1=foo&param2=bar
		$settings = array();
		parse_str( $serialized_settings, $settings );
		
		try{
			$activated = $this->activate($settings);
			$force_load_cache = $this->clean_cache($settings);
			
			FFDB::beginTransaction();
			
			$settings = $this->saveGeneralSettings($settings);
			
			FFDB::commit();
			
			if ($force_load_cache) {
				$this->refreshCache(null, $force_load_cache);
			}
			
			$responce = array(
				'settings' => $settings, 
				'activated' => $activated
			);
			$this->customizeResponce($responce);
			
			echo json_encode( $responce );
		}catch (\Exception $e){
			error_log('ff_save_settings_fn error:');
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
			FFDB::rollbackAndClose();
			die($e->getMessage());
		}
		FFDB::close();
		die();
	}
	

	public function modifySource($source, $changed_content = true){
		$id = $source['id'];
		$enabled = $source['enabled'];
		$cache_lifetime = $source['cache_lifetime'];
		$status = isset($source['status']) ? intval($source['status']) : 0;
		unset($source['id']);
		unset($source['enabled']);
		unset($source['last_update']);
		unset($source['cache_lifetime']);
		if (isset($source['errors'])) unset($source['errors']);
		if (isset($source['status'])) unset($source['status']);
		if (isset($source['system_enabled'])) unset($source['system_enabled']);
		
		$in = array(
				'settings' => serialize((object)$source),
				'enabled' => (int)FFSettingsUtils::YepNope2ClassicStyle($enabled, true),
				'system_enabled' => (int)FFSettingsUtils::YepNope2ClassicStyle($enabled, true),
				'last_update' => 0,
				'changed_time' => time(),
				'cache_lifetime' => $cache_lifetime,
				'status' => $status
		);
		$up = array(
				'settings' => serialize((object)$source),
				'enabled' => (int)FFSettingsUtils::YepNope2ClassicStyle($enabled, true),
				'system_enabled' => (int)FFSettingsUtils::YepNope2ClassicStyle($enabled, true),
				'cache_lifetime' => $cache_lifetime
		);
		if ($changed_content) $up['last_update'] =  '0';
		try {
			if ( false === FFDB::conn()->query( 'INSERT INTO ?n SET `feed_id`=?s, ?u ON DUPLICATE KEY UPDATE ?u',
					$this->cache_table_name, $id, $in, $up ) ) {
						throw new \Exception();
					}
					FFDB::commit();
		}
		catch (\Exception $e){
			FFDB::rollback();
		}
	}
	
	public function changedContent( $source ) {
		$sources = FFDB::sources($this->cache_table_name, $this->streams_sources_table_name);
		$old = $sources[$source['id']];
		foreach ( $source as $key => $value ) {
			$old_value = $old[$key];
			if ($key == 'status' || $key == 'enabled' || $key == 'posts' || $key == 'errors' || $key == 'last_update' || 
					$key == 'cache_lifetime' || $key == 'mod' || $key == 'posts') continue;
			if ($old_value !== $value) {
				return true;
			}
		}
		return false;
	}
	
	public function getGeneralSettings(){
		return new FFGeneralSettings($this->getOption('options', true), $this->getOption('fb_auth_options', true));
	}

	
	public function getOption( $optionName, $serialized = false, $lock_row = false ) {
		$options = FFDB::getOption($this->option_table_name, $this->plugin_slug_down . '_' . $optionName, $serialized, $lock_row);
		if ($optionName == 'options') {
			$options['general-uninstall'] = get_option($this->plugin_slug_down . '_general_uninstall', 'nope');
		}
		return $options;
	}

	public function setOption($optionName, $optionValue, $serialized = false, $cached = true){
		FFDB::setOption($this->option_table_name, $this->plugin_slug_down . '_' . $optionName, $optionValue, $serialized, $cached);
	}

	public function deleteOption($optionName){
		FFDB::deleteOption($this->option_table_name, $this->plugin_slug_down . '_' . $optionName);
	}
	
	public function streams(){
		if ($this->init) return $this->streams;
		throw new \Exception('Don`t init data manager');
	}
	
	public function countFeeds(){
		return FFDB::countFeeds($this->cache_table_name);
	}
	
	public function getStream($streamId){
		$stream = $this->streams[$streamId];
		return $stream;
	}
	
	public function delete_stream(){
		try{
			FFDB::beginTransaction();
			$id = $_GET['stream-id'];
			FFDB::deleteStream($this->streams_table_name, $this->streams_sources_table_name, $id);
			do_action('ff_after_delete_stream', $id);
			FFDB::commit();
		}catch (Exception $e){
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
			FFDB::rollbackAndClose();
			die(false);
		}
		die();
	}
	
	public function canCreateCssFolder(){
		$dir = WP_CONTENT_DIR . '/resources/' . $this->context['slug'] . '/css';
		if(!file_exists($dir)){
			return mkdir($dir, 0777, true);
		}
		return true;
	}
	
	public function generateCss($stream){
		$dir = WP_CONTENT_DIR . '/resources/' . $this->context['slug'] . '/css';
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);
		}
		
		$filename = $dir . "/stream-id" . $stream->id . ".css";
		ob_start();
		include($this->context['root']  . 'views/stream-template-css.php');
		$output = ob_get_clean();
		$a = fopen($filename, 'w');
		fwrite($a, $output);
		fclose($a);
		chmod($filename, 0644);
	}
	
	public function clone_stream(){
		$stream = $_REQUEST['stream'];
		$stream = (object)$stream;
		try{
			FFDB::beginTransaction();
			if (false !== ($count = FFDB::maxIdOfStreams($this->streams_table_name))) {
				$newId = (string) $count + 1;
				$stream->id = $newId;
				$stream->name = "{$stream->name} copy";
				$stream->last_changes = time();
				FFDB::setStream($this->streams_table_name, $this->streams_sources_table_name, $newId, $stream);
				
				$this->generateCss($stream);
				FFDB::commit();
				echo json_encode($stream);
			}
			else {
				throw new \Exception('Can`t get a new id for the clone stream');
			}
		}catch (Exception $e){
			FFDB::rollbackAndClose();
			error_log('clone_stream error:');
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
		}
		FFDB::close();
		die();
	}
	
	protected function saveGeneralSettings($settings){
		if (isset($settings['flow_flow_options']['general-uninstall'])){
			$general_uninstall_option_name = $this->plugin_slug_down . '_general_uninstall';
			$value = ($settings['flow_flow_options']['general-uninstall'] === 'yep') ? 'yep' : 'nope';
			if ( get_option( $general_uninstall_option_name) !== false ) {
				update_option( $general_uninstall_option_name, $value );
			}
			else {
				add_option( $general_uninstall_option_name, $value, '', 'no' );
			}
			unset($settings['flow_flow_options']['general-uninstall']);
		}
		
		$this->setOption('options', $settings['flow_flow_options'], true);
		return $settings;
	}
	
	protected abstract function customizeResponce(&$responce);
	
	protected abstract function clean_cache($options);
	
	protected function refreshCache($streamId, $force_load_cache = false){
		//TODO: anf: refact
		LABase::get_instance($this->context)->refreshCache($streamId, $force_load_cache);
	}

	protected function refreshCache4Source($id, $force_load_cache = false){
		//TODO: anf: refact
		$_REQUEST['feed_id'] = $id;
		LABase::get_instance($this->context)->processAjaxRequestBackground();
	}
	
	public function streamsWithStatus(){
		if (false !== ($result = self::streams())){
			return $result;
		}
		return array();
	}
	
	public function sources($only_enable = false){
		if ($this->init)  return $this->sources;
		throw new \Exception('Don`t init data manager');
	}
	
	//TODO: refact posts table does not have field with name stream_id 
	public function clean(array $streams = null){
		$partOfSql = $streams == null ? '' : FFDB::conn()->parse('WHERE `stream_id` IN (?a)', $streams);
		try{
			if (FFDB::beginTransaction()){
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->posts_table_name, $partOfSql);
				FFDB::conn()->query('DELETE FROM ?n', $this->image_cache_table_name);
				FFDB::commit();
			}
			FFDB::rollback();
		}catch (Exception $e){
			FFDB::rollbackAndClose();
		}
	}
	
	
	public function deleteFeed($feedId){
		try{
			if (FFDB::beginTransaction()){
				$partOfSql = FFDB::conn()->parse('WHERE `feed_id` = ?s', $feedId);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->posts_table_name, $partOfSql);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->post_media_table_name, $partOfSql);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->cache_table_name, $partOfSql);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->streams_sources_table_name, $partOfSql);
				FFDB::commit();
			}
			FFDB::rollback();
		}catch (\Exception $e){
			FFDB::rollbackAndClose();
		}
	}
	
	public function cleanFeed($feedId){
		try{
			if (FFDB::beginTransaction()){
				$partOfSql = FFDB::conn()->parse('WHERE `feed_id` = ?s', $feedId);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->posts_table_name, $partOfSql);
				FFDB::conn()->query('DELETE FROM ?n ?p', $this->post_media_table_name, $partOfSql);
				$this->setCacheInfo($feedId, array('last_update' => 0, 'status' => 0));
				FFDB::commit();
			}
			FFDB::rollback();
		}catch (Exception $e){
			FFDB::rollbackAndClose();
		}
	}
	
	public function cleanByFeedType($feedType){
		try{
			if (FFDB::beginTransaction()){
				$feeds = FFDB::conn()->getCol('SELECT DISTINCT `feed_id` FROM ?n WHERE `post_type` = ?s', $this->posts_table_name, $feedType);
				if (!empty($feeds)){
					FFDB::conn()->query("DELETE FROM ?n WHERE `feed_id` IN (?a)", $this->posts_table_name, $feeds);
					FFDB::conn()->query("DELETE FROM ?n WHERE `feed_id` IN (?a)", $this->post_media_table_name, $feeds);
					FFDB::commit();
				}
			}
			FFDB::rollback();
		}catch (Exception $e){
			FFDB::rollbackAndClose();
		}
	}
	
	public function addOrUpdatePost($only4insertPartOfSql, $imagePartOfSql, $mediaPartOfSql, $common){
		$sql = "INSERT INTO ?n SET ?p, ?p ?p ?u ON DUPLICATE KEY UPDATE ?u";
		if (false == FFDB::conn()->query($sql, $this->posts_table_name, $only4insertPartOfSql, $imagePartOfSql, $mediaPartOfSql, $common, $common)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function updateAdditionalInfo($posts){
		foreach ($posts as $post) {
			$sql = FFDB::conn()->parse('UPDATE ?n SET `post_additional` = ?s WHERE `post_id` = ?s AND `feed_id` = ?s AND `post_type` = ?s',
				$this->posts_table_name, json_encode($post->additional), $post->id, $post->feed_id, $post->type);
			if (false == FFDB::conn()->query($sql)){
				throw new \Exception(FFDB::conn()->conn->error);
			}
		}
	}
	
	public function getCarousel($feed_id, $post_id){
		$result = FFDB::conn(true)->getAll('SELECT `feed_id`, `post_id`, `media_url`, `media_width`, `media_height`, `media_type` FROM ?n WHERE `feed_id`=?s AND `post_id`=?s', 
			$this->post_media_table_name, $feed_id, $post_id);
		return $result;
	}
	
	public function addCarouselMedia($feed_id, $post_id, $post_type, $mediaPartOfSql4carousel){
		$common = array();
		$common['feed_id'] = $feed_id;
		$common['post_id'] = $post_id;
		$common['post_type'] = $post_type;
		
		$sql = FFDB::conn()->parse('INSERT INTO ?n SET ?p ?u', $this->post_media_table_name, $mediaPartOfSql4carousel, $common);
		if (false == FFDB::conn()->query($sql)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function addComments($post_id, $comment){
		$time = time();
		$sql_insert = "INSERT INTO ?n SET `id` = ?s, `post_id` = ?s, `from` = ?s, `text` = ?s, `created_time` = ?s, `updated_time` = ?s ON DUPLICATE KEY UPDATE ?u";
		$result = FFDB::conn()->query($sql_insert, $this->comments_table_name, $comment->id, $post_id, is_string($comment->from) ? $comment->from : json_encode($comment->from), $comment->text, $comment->created_time, $comment->created_time, array('updated_time' => $time, 'text' => $comment->text));
		if (false === $result){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function removeComments($post_id){
		$sql_delete = "DELETE FROM ?n WHERE `post_id` = ?s";
		FFDB::conn()->query($sql_delete, $this->comments_table_name, $post_id);
	}
	
	public function deleteCarousel4Post($feed_id, $post_id, $post_type){
		$sql = "delete from ?n where feed_id = ?s and post_id = ?s and post_type = ?s";
		if (false == FFDB::conn()->query($sql, $this->post_media_table_name, $feed_id, $post_id, $post_type)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function deleteCarousel4Feed($feed_id){
		$sql = "delete from ?n where feed_id = ?s";
		if (false == FFDB::conn()->query($sql, $this->post_media_table_name, $feed_id)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	/**
	 * @param string $feedId
	 *
	 * @return array|false
	 */
	public function getIdPosts($feedId){
		return FFDB::conn(true)->getCol('SELECT `post_id` FROM ?n WHERE `feed_id`=?s', $this->posts_table_name, $feedId);
	}
	
	public function getPostsIf($fields, $condition, $order, $offset = null, $limit = null){
		$limitPart = ($offset !== null && $offset !== null) ? FFDB::conn()->parse("LIMIT ?i, ?i", $offset, $limit) : '';
		$sql = FFDB::conn()->parse("SELECT ?p FROM ?n post INNER JOIN ?n stream ON stream.feed_id = post.feed_id INNER JOIN ?n cach ON post.feed_id = cach.feed_id WHERE ?p ORDER BY ?p ?p",
				$fields, $this->posts_table_name, $this->streams_sources_table_name, $this->cache_table_name, $condition, $order, $limitPart);
		return FFDB::conn()->getAll($sql);
	}
	
	public function getPostsIf2($fields, $condition){
		return FFDB::conn()->getAll("SELECT ?p FROM ?n post INNER JOIN ?n stream ON stream.feed_id = post.feed_id INNER JOIN ?n cach ON post.feed_id = cach.feed_id WHERE ?p ORDER BY post.post_timestamp DESC, post.post_id",
				$fields, $this->posts_table_name, $this->streams_sources_table_name, $this->cache_table_name, $condition);
	}
	
	public function countPostsIf($condition){
		return FFDB::conn()->getOne('SELECT COUNT(*) FROM ?n post INNER JOIN ?n stream ON stream.feed_id = post.feed_id INNER JOIN ?n cach ON post.feed_id = cach.feed_id WHERE ?p',
				$this->posts_table_name, $this->streams_sources_table_name, $this->cache_table_name, $condition);
	}
	
	public function getLastUpdateHash($streamId){
		return $this->getHashIf(FFDB::conn()->parse('stream.`stream_id` = ?s', $streamId));
	}
	
	public function getHashIf($condition){
		return FFDB::conn()->getOne("SELECT MAX(post.creation_index) FROM ?n post INNER JOIN ?n stream ON stream.feed_id = post.feed_id INNER JOIN ?n cach ON post.feed_id = cach.feed_id WHERE ?p",
				$this->posts_table_name, $this->streams_sources_table_name, $this->cache_table_name, $condition);
	}
	
	public function getLastUpdateTime($streamId){
		return FFDB::conn()->getOne('SELECT MAX(`last_update`) FROM ?n `cach` inner join ?n `st2src` on `st2src`.`feed_id` = `cach`.`feed_id` WHERE `stream_id` = ?s',  $this->cache_table_name, $this->streams_sources_table_name, $streamId);
	}
	
	public function getLastUpdateTimeAllStreams(){
		return FFDB::conn()->getIndCol('stream_id', 'SELECT MAX(`last_update`), `stream_id` FROM ?n `cach` inner join ?n `st2src` on `st2src`.`feed_id` = `cach`.`feed_id` GROUP BY `stream_id`',  $this->cache_table_name, $this->streams_sources_table_name);
	}
	
	public function deleteEmptyRecordsFromCacheInfo($streamId){
		//FFDB::conn()->query("DELETE FROM ?n where `stream_id`=?s", $this->cache_table_name, $streamId);
	}
	
	public function systemDisableSource($feedId, $enabled){
		return FFDB::conn()->query('UPDATE ?n SET `system_enabled` = ?i WHERE `feed_id`=?s', $this->cache_table_name, $enabled, $feedId);
	}
	
	public function setCacheInfo($feedId, $values){
		$sql = 'INSERT INTO ?n SET `feed_id`=?s, ?u ON DUPLICATE KEY UPDATE ?u';
		return FFDB::conn()->query( $sql, $this->cache_table_name, $feedId, $values, $values );
	}
	
	public function setRandomOrder($feedId){
		return FFDB::conn()->query('UPDATE ?n SET `rand_order` = RAND() WHERE `feed_id`=?s', $this->posts_table_name, $feedId);
	}
	
	public function setSmartOrder($feedId, $count){
		$wherePartOfSql = FFDB::conn()->parse('feed_id = ?s', $feedId);
		if (false === FFDB::conn()->query('UPDATE ?n SET `smart_order` = `smart_order` + ?i WHERE ?p', $this->posts_table_name, $count, $wherePartOfSql)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function removeOldRecords($c_count){
		$result = FFDB::conn()->getAll('select count(*) as `count`, `feed_id` from ?n group by `feed_id` order by 1 desc', $this->posts_table_name);
		foreach ( $result as $row ) {
			$count = (int)$row['count'];
			if ($count > $c_count) {
				$feed = $row['feed_id'];
				$count = $count - $c_count;
				$sub_query = FFDB::conn()->parse('select max(tmp.`post_timestamp`) from (select `post_timestamp` from ?n where `feed_id` = ?s order by `post_timestamp` limit 0, ?i) as tmp',$this->posts_table_name, $feed, $count);
				$sub_query2 = FFDB::conn()->parse('select tmp2.post_id from ?n as tmp2 where tmp2.post_timestamp <= (?p)', $this->posts_table_name, $sub_query);
				FFDB::conn()->query('delete from ?n where feed_id = ?s and post_id in (?p)', $this->post_media_table_name, $feed, $sub_query2);
				FFDB::conn()->query('delete from ?n where feed_id = ?s and post_timestamp <= (?p)', $this->posts_table_name, $feed, $sub_query);
				continue;
			}
			break;
		}
	}
	
	public function setPostStatus($status, $condition, $creation_index = null){
		$sql = "UPDATE ?n SET `post_status` = ?s";
		$sql .= ($creation_index != null) ? ", `creation_index` = " . $creation_index . " ?p" : " ?p";
		if (false == FFDB::conn()->query($sql, $this->posts_table_name, $status, $condition)){
			throw new \Exception(FFDB::conn()->conn->error);
		}
	}
	
	public function registrationCheck(){
		$activated = false;
		if (false !== ($registration_id = $this->getOption('registration_id'))){
			if ((false !== ($registration_date = $this->getOption('registration_date'))) &&
					(time() > $registration_date + 604800)){
						$ch = curl_init( 'http://flow.looks-awesome.com/wp-admin/admin-ajax.php?action=la_check&registration_id=' . $registration_id);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, false);
						$result = curl_exec( $ch );
						curl_close( $ch );
						$result = json_decode($result);
						if (isset($result->registration_id) && $registration_id == $result->registration_id){
							$this->setOption('registration_id', $result->registration_id);
							$this->setOption('registration_date', time());
							return true;
						}
						return false;
			}
			$activated = !empty($registration_id);
		}
		return $activated;
	}
	
	private function activate($settings){
		$activated = $this->registrationCheck();
		$option_name = 'flow_flow_options';
		if (!$activated
				&& isset($settings[$option_name]['company_email']) && isset($settings[$option_name]['purchase_code'])
				&& !empty($settings[$option_name]['company_email']) && !empty($settings[$option_name]['purchase_code'])){
					
					$name = isset($settings[$option_name]['company_name']) ? $settings[$option_name]['company_name'] : 'Unnamed';
					$subscription = 0;
					if (isset($settings[$option_name]['news_subscription']) && !empty($settings[$option_name]['news_subscription'])){
						$subscription = $settings[$option_name]['news_subscription'] == 'yep' ? 1 : 0;
					}
					$post = array(
							'action' => 'la_activation',
							'name' => $name,
							'email' => @$settings[$option_name]['company_email'],
							'purchase_code'   => @$settings[$option_name]['purchase_code'],
							'subscription' => $subscription,
							'plugin_name'	=>	$this->plugin_slug
					);
					
					list($result, $error) = $this->sendRequest2lo($post);
					if (false !== $result){
						$result = json_decode($result);
						if (isset($result->registration_id)){
							$this->setOption('registration_id', $result->registration_id);
							$this->setOption('registration_date', time());
							return true;
						}
						else if (isset($result->error)){
							throw new \Exception(is_string($result->error) ? $result->error : print_r($result->error, true));
						}
					}
					else {
						throw new \Exception($error);
					}
		}
		if ($activated){
			$registration_id = $this->getOption('registration_id');
			$name = isset($settings[$option_name]['company_name']) ? $settings[$option_name]['company_name'] : 'Unnamed';
			$post = array(
					'action' => 'la_activation',
					'registration_id' => $registration_id,
					'name' => $name,
					'email' => @$settings[$option_name]['company_email'],
					'purchase_code'   => @$settings[$option_name]['purchase_code'],
					'subscription' => 1,
					'plugin_name'	=>	$this->plugin_slug
			);
			
			//subscribe
			if (isset($_POST['doSubcribe']) && $_POST['doSubcribe'] == 'true'){
				$result = $this->sendRequest2lo($post);
				$result = json_decode($result[0]);
				if (isset($result->registration_id)){
					$this->setOption('registration_id', $result->registration_id);
					$this->setOption('registration_date', time());
					return true;
				}
				return false;
			}
			
			//remove registration
			if (!isset($settings[$option_name]['purchase_code']) || empty($settings[$option_name]['purchase_code'])){
				$post['purchase_code'] = '';
				$this->sendRequest2lo($post);
				$this->deleteOption('registration_id');
				$this->deleteOption('registration_date');
				return false;
			}
		}
		return true;
	}
	
	private function sendRequest2lo($data){
		$ch = curl_init( 'http://flow.looks-awesome.com/wp-admin/admin-ajax.php' );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
		$error = null;
		$result = curl_exec( $ch );
		if ($result === false){
			$error = curl_error($ch);
		}
		curl_close( $ch );
		return array($result, $error);
	}
}