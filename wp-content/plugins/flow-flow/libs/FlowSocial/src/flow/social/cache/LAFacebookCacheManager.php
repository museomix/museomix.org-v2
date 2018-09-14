<?php namespace flow\social\cache;
if ( ! defined( 'WPINC' ) ) die;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
interface LAFacebookCacheManager {
	public function getAccessToken();
	public function getError();
	public function clean();
	public function save($token, $expires);

	public function startCounter();
	public function stopCounter();
	public function hasLimit();
	public function addRequest();

	public function getIdPosts($feedId);
}