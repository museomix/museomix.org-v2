<?php namespace flow\tabs;
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
class LAGeneralTab implements LATab {
	public function __construct() {
	}

	public function id() {
		return 'general-tab';
	}

	public function flaticon() {
		return 'flaticon-settings';
	}

	public function title() {
		return 'Settings';
	}

	public function includeOnce( $context ) {
		include_once($context['root']  . 'views/general.php');
	}
}