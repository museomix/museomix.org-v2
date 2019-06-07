<?php namespace flow\db\migrations;

use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFMigration_3_9 implements ILADBMigration {

	public function version() {
		return '3.9';
	}

	/**
	 * @param \flow\db\SafeMySQL $conn
	 * @param \flow\db\LADBManager $manager
	 */
	public function execute( $conn, $manager ) {
		if (!FFDB::existColumn($manager->posts_table_name, 'post_content')){
			$conn->query('ALTER TABLE ?n ADD ?n BLOB DEFAULT NULL', $manager->posts_table_name, 'post_content');
		}
	}
}