<?php namespace flow;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FlowFlowException extends \ErrorException {
	public function __construct( $message = "", $code = 0, $filename = __FILE__, $lineno = __LINE__, $previous ) {
		parent::__construct( $message, $code, 1, $filename, $lineno, $previous );
	}
}

function ff_err2exc($code, $message, $filename, $lineno) {
	if (strrpos($filename, AS_PLUGIN_DIR) == 0){
		$e = new \FlowFlowException($message, $code, $filename, $lineno, null);
		throw $e;
	}
}

if (FF_USE_WP){
	set_error_handler('ff_err2exc', E_ALL & ~E_NOTICE &~ E_USER_NOTICE | E_STRICT);
	error_reporting(E_ALL | E_STRICT);
}
