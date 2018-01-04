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
class FFMigration_2_6 implements ILADBMigration{

	public function version() {
		return '2.6';
	}

	public function execute($conn, $manager) {
		$options = $manager->getOption('options', true);
		if ($options === false) $options = array();
		if (!isset($options['general-settings-ipv4'])) $options['general-settings-ipv4'] = 'nope';
		if (!isset($options['general-settings-https'])) $options['general-settings-https'] = 'nope';
		$manager->setOption('options', $options, true);
	}
}