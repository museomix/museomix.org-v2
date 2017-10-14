<?php namespace flow\db\migrations;
use flow\db\FFDB;
use flow\db\FFDBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_2_11 implements FFDBMigration{

	public function version() {
		return '2.11';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existColumn($manager->posts_table_name, 'post_source')){
			$conn->query("ALTER TABLE ?n ADD COLUMN ?n VARCHAR(300)", $manager->posts_table_name, 'post_source');
		}
	}
}