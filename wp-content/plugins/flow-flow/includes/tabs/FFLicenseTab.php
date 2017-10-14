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

class FFLicenseTab implements LATab{
	private $activated;

	public function __construct($activated) {
		$this->activated = $activated;
	}

	public function id() {
		return "license-tab";
	}

	public function flaticon() {
		return 'flaticon-like';
	}

	public function title() {
		return $this->activated ? 'License' : '!Activate';
	}

	public function includeOnce( $context ) {
		include_once($context['root']  . 'views/license.php');
	}
}