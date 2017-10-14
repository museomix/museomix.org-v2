<?php namespace flow\db;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */

interface FFDBMigration {
	public function version();

	/**
	 * @param SafeMySQL $conn
	 * @param LADBManager $manager
	 */
	public function execute( $conn, $manager);
} 