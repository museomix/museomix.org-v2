<?php namespace flow\tabs;

use la\core\tabs\LATab;

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

class FFModerationTab implements LATab{
	public function __construct() {
	}

	public function id() {
		return "moderation-tab";
	}

	public function flaticon() {
		return 'flaticon-like';
	}

	public function title() {
		return 'Moderation';
	}

	public function includeOnce( $context ) {
		/** @noinspection PhpIncludeInspection */
		include_once($context['root']  . 'views/moderation.php');
	}
}