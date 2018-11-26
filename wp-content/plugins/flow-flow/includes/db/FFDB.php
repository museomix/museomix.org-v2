<?php namespace flow\db;
use flow\settings\FFSettingsUtils;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 *@copyright 2014-2016 Looks Awesome
 */
class FFDB {
	/** @var SafeMySQL $db */
	private static $db = null;

	/**
	 * @return string
	 */
	public static function charset(){
		if (FF_USE_WP){
			/** @var wpdb $wpdb */
			$wpdb = $GLOBALS['wpdb'];
			return $wpdb->charset;
		}
		return DB_CHARSET; // @codeCoverageIgnore
	}

	/**
	 * @param bool $reopen
	 * @return SafeMySQL
	 */
	public static function conn($reopen = false){
		if ($reopen || self::$db == null)
		{
			self::$db = self::create();
			self::$db->conn->autocommit(true);
		}
		return self::$db;
	}

	public static function create(){
		try{
			return new SafeMySQL(array('host' => DB_HOST, 'user' => DB_USER, 'pass' => DB_PASSWORD, 'db' => DB_NAME, 'charset' => FF_DB_CHARSET, 'errmode' => 'exception'));
		// @codeCoverageIgnoreStart
		} catch(\Exception $e){
            echo '<b>Flow-Flow</b> plugin encountered database connection error. Please contact for support via item\'s comments section and provide info below:<br>';
			echo $e->getMessage();
			if (isset($_REQUEST['debug'])){
				var_dump($e);
			}
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
			die();
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @return bool
	 */
	public static function close(){
		$result = self::conn()->conn->close();
		self::$db = null;
		return $result;
	}

	/**
	 * @return string
	 */
	public static function collate() {
		if (FF_USE_WP){
			/** @var wpdb $wpdb */
			$wpdb = $GLOBALS['wpdb'];
			return $wpdb->collate;
		}
		return DB_COLLATE; // @codeCoverageIgnore
	}

	/**
	 * @return bool
	 */
	public static function beginTransaction(){
		return self::conn()->conn->autocommit(false);
	}

	/**
	 * @return bool
	 */
	public static function commit(){
		return self::conn()->conn->commit();
	}

	/**
	 * @return bool
	 */
	public static function rollback(){
		$result = self::conn()->conn->rollback();
		self::$db->conn->autocommit(true);
		return $result;
	}

	/**
	 * @return bool
	 */
	public static function rollbackAndClose(){
		$result = self::rollback();
		self::close();
		return $result;
	}

	/**
	 * @param $table_name
	 * @return bool
	 */
	public static function existTable($table_name){
		return self::conn()->getOne('SHOW TABLES LIKE ?s', $table_name) !== false;
	}

	public static function dropTable($table_name){
		self::conn()->query('DROP TABLE ' . $table_name);
	}

	public static function existColumn($table_name, $column_name){
		return self::conn()->getOne('SHOW COLUMNS FROM ?n LIKE ?s', $table_name, $column_name) !== false;
	}

	private static $cache = array();

	public static function getOption($table_name, $option_name, $serialized = false, $lock_row = false){
		if ($lock_row || !isset(self::$cache[$option_name])){
			$q = 'select `value` from ?n where `id`=?s';
			if ($lock_row) $q .= ' for update';
			$options = self::conn()->getOne($q, $table_name, $option_name);
			if ($options == false || $options == null ) return false;
			self::$cache[$option_name] = $serialized ? unserialize($options) : $options;
		}
		return self::$cache[$option_name];
	}

	public static function setOption($table_name, $optionName, $optionValue, $serialized = false, $cached = true){
		if ($cached) self::$cache[$optionName] = is_object($optionValue) ? clone $optionValue : $optionValue;
		if ($serialized) $optionValue = serialize($optionValue);
		if ( false === self::conn()->query( 'INSERT INTO ?n SET `id`=?s, `value`=?s ON DUPLICATE KEY UPDATE `value`=?s',
				$table_name, $optionName, $optionValue, $optionValue ) ) {
			throw new \Exception(); // @codeCoverageIgnore
		}
	}

	public static function deleteOption($table_name, $optionName){
		if (false === self::conn()->query('DELETE FROM ?n WHERE `id`=?s', $table_name, $optionName)){
			throw new \Exception(); // @codeCoverageIgnore
		}
		unset(self::$cache[$optionName]);
	}

	public static function streams($table_name){
		if (false !== ($result = self::conn()->getIndCol('id', 'SELECT `id`, `name`, `value` FROM ?n ORDER BY `id`',
				$table_name))){
			return $result;
		}
		return array();
	}

	/**
	 * @param FFDBManager $dbm
	 * @param string $stream
	 * @param bool $only_enable
	 * @return array|array
	 */
	public static function sources($cache_table_name, $streams_sources_table_name, $stream = null, $only_enable = false){
		$sql_part = '';
		if ($only_enable && $stream == null)  $sql_part = self::conn()->parse('WHERE `enabled` = 1');
		if ($stream != null) $sql_part = self::conn()->parse('inner join ?n `conn` on `cach`.`feed_id` = `conn`.`feed_id` WHERE `enabled` = 1 and `conn`.`stream_id` = ?s', $streams_sources_table_name, $stream);
		$sql = self::conn()->parse('SELECT  `cach`.`feed_id` as `id`, `settings`, `errors`, `status`, `enabled`, `last_update`, `cach`.cache_lifetime, `cach`.system_enabled FROM ?n `cach` ?p ORDER BY `changed_time` DESC', $cache_table_name, $sql_part);
		if (false !== ($result = self::conn()->getInd('id', $sql))){
			foreach ( $result as &$source ) {
				if (isset($source['settings'])){
					$settings = unserialize($source['settings']);
					if (is_object($settings)) {
						$source = array_merge($source, (array) $settings);
						unset($source['settings']);
					}
				}

				$source['enabled'] = $source['system_enabled'] == 1 ? ($source['enabled'] == 1 ? FFSettingsUtils::YEP : FFSettingsUtils::NOPE) : FFSettingsUtils::NOPE;
				$offset = get_option('gmt_offset', 0);
				$date = $source['last_update'] + $offset * 3600;
				$source['last_update'] = $source['last_update'] == 0 ? 'N/A' : FFSettingsUtils::classicStyleDate($date);
				if (!isset($source['errors']) || is_null($source['errors'])) {
					$source['errors'] = array();
				}
				if (!empty($source['errors'])){
					if (false !== ($errors = unserialize($source['errors']))){
						if (is_array($errors)){
							$escape = array("'");
							$replacements = array(" ");
							foreach ( $errors as &$error ) {
								if (isset($error['message'])){
									if (is_array($error['message'])){
										for ( $i = 0; $i < sizeof($error['message']); $i ++ ) {
											$error['message'][$i]['msg'] = str_replace($escape, $replacements, $error['message'][$i]['msg']);
										}
									}
									else {
										$error['message'] = str_replace($escape, $replacements, $error['message']);
									}
									continue;
								}

								//TODO delete
								if (is_array($error) && isset($error[0])){
									$error['message'] = $error[0];
									unset($error[0]);
								}
								if (is_array($error) && isset($error['msg'])){
									$error['message'] = $error['msg'];
									unset($error['msg']);
								}
							}
							$source['errors'] = $errors;
						}
					}
				}
				if ((empty($source['errors']) || is_string($source['errors'])) && $source['status'] === '0') {
					$source['errors'] = array( array( 'type' => $source['type'], 'message' => 'Feed cache has not been built. Try to manually rebuild cache using three dots menu on the left.' ) );
				}
			}
			return $result;
		}
		return array();
	}

	/**
	 * @param string $table_name
	 *
	 * @return bool|int
	 */
	public static function countFeeds($table_name){
		if (self::existTable($table_name) && false !== ($count = self::conn()->getOne('select count(*) from ?n', $table_name))){
			return (int) $count;
		}
		return false;
	}

	/**
	 * @param string $table_name
	 *
	 * @return bool|int
	 */
	public static function maxIdOfStreams($table_name){
		if (false !== ($max = self::conn()->getOne('select max(`id`) from ?n', $table_name))){
			return (int) $max;
		}
		return false;
	}

	public static function getStream($table_name, $id){
		if (!array_key_exists($id, self::$cache)){
			if (false !== ($row = self::conn()->getRow('select `value`, `feeds` from ?n where `id`=?s', $table_name, $id))) {
				if ($row != null){
					self::$cache[$id] = self::unserializeStream($row);
				}
				else return null;
			}
		}
		return self::$cache[$id];
	}

	public static function unserializeStream($stream){
		$options = unserialize($stream['value']);
		//$options->feeds = $stream['feeds'];
		return $options;
	}

	public static function getStatusInfo($cache_table_name, $streams_sources_table_name, $streamId, $format = true) {
		$sql_part = FFDB::conn()->parse('where `src`.`stream_id` = ?s and `cach`.`enabled` = true', $streamId);
		$sql = FFDB::conn()->parse('select `src`.`stream_id` as `id`, MIN(`cach`.`status`) as `status`, COUNT(`cach`.`feed_id`) as `feeds_count` from ?n `cach` inner join ?n `src` on `cach`.`feed_id` = `src`.`feed_id`  ?p  group by `src`.`stream_id`', $cache_table_name, $streams_sources_table_name, $sql_part);
		$status_info = FFDB::conn()->getAll($sql);
		if (empty($status_info)){
			return array('id' => (string)$streamId, 'status' => '1', 'feeds_count' => '0');
		}
		$status_info = $status_info[0];
		if ($status_info['status'] == '0') {
			$status_info['error'] = self::getError($cache_table_name, $streams_sources_table_name, $streamId, $format);
		}
		return $status_info;
	}

	public static function getError($cache_table_name, $streams_sources_table_name, $streamId, $format = true){
		$result = '';
		$errors = FFDB::conn()->getInd('feed_id', 'select `cach`.`errors`, `cach`.`feed_id` from ?n `cach` inner join ?n `src` on `cach`.`feed_id` = `src`.`feed_id` where `src`.`stream_id` = ?s and `cach`.`enabled` = 1', $cache_table_name, $streams_sources_table_name, $streamId);
		foreach ( $errors as $feed => $error ) {
			unset($error['feed_id']);
			if (is_array($error)){
				foreach ( $error as $str ) {
					$value = unserialize($str);
					if (!empty($value)){
						if (is_array($value) && sizeof($value) == 1){
							$value = $value[0];
						}
						$result[$feed] = $value;
					}

				}
			}
			else if (is_string($error)){
				$value = unserialize($error);
				if (!empty($value)){
					$result[] = $value;
				}

			}
		}
		return $format ? print_r($result, true) : $result;
	}

	public static function setStream($streams_table_name, $streams_sources_table_name, $id, $stream){
		self::$cache[$id] = clone $stream;
		$name = $stream->name;
		$originalFeed = $stream->feeds;
		if (is_string($stream->feeds)){
			$feeds = stripslashes($stream->feeds);
			$feeds = json_decode($feeds);
		}
		else{
			$feeds = (array)$stream->feeds;
		}
		unset($stream->feeds);
		$serialized = serialize($stream);

		$common = array(
			'name'      => $name,
			'value'     => $serialized
		);
		if ( false === self::conn()->query( 'INSERT INTO ?n SET `id`=?s, ?u ON DUPLICATE KEY UPDATE ?u',
				$streams_table_name, $id, $common, $common ) ) {
			throw new \Exception();
		}

		$stream->feeds = $originalFeed;

		$feed_ids = array();
		foreach ( $feeds as $feed ) {
			$fid = is_array($feed) ? $feed['id'] :  $feed->id;
			$feed_ids[] = $fid;
			$connect = array(
				'stream_id' => $id,
				'feed_id' => $fid
			);
			if ( false === self::conn()->query( 'INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u',
					$streams_sources_table_name, $connect, $connect ) ) {
				throw new \Exception();
			}
		}
		if ( false === self::conn()->query( 'DELETE FROM ?n WHERE `feed_id` NOT IN (?a) AND `stream_id`=?s',
				$streams_sources_table_name, $feed_ids, $id ) ) {
			throw new \Exception();
		}

		self::commit();
	}

	public static function deleteStream($streams_table_name, $streams_sources_table_name, $id){
		unset(self::$cache[$id]);
		if (false === self::conn()->query('DELETE FROM ?n WHERE `id`=?s', $streams_table_name, $id)){
			return new \Exception();
		}
		if (false === self::conn()->query('DELETE FROM ?n WHERE `stream_id`=?s', $streams_sources_table_name, $id)){
			return new \Exception();
		}
		return true;
	}

	public static function saveFeed($cache_table_name, $feed_id, $values){
		$sql = FFDB::conn()->parse('UPDATE ?n SET ?u WHERE `feed_id` = ?s', $cache_table_name, $values, $feed_id);
		return FFDB::conn()->query($sql);
	}
}