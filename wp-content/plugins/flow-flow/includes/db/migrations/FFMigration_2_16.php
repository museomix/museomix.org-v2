<?php namespace flow\db\migrations;
use flow\db\FFDB;
use flow\db\FFDBMigration;
use flow\db\FFDBUpdate;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_2_16 implements FFDBMigration{
	private $sources;

	public function version() {
		return '2.16';
	}

	public function execute($conn, $manager) {
		$this->sources = array();

		FFDBUpdate::create_cache_table($manager->cache_table_name, $manager->posts_table_name, $manager->streams_sources_table_name);

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
}