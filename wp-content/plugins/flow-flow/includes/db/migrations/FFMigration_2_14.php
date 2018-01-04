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
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_2_14 implements ILADBMigration{

	public function version() {
		return '2.14';
	}

	public function execute($conn, $manager) {
		$tableName = $manager->image_cache_table_name;
		if (!FFDB::existColumn($tableName, 'original_url')){
			$conn->query("ALTER TABLE ?n ADD COLUMN ?n VARCHAR(300)", $tableName, 'original_url');
		}

		$tableName = str_replace('ff_image_cache', 'wss_image_cache', $tableName);
		if (FFDB::existTable($tableName) && !FFDB::existColumn($tableName, 'original_url')){
			$conn->query("ALTER TABLE ?n ADD COLUMN ?n VARCHAR(300)", $tableName, 'original_url');
		}
	}
}