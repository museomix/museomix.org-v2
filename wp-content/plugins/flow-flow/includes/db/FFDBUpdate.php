<?php namespace flow\db;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFDBUpdate {
	public static function create_options_table ($table_name) {
		if (!FFDB::existTable($table_name)){
			$charset = FFDB::charset();
			if ( !empty( $charset ) ) {
				$charset = " CHARACTER SET {$charset}";
			}

			$sql = "
			CREATE TABLE `" . $table_name . "`
			(
			`id` VARCHAR(50) NOT NULL,
				`value` LONGBLOB,
				PRIMARY KEY (`id`)
			){$charset}";
			FFDB::conn()->query($sql);
		}
	}

	public static function create_streams_table ($table_name) {
		if (!FFDB::existTable($table_name)){
			$charset = FFDB::charset();
			if ( !empty( $charset ) ) {
				$charset = " CHARACTER SET {$charset}";
			}

			$sql = "
			CREATE TABLE `" . $table_name . "`
			(
				`id` INT NOT NULL,
				`name` VARCHAR(250),
				`layout` VARCHAR(50),
				`feeds` LONGBLOB,
				`value` LONGBLOB,
				PRIMARY KEY (`id`)
			){$charset}";
			FFDB::conn()->query($sql);
		}
	}

	public static function create_cache_table ($cache_table, $posts_table, $streams2sources_table) {
		/*
		 * We'll set the default character set and collation for this table.
		 * If we don't do this, some characters could end up being converted
		 * to just ?'s when saved in our table.
		 */
		$charset_collate = '';

		$charset = FFDB::charset();
		if ( !empty( $charset ) ) {
			$charset_collate = " CHARACTER SET {$charset}";
			$charset = " CHARACTER SET {$charset}";
		}

		$collate = FFDB::collate();
		if ( !empty( $collate ) ) {
			$charset_collate .= " COLLATE {$collate}";
		}

		if(!FFDB::existTable($cache_table)){
			$sql = "
		CREATE TABLE `{$cache_table}`
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
			PRIMARY KEY (`feed_id`)
		){$charset}";
			FFDB::conn()->query($sql);
		}

		if(!FFDB::existTable($streams2sources_table)){
			$sql = "
		CREATE TABLE `{$streams2sources_table}`
		(
			`feed_id` VARCHAR(20) NOT NULL,
			`stream_id` INT NOT NULL,
			PRIMARY KEY (`feed_id`, `stream_id`)
		){$charset}";
			FFDB::conn()->query($sql);
		}

		if(!FFDB::existTable($posts_table)) {
			$sql = "
		CREATE TABLE `{$posts_table}`
		(
    		`feed_id` VARCHAR(20) NOT NULL,
			`post_id` VARCHAR(50) NOT NULL,
    		`post_type` VARCHAR(10) NOT NULL,
		    `post_date` TIMESTAMP,
		    `post_text` BLOB,
		    `post_permalink` VARCHAR(300),
		    `post_header` VARCHAR(200){$charset_collate},
			`user_nickname` VARCHAR(100){$charset_collate},
		    `user_screenname` VARCHAR(200){$charset_collate},
		    `user_pic` VARCHAR(300) NOT NULL,
		    `user_link` VARCHAR(300),
		    `rand_order` REAL,
		    `creation_index` INT NOT NULL DEFAULT 0,
		    `image_url` VARCHAR(500),
		    `image_width` INT,
		    `image_height` INT,
		    `media_url` VARCHAR(500),
		    `media_width` INT,
		    `media_height` INT,
		    `media_type` VARCHAR(100),
		    PRIMARY KEY (`post_id`, `post_type`, `feed_id`)
		){$charset}";
			FFDB::conn()->query($sql);
		}
	}

	public static function create_snapshot_table () {
		if( !FFDB::existTable(FF_SNAPSHOTS_TABLE_NAME) ) {
			/*
			 * We'll set the default character set and collation for this table.
			 * If we don't do this, some characters could end up being converted
			 * to just ?'s when saved in our table.
			 */
			$charset_collate = '';

			$charset = FFDB::charset();
			if ( !empty( $charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$charset}";
			}

			$collate = FFDB::collate();
			if ( !empty( $collate ) ) {
				$charset_collate .= " COLLATE {$collate}";
			}

			$sql = "CREATE TABLE `" . FF_SNAPSHOTS_TABLE_NAME . "` (
				id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
				description VARCHAR(20),
				creation_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				settings LONGTEXT NOT NULL,
				fb_settings LONGTEXT,
				version VARCHAR(10) DEFAULT '2.0' NOT NULL
			) $charset_collate;";
			FFDB::conn()->query($sql);
		}
	}

	public static function create_image_size_table($table_name) {
		if (!FFDB::existTable($table_name)){
			$charset_collate = '';
			$charset = FFDB::charset();
			if ( !empty( $charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$charset}";
			}
			$collate = FFDB::collate();
			if ( !empty( $collate ) ) {
				$charset_collate .= " COLLATE {$collate}";
			}
			$sql = "CREATE TABLE ?n ( `url` VARCHAR(50) NOT NULL, `width` INT, `height` INT, `creation_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `original_url` VARCHAR(300), PRIMARY KEY (`url`) ) $charset_collate";
			FFDB::conn()->query($sql, $table_name);
		}
	}

	public static function remove_all_tables($prefix) {
		FFDB::dropTable($prefix . 'streams');
		FFDB::dropTable($prefix . 'snapshots');
		FFDB::dropTable($prefix . 'posts');
		FFDB::dropTable($prefix . 'options');
		FFDB::dropTable($prefix . 'image_cache');
		FFDB::dropTable($prefix . 'cache');
		FFDB::dropTable($prefix . 'streams_sources');
	}
}