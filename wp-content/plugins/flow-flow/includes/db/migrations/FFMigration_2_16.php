<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFMigration_2_16 implements ILADBMigration{
	private $sources;

	public function version() {
		return '2.16';
	}

	public function execute($conn, $manager) {
		$this->sources = array();

		$this->create_cache_table($manager->cache_table_name, $manager->posts_table_name, $manager->streams_sources_table_name);

		if (!FFDB::existColumn($manager->cache_table_name, 'settings')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n BLOB";
			$conn->query($sql, $manager->cache_table_name, 'settings');
		}

		if (!FFDB::existColumn($manager->cache_table_name, 'enabled')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n TINYINT(1)";
			$conn->query($sql, $manager->cache_table_name, 'enabled');
		}

		if (!FFDB::existColumn($manager->cache_table_name, 'changed_time')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n INT DEFAULT 0";
			$conn->query($sql, $manager->cache_table_name, 'changed_time');
		}

		if (!FFDB::existColumn($manager->cache_table_name, 'cache_lifetime')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n INT DEFAULT 60";
			$conn->query($sql, $manager->cache_table_name, 'cache_lifetime');
		}

		if (FFDB::existColumn($manager->cache_table_name, 'stream_id')){
			$sql = "ALTER TABLE ?n DROP `stream_id`";
			$conn->query($sql, $manager->cache_table_name);
		}

		if (FFDB::existColumn($manager->posts_table_name, 'stream_id')){
			$sql = "ALTER TABLE ?n DROP `stream_id`";
			$conn->query($sql, $manager->posts_table_name);
		}

		$time = time();
		$streams = $this->streams($conn, $manager->streams_table_name);
		foreach ( $streams as $stream ) {
			$stream = $this->getStream($conn, $manager->streams_table_name, $stream['id']);
			if (!isset($stream->feeds) || is_null($stream->feeds)){
				continue;
			}
			$feeds = json_decode($stream->feeds);
			$cache_lifetime = 60;
			if (isset($stream->{'cache-lifetime'})){
				$cache_lifetime = (int) $stream->{'cache-lifetime'};
				if ($cache_lifetime > 5760) $cache_lifetime = 10080;
				else if ($cache_lifetime > 900 && $cache_lifetime <= 5760) $cache_lifetime = 1440;
				else if ($cache_lifetime > 210 && $cache_lifetime <= 900) $cache_lifetime = 360;
				else if ($cache_lifetime > 45 && $cache_lifetime <= 210) $cache_lifetime = 60;
				else if ($cache_lifetime > 17 && $cache_lifetime <= 45) $cache_lifetime = 30;
				else if (17 >= $cache_lifetime) $cache_lifetime = 5;
			}
			$load_last = 5;
			if (isset($stream->posts)){
				$load_last = (int) $stream->posts;
				if ($load_last > 15) $load_last = 20;
				else if ($load_last > 8 && $load_last <= 15) $load_last = 10;
				else if ($load_last > 3 && $load_last <= 8) $load_last = 5;
				else if (3 >= $load_last) $load_last = 1;
			}
			foreach ( $feeds as $feed ) {
				$feed->posts = $load_last;
				if (isset($stream->moderation)){
					$feed->mod = $stream->moderation;
				}
				$f = serialize($feed);
				$insert = array(
					'last_update' => time(),
					'settings' => $f,
					'enabled' => true,
					'changed_time' => $time,
					'cache_lifetime' => $cache_lifetime
				);
				$update = array(
					'settings' => $f,
					'enabled' => true,
					'changed_time' => $time,
					'cache_lifetime' => $cache_lifetime
				);
				if ( false === $conn->query( 'INSERT INTO ?n SET `feed_id`=?s, ?u ON DUPLICATE KEY UPDATE ?u',
						$manager->cache_table_name, $feed->id, $insert, $update ) ) {
					throw new \Exception();
				}

				if ( false === $conn->query( 'INSERT INTO ?n SET `feed_id`=?s, `stream_id`=?i',
						$manager->streams_sources_table_name, $this->source($f, $feed->id), $stream->id) ) {
					throw new \Exception();
				}
			}
		}

		if (FFDB::existColumn($manager->streams_table_name, 'feeds')){
			$sql = "ALTER TABLE ?n DROP `feeds`";
			$conn->query($sql, $manager->streams_table_name);
		}
	}

	private function source($source, $id){
		$hash = hash('md5', $source);
		if (array_key_exists($hash, $this->sources)){
			return $this->sources[$hash];
		}
		else {
			$this->sources[$hash] = $id;
			return $id;
		}
	}

	private function streams($conn, $table_name){
		if (false !== ($result = $conn->getAll('SELECT `id`, `value` FROM ?n ORDER BY `id`',
				$table_name))){
			return $result;
		}
		return array();
	}

	private function getStream($conn, $table_name, $id){
		if (false !== ($row = $conn->getRow('select `value`, `feeds` from ?n where `id`=?s', $table_name, $id))) {
			if ($row != null){
				$options = unserialize($row['value']);
				$options->feeds = $row['feeds'];
				return $options;
			}
		}
		return null;
	}
	
	private function create_cache_table ($cache_table, $posts_table, $streams2sources_table) {
		/*
		 * We'll set the default character set and collation for this table.
		 * If we don't do this, some characters could end up being converted
		 * to just ?'s when saved in our table.
		 */
		$charset_collate = '';
		
		$charset = FFDB::charset();
		if ( !empty( $charset ) ) {
			$charset_collate = " CHARACTER SET {$charset}";
			$charset = " CHARACTER SET {$charset}";
		}
		
		$collate = FFDB::collate();
		if ( !empty( $collate ) ) {
			$charset_collate .= " COLLATE {$collate}";
		}
		
		if(!FFDB::existTable($cache_table)){
			$sql = "
			CREATE TABLE `{$cache_table}`
			(
			`feed_id` VARCHAR(20) NOT NULL,
			`last_update` INT NOT NULL,
			`status` INT NOT NULL DEFAULT 0,
			`errors` BLOB,
			`settings` BLOB,
			`enabled` TINYINT(1) DEFAULT 0,
			`system_enabled` TINYINT(1) DEFAULT 1,
			`changed_time` INT DEFAULT 0,
			`cache_lifetime` INT DEFAULT 60,
			PRIMARY KEY (`feed_id`)
			){$charset}";
			FFDB::conn()->query($sql);
		}
		
		if(!FFDB::existTable($streams2sources_table)){
			$sql = "
			CREATE TABLE `{$streams2sources_table}`
			(
			`feed_id` VARCHAR(20) NOT NULL,
			`stream_id` INT NOT NULL,
			PRIMARY KEY (`feed_id`, `stream_id`)
			){$charset}";
			FFDB::conn()->query($sql);
		}
		
		if(!FFDB::existTable($posts_table)) {
			$sql = "
			CREATE TABLE `{$posts_table}`
			(
			`feed_id` VARCHAR(20) NOT NULL,
			`post_id` VARCHAR(50) NOT NULL,
			`post_type` VARCHAR(10) NOT NULL,
			`post_date` TIMESTAMP,
			`post_text` BLOB,
			`post_permalink` VARCHAR(300),
			`post_header` VARCHAR(200){$charset_collate},
			`user_nickname` VARCHAR(100){$charset_collate},
			`user_screenname` VARCHAR(200){$charset_collate},
			`user_pic` VARCHAR(300) NOT NULL,
			`user_link` VARCHAR(300),
			`user_bio` VARCHAR(200),
			`user_counts_media` INT,
			`user_counts_follows` INT,
			`user_counts_followed_by` INT,
			`rand_order` REAL,
			`creation_index` INT NOT NULL DEFAULT 0,
			`image_url` VARCHAR(500),
			`image_width` INT,
			`image_height` INT,
			`media_url` VARCHAR(500),
			`media_width` INT,
			`media_height` INT,
			`media_type` VARCHAR(100),
			PRIMARY KEY (`post_id`, `post_type`, `feed_id`)
			){$charset}";
			FFDB::conn()->query($sql);
		}
	}
}