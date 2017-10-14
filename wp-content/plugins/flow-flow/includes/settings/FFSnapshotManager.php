<?php namespace flow\settings;
if ( ! defined( 'WPINC' ) ) die;

use flow\db\FFDB;
use flow\db\FFDBManager;
use flow\FlowFlow;

/**
 * Flow-Flow
 *
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `FlowFlowAdmin.php`
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFSnapshotManager {
	private $context;
	const VERSION = '2.10';

	public function __construct($context) {
		$this->context = $context;
	}

	public function getSnapshots(){
		$result = array();
		$rows = FFDB::conn()->getAll('SELECT * FROM ?n ORDER BY `creation_time` DESC', FF_SNAPSHOTS_TABLE_NAME);
		foreach ( $rows as $row ) {
			$sn = new \stdClass();
			$sn->id = $row['id'];
			$sn->description = $row['description'];
			$sn->creation_time = $row['creation_time'];
			$sn->settings = $row['settings'];
			$sn->version = $row['version'];
			$sn->outdated = version_compare(FFSnapshotManager::VERSION, $row['version'], '>=');
			$result[] = $sn;
		}
		return $result;
	}

	public function processAjaxRequest() {
		$result = array();
		if (isset($_REQUEST['action'])){
			/** @var FFDBManager $db */
			$dbm = $this->context['db_manager'];
			$dbm->dataInit();

			try{
				if (false === FFDB::beginTransaction()) throw new \Exception('Don`t started transaction');

				switch ($_REQUEST['action']){
					case 'create_backup':
						$result = $this->createBackup($dbm);
						break;
					case 'restore_backup':
						$result = $this->restoreBackup($dbm);
						break;
					case 'delete_backup':
						$result = $this->deleteBackup($dbm);
						break;
				}
				FFDB::commit();
			}
			catch (\Exception $e){
				FFDB::rollbackAndClose();
				error_log($e->getMessage());
				error_log($e->getTraceAsString());

				switch ($_REQUEST['action']){
					case 'create_backup':
						$result = array('backed_up' => false);
						break;
					case 'restore_backup':
						$result = array('restore' => false);
						break;
					case 'delete_backup':
						$result = array('deleted' => false);
						break;
				}
			}

		}
		echo json_encode($result);
		die();
	}

	/**
	 * @param FFDBManager $dbm
	 * @return array
	 */
	public function createBackup ($dbm) {
		$all = array();
		$description = '';//TODO add description for snapshot

		$options = FFDB::conn()->getAll('SELECT `id`, `value` FROM ?n', $dbm->option_table_name);
		foreach ( $options as $option ) {
			$all[$option['id']] = $option['value'];
		}
		$all['streams'] = $dbm->streams();
		$all['sources'] = $dbm->sources();
		$result = gzcompress(serialize($all), 6);

		FFDB::conn()->query("INSERT INTO ?n (`description`, `settings`, `dump`, `version`) VALUES(?s, ?s, ?s, ?s)", FF_SNAPSHOTS_TABLE_NAME, $description, '', $result, FlowFlow::VERSION);

		return array('backed_up' => true, 'result' => FFDB::conn()->affectedRows());
	}

	/**
	 * @param FFDBManager $dbm
	 * @return array
	 */
	public function restoreBackup ($dbm) {
		if (false !== ($dump = FFDB::conn()->getOne('SELECT `dump` FROM ?n WHERE id=?s', FF_SNAPSHOTS_TABLE_NAME, $_REQUEST['id']))){
			$all = gzuncompress($dump);
			$all = unserialize($all);
			unset($dump);

			foreach ( $dbm->sources() as $id => $source ) {
				$dbm->deleteFeed($id);
			}

			foreach ( $all['sources'] as $source ) {
				$dbm->modifySource($source);
			}
			unset($all['sources']);

			$dbm->dataInit();

			foreach ( $dbm->streams() as $stream ) {
				FFDB::deleteStream($stream['id']);
			}

			foreach ( $all['streams'] as $stream ) {
				$obj = (object)$stream;
				FFDB::setStream($obj->id, $obj);
				$dbm->generateCss($obj);
			}
			unset($all['streams']);

			foreach ( $all as $key => $value ) {
				$key = strpos($key, 'flow_flow_') === 0 ? str_replace('flow_flow_', '', $key) : $key;
				$dbm->setOption($key, $value);
			}
			$dbm->clean();
			return array('restore' => true);
		}
		else {
			return array('found' => false);
		}
	}

	/**
	 * @param FFDBManager $dbm
	 * @return array
	 */
	public function deleteBackup ($dbm) {
		$op = FFDB::conn()->query ('DELETE FROM ?n WHERE `id`=?s', FF_SNAPSHOTS_TABLE_NAME, $_REQUEST['id']);
		return array('deleted' => (false !== $op));
	}
} 