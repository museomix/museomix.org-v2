<?php namespace flow\db\migrations;
use flow\db\FFDB;
use flow\db\FFDBMigration;
use flow\db\LADBManager;
use flow\db\SafeMySQL;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_2_17 implements FFDBMigration {

	public function version() {
		return '2.17';
	}

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager ) {
		if (!FFDB::existColumn(FF_SNAPSHOTS_TABLE_NAME, 'version')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n VARCHAR(25) DEFAULT '2.0'";
			$conn->query($sql, FF_SNAPSHOTS_TABLE_NAME, 'version');
		}
	}
}