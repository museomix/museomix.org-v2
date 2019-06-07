<?php namespace la\core\tabs;

if ( ! defined( 'WPINC' ) ) die;

/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
class LAAuthTab implements LATab {
	private $prefix;
	
	public function __construct($tab_prefix) {
		$this->prefix = $tab_prefix;
	}
	
	public function id() {
		return $this->prefix . '-auth-tab';
	}

	public function flaticon() {
		return 'flaticon-user';
	}

	public function title() {
		return 'Auth';
	}

	public function includeOnce( $context ) {
		/** @noinspection PhpIncludeInspection */
		include_once($context['root']  . 'views/auth.php');
	}
}