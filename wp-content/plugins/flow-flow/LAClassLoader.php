<?php
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class LAClassLoader {
	private static $instance = null;

	public static function get($root = null) {
		if(self::$instance == null) {
			self::$instance = new LAClassLoader($root);
		}
		return self::$instance;
	}

	private $root;

	private function __construct($root) {
		$this->root = $root;
	}

	public function loadClass($className) {
		if (0 === strpos($className, 'flow\\social\\')){
			$path = $this->root . 'libs/FlowSocial/src/flow';
			$cls = str_replace('flow', $path, $className);
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';
			/** @noinspection PhpIncludeInspection */
			require_once($path);
		}
		else if (0 === strpos($className, 'flow\\')){
			$path = $this->root . 'includes';
			$cls = str_replace('flow', $path, $className);
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';
			/** @noinspection PhpIncludeInspection */
			require_once($path);
		}
		else if (0 === strpos($className, 'la\\')){
			$path = $this->root . 'includes';
			$cls = str_replace('la', $path, $className);
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';
			/** @noinspection PhpIncludeInspection */
			require_once($path);
		}
	}

	public function register($with_config = false) {
		if ($with_config) {
			/** @noinspection PhpIncludeInspection */
			require_once($this->root . 'ff-config.php');
			/** @noinspection PhpIncludeInspection */
			require_once($this->root . 'ff-init.php');
		}
		spl_autoload_register(array(self::get(), 'loadClass'));
	}

	/**
	 * @return mixed
	 */
	public function getRoot() {
		return $this->root;
	}
} 