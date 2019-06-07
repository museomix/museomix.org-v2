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
class FFMigration_3_1 implements ILADBMigration{
	public function version(){
		return '3.1';		
	}
	
	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute($conn, $manager){
		if (!FFDB::existColumn($manager->posts_table_name, 'user_bio')){
			$conn->query('ALTER TABLE ?n ADD ?n VARCHAR(200) NULL', $manager->posts_table_name, 'user_bio');
		}
		if (!FFDB::existColumn($manager->posts_table_name, 'user_counts_media')){
			$conn->query('ALTER TABLE ?n ADD ?n INT NULL', $manager->posts_table_name, 'user_counts_media');
		}
		if (!FFDB::existColumn($manager->posts_table_name, 'user_counts_follows')){
			$conn->query('ALTER TABLE ?n ADD ?n INT NULL', $manager->posts_table_name, 'user_counts_follows');
		}
		if (!FFDB::existColumn($manager->posts_table_name, 'user_counts_followed_by')){
			$conn->query('ALTER TABLE ?n ADD ?n INT NULL', $manager->posts_table_name, 'user_counts_followed_by');
		}
		if (!FFDB::existColumn($manager->posts_table_name, 'location')){
			$conn->query('ALTER TABLE ?n ADD ?n VARCHAR(300) NULL', $manager->posts_table_name, 'location');
		}
	}
}