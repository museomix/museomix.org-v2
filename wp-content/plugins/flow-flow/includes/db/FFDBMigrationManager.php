<?php namespace flow\db;

use la\core\db\LADBMigrationManager;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Insta Flow.
 *
 * @package   Insta_Flow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class FFDBMigrationManager extends LADBMigrationManager{
	
	protected function migrations(){
		$result = array();
		foreach ( glob($this->context['root'] . 'includes/db/migrations/FFMigration_*.php') as $filename ) {
			$result[] = 'flow\\db\\migrations\\' . basename($filename, ".php");
		}
		return $result;
	}
}