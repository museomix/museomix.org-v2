<?php namespace flow\db\migrations;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_2_2 implements ILADBMigration{

	public function version() {
		return '2.2';
	}

	public function execute($conn, $manager) {
	}
}