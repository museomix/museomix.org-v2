<?php namespace la\core\db;

use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;
use flow\db\LADBManager;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
abstract class LADBMigrationManager{
	const INIT_MIGRATION = '0.9999';
	
	protected $context;
	
	public function __construct($context) {
		$this->context = $context;
	}
	
	public final function migrate(){
		$version = $this->getDBVersion();
		/** @var LADBManager $dbm */
		$dbm = $this->context['db_manager'];
		$plugin_slug_down = $this->context['slug_down'];
		
		
		try{
			if (FFDB::beginTransaction()){
				$conn = FFDB::conn();
				
				if ($this->needStartInitMigration($version)){
					foreach ($this->getInitMigration() as $max_version => $migration){
						$migration->execute($conn, $dbm);
						FFDB::setOption($dbm->option_table_name, $plugin_slug_down. '_db_version', $max_version);
					}
				}
				else {
					foreach ( $this->getMigrations() as $migration ) {
						if ($this->needExecuteMigration($version, $migration->version())){
							$migration->execute($conn, $dbm);
							FFDB::setOption($dbm->option_table_name, $plugin_slug_down. '_db_version', $migration->version());
						}
					}
				}
				FFDB::commit();
			}
		} catch (\Exception $e){
			error_log($e->getTraceAsString());
			FFDB::rollbackAndClose();
			throw $e;
		}
	}
	
	/**
	 * Return the list of migration
	 * @return array
	 */
	protected abstract function migrations();
	
	private function getDBVersion(){
		$version = self::INIT_MIGRATION;
		$dbm = $this->context['db_manager'];
		if (FFDB::existTable($dbm->option_table_name)){
			$version = $dbm->getOption('db_version');
			if (false === $version){
				$e = new \Exception('Can`t get the db version of plugin');
				error_log($e->getTraceAsString());
				throw $e;
			}
		}
		return $version;
	}
	
	private function needStartInitMigration($version){
		return self::INIT_MIGRATION == $version || $version === false;
	}
	
	private function getInitMigration(){
		$migrations = $this->getMigrations();
		
		$max = self::INIT_MIGRATION;
		foreach ($migrations as $version => $migration) {
			if ($max < $version){
				$max = $version;
			}
		}
		return array($max => $migrations[self::INIT_MIGRATION]);
	}
	
	private function getMigrations(){
		$migrations = array();
		foreach ($this->migrations() as $class) {
			$clazz = new \ReflectionClass($class);
			/** @var ILADBMigration $migration */
			$migration = $clazz->newInstance();
			$migrations[$migration->version()] = $migration;
		}
		uksort($migrations, 'version_compare');
		
		return $migrations;
	}
	
	private function needExecuteMigration($db_version, $migration_version){
		$db = explode('.', $db_version);
		$migration = explode('.', $migration_version);
		if (intval($migration[0]) == intval($db[0])){
			return (intval($migration[1]) > $db[1]);
		}
		return (intval($migration[0]) > intval($db[0]));
	}
}