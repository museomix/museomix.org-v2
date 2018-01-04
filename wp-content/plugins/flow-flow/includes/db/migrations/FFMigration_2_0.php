<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Insta-Flow.
 *
 * @package   InstaFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2017 Looks Awesome
 */
class FFMigration_2_0 implements ILADBMigration{

	public function version() {
		return '2.0';
	}

	public function execute($conn, $manager) {
		if (FF_USE_WP) {
			if (!FFDB::existColumn($manager->streams_table_name, 'feeds')){
				$sql = "ALTER TABLE ?n ADD COLUMN ?n LONGBLOB";
				FFDB::conn()->query($sql, $manager->streams_table_name, 'feeds');
			}

			global $wpdb;

			$option_name = 'flow_flow_options';
			$sql = "INSERT INTO ?n (`id`, `value`) SELECT ?s, wp1.option_value as 'settings' FROM ?n wp1 WHERE wp1.option_name = ?s";
			$conn->query($sql, $manager->option_table_name, $option_name, $wpdb->prefix . 'options', $option_name);

			$option_name = 'flow_flow_fb_auth_options';
			$sql = "INSERT INTO ?n (`id`, `value`) SELECT ?s, wp1.option_value as 'settings' FROM ?n wp1 WHERE wp1.option_name = ?s";
			$conn->query($sql, $manager->option_table_name, $option_name, $wpdb->prefix . 'options', $option_name);

			$option_name = 'flow_flow_facebook_access_token';
			$sql = "INSERT INTO ?n (`id`, `value`) SELECT ?s, wp1.option_value as 'settings' FROM ?n wp1 WHERE wp1.option_name = ?s";
			$conn->query($sql, $manager->option_table_name, $option_name, $wpdb->prefix . 'options', '_transient_' . $option_name);

			$option_name = 'flow_flow_facebook_access_token_expires';
			$sql = "INSERT INTO ?n (`id`, `value`) SELECT ?s, wp1.option_value as 'settings' FROM ?n wp1 WHERE wp1.option_name = ?s";
			$conn->query($sql, $manager->option_table_name, $option_name, $wpdb->prefix . 'options', '_transient_' . $option_name);

			$options = $manager->getOption('options', true);
			if (isset($options['streams'])){
				$json = json_decode($options['streams']);
				foreach ( $json as $stream) {
					$obj = (object)$stream;
					$this->setStream($conn, $manager->streams_table_name, $obj->id, $obj);
				}
				unset($options['streams']);
			}
			unset($options['streams_count']);
			$manager->setOption('options', $options, true);
		}
	}

	private function setStream($conn, $table_name, $id, $stream){
		$name = $stream->name;
		$layout = isset($stream->layout) ? $stream->layout : NULL;
		$feeds = (is_array($stream->feeds) || is_object($stream->feeds)) ? serialize($stream->feeds) : stripslashes($stream->feeds);
		unset($stream->feeds);
		$serialized = serialize($stream);
		$common = array(
			'name'      => $name,
			'layout'    => $layout,
			'feeds'     => $feeds,
			'value'     => $serialized
		);
		if ( false === $conn->query( 'INSERT INTO ?n SET `id`=?s, ?u ON DUPLICATE KEY UPDATE ?u',
				$table_name, $id, $common, $common ) ) {
			throw new \Exception();
		}
		FFDB::commit();
	}
}