<?php namespace flow\db\migrations;
use la\core\db\migrations\ILADBMigration;

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
class FFMigration_2_9 implements ILADBMigration{

	public function version() {
		return '2.9';
	}

	public function execute($conn, $manager) {
		$options = $manager->getOption('options', true);
		if ($options === false) $options = array();
		if (!isset($options['soundcloud_api_key'])) $options['soundcloud_api_key'] = '';
		$manager->setOption('options', $options, true);
	}
}