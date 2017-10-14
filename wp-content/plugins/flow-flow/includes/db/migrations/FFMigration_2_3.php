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
class FFMigration_2_3 implements FFDBMigration {

	public function version() {
		return '2.3';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existTable($manager->image_cache_table_name)){
			$charset_collate = '';
			$charset = FFDB::charset();
			if ( !empty( $charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$charset}";
			}
			$collate = FFDB::collate();
			if ( !empty( $collate ) ) {
				$charset_collate .= " COLLATE {$collate}";
			}

			$sql = "CREATE TABLE ?n ( `url` VARCHAR(50) NOT NULL, `width` INT, `height` INT, `creation_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`url`) ) $charset_collate";
			$conn->query($sql, $manager->image_cache_table_name);
		}

		if (!FFDB::existColumn($manager->table_prefix . 'snapshots', 'dump')){
			$sql = "ALTER TABLE ?n ADD COLUMN ?n BLOB NULL";
			$conn->query($sql, $manager->table_prefix . 'snapshots', 'dump');
		}
	}
}