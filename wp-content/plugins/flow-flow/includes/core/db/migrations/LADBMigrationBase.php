<?php namespace la\core\db\migrations;
if ( ! defined( 'WPINC' ) ) die;

use la\core\db\LADBMigrationManager;
use flow\db\FFDB;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2017 Looks Awesome
 */
abstract class LADBMigrationBase implements ILADBMigration{
	
	public function version() {
		return LADBMigrationManager::INIT_MIGRATION;
	}
	
	public function execute($conn, $manager){
		$this->create_options_table($conn, $manager->option_table_name);
		$this->create_streams_table($conn, $manager->streams_table_name);
		$this->create_cache_table($conn, $manager->cache_table_name);
		$this->create_posts_table($conn, $manager->posts_table_name);
		$this->create_post_media_table($conn, $manager->post_media_table_name);
		$this->create_streams2sources_table($conn, $manager->streams_sources_table_name);
		$this->create_image_size_table($conn, $manager->image_cache_table_name);
		$this->create_snapshot_table($conn, $manager->snapshot_table_name);
		$this->create_comments_table($conn, $manager->comments_table_name);
	}
	
	protected function create_options_table ($conn, $table_name){
		if (!FFDB::existTable($table_name)){
			$sql = "CREATE TABLE ?n
			(
				`id` VARCHAR(50) NOT NULL,
				`value` LONGBLOB,
				PRIMARY KEY (`id`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function create_streams_table ($conn, $table_name){
		if (!FFDB::existTable($table_name)){
			$sql = "CREATE TABLE ?n
			(
				`id` INT NOT NULL,
				`name` VARCHAR(250),
				`value` LONGBLOB,
				PRIMARY KEY (`id`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function create_cache_table ($conn, $table_name) {
		if(!FFDB::existTable($table_name)){
			$sql = "CREATE TABLE ?n
			(
				`feed_id` VARCHAR(20) NOT NULL,
				`last_update` INT NOT NULL,
				`status` INT NOT NULL DEFAULT 0,
				`errors` BLOB,
				`settings` BLOB,
				`enabled` TINYINT(1) DEFAULT 0,
				`system_enabled` TINYINT(1) DEFAULT 1,
				`changed_time` INT DEFAULT 0,
				`cache_lifetime` INT DEFAULT 60,
				`send_email` TINYINT(1) DEFAULT 0,
				PRIMARY KEY (`feed_id`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function create_streams2sources_table($conn, $table_name){
		if(!FFDB::existTable($table_name)){
			$sql = "CREATE TABLE ?n
			(
				`feed_id` VARCHAR(20) NOT NULL,
				`stream_id` INT NOT NULL,
				PRIMARY KEY (`feed_id`, `stream_id`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function create_posts_table($conn, $table_name){
		if(!FFDB::existTable($table_name)) {
			$charset = $this->charset();
			$sql = "CREATE TABLE ?n
			(
				`feed_id` VARCHAR(20) NOT NULL,
				`post_id` VARCHAR(50) NOT NULL,
				`post_type` VARCHAR(10) NOT NULL,
				`post_text` BLOB,
				`post_permalink` VARCHAR(300),
				`post_header` VARCHAR(200),
				`user_nickname` VARCHAR(100),
				`user_screenname` VARCHAR(200),
				`user_pic` VARCHAR(300) NOT NULL,
				`user_link` VARCHAR(300),
				`rand_order` REAL,
				`creation_index` INT NOT NULL DEFAULT 0,
				`image_url` TEXT,
				`image_width` INT,
				`image_height` INT,
				`media_url` TEXT,
				`media_width` INT,
				`media_height` INT,
				`media_type` VARCHAR(100),
				`post_timestamp` INT,
				`smart_order` INT,
				`post_status` VARCHAR(15),
				`post_source` VARCHAR(300),
				`post_additional` VARCHAR(300),
				`user_bio` TEXT,
				`user_counts_media` INT,
				`user_counts_follows` INT,
				`user_counts_followed_by` INT,
				`location` VARCHAR(500),
				`carousel_size` INT,
				PRIMARY KEY (`post_id`, `post_type`, `feed_id`)
			) ?p";
			$conn->query($sql, $table_name, $charset);
		}
	}
	
	protected function create_post_media_table($conn, $table_name){
		if(!FFDB::existTable($table_name)) {
			$charset = $this->charset();
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
			$conn->query($sql, $table_name, $charset);
		}
	}
	
	protected function create_image_size_table($conn, $table_name) {
		if (!FFDB::existTable($table_name)){
			$charset = $this->charset();
			$sql = "CREATE TABLE ?n (
				`url` VARCHAR(50) NOT NULL,
				`width` INT,
				`height` INT,
				`creation_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				`original_url` VARCHAR(300),
				PRIMARY KEY (`url`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function create_snapshot_table($conn, $table_name){
		if( !FFDB::existTable($table_name) ) {
			$sql = "CREATE TABLE ?n (
				`id` INT NOT NULL AUTO_INCREMENT,
				`description` VARCHAR(20),
				`creation_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				`settings` LONGTEXT NOT NULL,
				`fb_settings` LONGTEXT,
				`version` VARCHAR(10) DEFAULT '2.0' NOT NULL,
				`dump` BLOB,
				PRIMARY KEY (`id`)
			) ?p";
			$conn->query($sql, $table_name, $this->charset());
		}
	}

	protected function create_comments_table ($conn, $table_name){
		if (!FFDB::existTable($table_name)){
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
			$conn->query($sql, $table_name, $this->charset());
		}
	}
	
	protected function charset(){
		$charset = FFDB::charset();
		if ( !empty( $charset ) ) {
			$charset = " CHARACTER SET {$charset}";
		}
		return $charset;
	}
	
	protected function collate(){
		$collate = FFDB::collate();
		if ( !empty( $collate ) ) {
			$charset_collate = " COLLATE {$collate}";
		}
		return $charset_collate;
	}
}