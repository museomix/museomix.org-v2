<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;
use flow\db\LADBManager;
use flow\db\SafeMySQL;

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
class FFMigration_2_17 implements ILADBMigration{

	public function version() {
		return '2.17';
	}

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager ) {
		if (!FFDB::existColumn($manager->snapshot_table_name, 'version')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n VARCHAR(25) DEFAULT '2.0'";
			$conn->query($sql, $manager->snapshot_table_name, 'version');
		}
	}
}