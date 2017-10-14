<?php namespace flow\db\migrations;
use flow\db\FFDB;
use flow\db\FFDBMigration;

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
class FFMigration_2_8 implements FFDBMigration {

	public function version() {
		return '2.8';
	}

	public function execute($conn, $manager) {
		if (!FFDB::existColumn($manager->posts_table_name, 'smart_order'))
			$conn->query('ALTER TABLE ?n ADD ?n INT NULL', $manager->posts_table_name, 'smart_order');
		if (false !== ($feeds = $conn->getCol('SELECT DISTINCT `feed_id` FROM ?n', $manager->posts_table_name))){
			foreach ( $feeds as $feed ) {
				if (false === ($posts = $conn->getCol('SELECT `post_id` FROM ?n WHERE `feed_id` = ?s ORDER BY post_timestamp DESC', $manager->posts_table_name, $feed))){
					throw new \Exception($conn->conn->error);
				}
				$index = 0;
				foreach ( $posts as $post ) {
					if (false === $conn->query('UPDATE ?n SET `smart_order` = ?i WHERE `feed_id` = ?s AND `post_id` = ?s',
							$manager->posts_table_name, $index, $feed, $post)){
						throw new \Exception($conn->conn->error);
					}
					$index++;
				}
			}
		}
	}
}