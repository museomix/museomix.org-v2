<?php namespace la\core\tabs;

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

class LALicenseTab implements LATab{
	private $prefix;
	private $activated;

	public function __construct($tab_prefix, $activated) {
		$this->prefix = $tab_prefix;
		$this->activated = $activated;
	}

	public function id() {
		return $this->prefix . "-license-tab";
	}

	public function flaticon() {
		return 'flaticon-like';
	}

	public function title() {
		return $this->activated ? 'License' : '<i class="flaticon-error" style="display: inline-block;"></i> Activate';
	}

	public function includeOnce( $context ) {
		$context['activated'] = $this->activated;
		/** @noinspection PhpIncludeInspection */
		include_once($context['root']  . 'views/license.php');
	}
}