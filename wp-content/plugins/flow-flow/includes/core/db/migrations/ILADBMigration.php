<?php namespace la\core\db\migrations;
use flow\db\LADBManager;
use flow\db\SafeMySQL;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
interface ILADBMigration {
	public function version();

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager);
} 