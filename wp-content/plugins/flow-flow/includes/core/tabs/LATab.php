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
interface LATab {
	public function id();
	public function flaticon();
	public function title();
	public function includeOnce($context);
}