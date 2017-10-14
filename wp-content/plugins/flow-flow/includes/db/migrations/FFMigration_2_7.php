<?php namespace flow\db\migrations;
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
class FFMigration_2_7 implements FFDBMigration {

	public function version() {
		return '2.7';
	}

	public function execute($conn, $manager) {
		$options = $manager->getOption('options', true);
		if ($options === false) $options = array();
		if (!isset($options['linkedin_api_key'])) $options['linkedin_api_key'] = '';
		if (!isset($options['linkedin_secret_key'])) $options['linkedin_secret_key'] = '';
		if (!isset($options['linkedin_access_token'])) $options['linkedin_access_token'] = '';
		$manager->setOption('options', $options, true);
	}
}