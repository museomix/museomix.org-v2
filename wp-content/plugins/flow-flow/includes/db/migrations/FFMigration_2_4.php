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
class FFMigration_2_4 implements ILADBMigration{

	public function version() {
		return '2.4';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existColumn($manager->streams_table_name, 'status')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n INT DEFAULT 0";
			$conn->query($sql, $manager->streams_table_name, 'status');
		}

		$conn->query('DELETE FROM ?n', $manager->cache_table_name);
	}
}