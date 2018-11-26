<?php namespace flow\tabs;

use flow\settings\FFSnapshotManager;
use la\core\tabs\LATab;

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
class FFBackupTab implements LATab {
	public function __construct() {
	}

	public function id() {
		return 'backup-tab';
	}

	public function flaticon() {
		return 'flaticon-data';
	}

	public function title() {
		return 'Database';
	}

	public function includeOnce( $context ) {
		$manager            = new FFSnapshotManager( $context );
		$context['backups'] = $manager->getSnapshots();
		/** @noinspection PhpIncludeInspection */
		include_once($context['root']  . 'views/backup.php');
	}
}