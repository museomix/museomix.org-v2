<?php namespace la\core;

class LAActivatorFacade{
	private static $instance = null;
	
	public static function get(){
		if (is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private $activators;
	
	function __construct(){
		$this->activators = array();
		add_action( 'plugins_loaded', array($this, 'load_plugins'));
	}
	
	/**
	 * @param LAActivatorBase $activator
	 */
	public final function registry_activator($activator){
		$this->activators[$activator->slug()] = $activator;
	}
	
	public final function load_plugins(){
		/** @var LAActivatorBase $a */
		foreach ($this->activators as $a){
			$a->loadPlugin();
		}
	}
}