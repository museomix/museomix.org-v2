<?php namespace flow\social;

if ( ! defined( 'WPINC' ) ) die;

/**
 * ff2
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2018 Looks Awesome
 */
class LASocialRequestException extends LASocialException {
	/** @var array */
	private $errors;

	/**
	 * LASocialRequestException constructor.
	 *
	 * @param string $url
	 * @param array $errors
	 * @param string $message
	 *
	 * @throws \Exception
	 * @internal param int $response
	 *
	 * @internal param string $message
	 */
	public function __construct($url, $errors, $message = '') {
		$this->errors = $errors;
		parent::__construct($message, array('url' => $url));
	}

	public function getRequestErrors(){
		return $this->errors;
	}

	public function __toString() {
		$text = parent::__toString();
		if (!empty($this->errors)){
			$text .= "\n Request errors:\n";
			$text .= print_r($this->errors, true);
			$text .= "\n";
		}
		return $text;
	}
}