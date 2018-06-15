<?php namespace flow\db\migrations;

use flow\db\FFDB;
use flow\db\LADBManager;
use flow\db\SafeMySQL;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2017 Looks Awesome
 */
class FFMigration_3_5 implements ILADBMigration{
	public function version() {
		return '3.5';
	}

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager ) {
		$conn->query('ALTER TABLE ?n MODIFY ?n TEXT', $manager->posts_table_name, 'user_bio');
	}
}