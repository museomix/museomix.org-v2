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
 * @copyright Looks Awesome
 */
class FFMigration_3_2 implements ILADBMigration{
	public function version(){
		return '3.2';
	}
	
	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute($conn, $manager){
		if (!FFDB::existColumn($manager->posts_table_name, 'carousel_size')){
			$conn->query('ALTER TABLE ?n ADD ?n INT DEFAULT 0 NOT NULL', $manager->posts_table_name, 'carousel_size');
		}
		
		if (!FFDB::existTable($manager->post_media_table_name)){
			$sql = "CREATE TABLE ?n
			(
				`id` INT NOT NULL AUTO_INCREMENT,
				`feed_id` VARCHAR(20) NOT NULL,
				`post_id` VARCHAR(50) NOT NULL,
				`post_type` VARCHAR(10) NOT NULL,
				`media_url` TEXT,
				`media_width` INT,
				`media_height` INT,
				`media_type` VARCHAR(100),
				PRIMARY KEY (`id`)
			) ?p";
			$conn->query($sql, $manager->post_media_table_name, $this->charset());
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