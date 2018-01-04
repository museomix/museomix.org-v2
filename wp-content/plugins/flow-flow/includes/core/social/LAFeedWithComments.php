<?php namespace la\core\social;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Social-Streams core
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
interface LAFeedWithComments {
	public function getComments($mediaId);
}