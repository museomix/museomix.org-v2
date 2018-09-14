<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
interface FFFeed {
    public function id();
    public function init($context, $feed);
    public function posts($is_empty_feed);
    public function errors();
    public function useCache();
	public function hasCriticalError();
}