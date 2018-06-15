<?php namespace flow\db\migrations;

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
class FFMigration_3_6 implements ILADBMigration{
	public function version() {
		return '3.6';
	}

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager ) {
		$cache_table_name = $manager->cache_table_name;
		$all = $conn->getAll('select * from ?n', $cache_table_name);
		foreach ( $all as $source ) {
			$cache_lifetime = (int) $source['cache_lifetime'];
			if ($cache_lifetime < 60) {
				if (isset($source['settings'])){
					$settings = unserialize($source['settings']);
					if (is_object($settings)) {
						$settings = (array) $settings;
					}
					if (($settings['type'] == 'facebook')){
						$conn->query('UPDATE ?n SET `cache_lifetime` = ?i WHERE `feed_id` = ?s', $cache_table_name, 60, $source['feed_id']);
					}
				}
			}
		}
	}
}