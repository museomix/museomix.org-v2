<?php namespace flow\db\migrations;

use la\core\db\migrations\ILADBMigration;
use flow\db\LADBManager;
use flow\db\FFDB;

if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_3_3 implements ILADBMigration{
	public function version(){
		return '3.3';
	}
	
	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute($conn, $manager){
		if (!FFDB::existTable($manager->comments_table_name)){
			$sql = "CREATE TABLE ?n
			(
				`id` VARCHAR(50) NOT NULL,
				`post_id` VARCHAR(50) NOT NULL,
				`from` BLOB,
				`text` LONGBLOB,
				`created_time` INT,
				`updated_time` INT,
				PRIMARY KEY (`id`)
			) ?p";
			$conn->query($sql, $manager->comments_table_name, $this->charset());
		}
	}
	
	private function charset(){
		$charset = FFDB::charset();
		if ( !empty( $charset ) ) {
			$charset = " CHARACTER SET {$charset}";
		}
		return $charset;
	}
}