<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFMigration_2_5 implements ILADBMigration{

	public function version() {
		return '2.5';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existColumn($manager->posts_table_name, 'post_timestamp')){
			$conn->query("ALTER TABLE ?n ADD COLUMN ?n INT", $manager->posts_table_name, 'post_timestamp');
		}
		if (FFDB::existColumn($manager->posts_table_name, 'post_date')){
			$conn->query("ALTER TABLE ?n DROP `post_date`",  $manager->posts_table_name);
		}

		$conn->query('DELETE FROM ?n', $manager->cache_table_name);
	}
}