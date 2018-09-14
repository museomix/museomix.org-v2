<?php namespace flow\social\cache;

/**
 * FlowSocial
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2018 Looks Awesome
 */
class FFImageSizeCacheBase{

	public function getOriginalUrl( $url ) {
		return '';
	}

	public function size( $url, $original_url = '' ) {
		return ['width' => 300, 'height' => 300];
	}

	public function save() {
	}
}