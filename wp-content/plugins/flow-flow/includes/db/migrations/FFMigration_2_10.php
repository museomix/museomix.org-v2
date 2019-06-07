<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFMigration_2_10 implements ILADBMigration{

	public function version() {
		return '2.10';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existColumn($manager->posts_table_name, 'post_status')){
			$conn->query("ALTER TABLE ?n ADD ?n VARCHAR(15) NOT NULL DEFAULT 'approved'", $manager->posts_table_name, 'post_status');
		}
	}
}