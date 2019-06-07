<?php namespace flow\db;
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

class FFWPDB extends \wpdb{
	public function get_connection(){
		return $this->dbh;
	}
} 