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
class LASocialException extends \Exception {
	/** @var array */
	protected $options;
	/** @var mixed */
	private $problem;

	/**
	 * LASocialException constructor.
	 *
	 * @param mixed $message
	 * @param array $options
	 * @param mixed $problem
	 */
	public function __construct( $message = '', $options = array(), $problem = null) {
		parent::__construct($message);
		$this->options = $options;
		$this->problem = $problem;
	}

	public function __toString() {
		$text = parent::__toString();
		if (!is_null($this->problem)){
			$text .= "\n Problem object:\n";
			$text .= print_r($this->problem, true);
			$text .= "\n";
		}
		return $text;
	}


	/**
	 * @param mixed $message
	 */
	public function setMessage( $message ) {
		$this->message = $message;
	}

	public function getSocialError(){
		$message = $this->filter_error_message($this->getMessage());
		if (!empty($this->getMessage())){
			$this->options['message'] = $message;
		}
		return $this->options;
	}

	/**
	 * @return mixed
	 */
	public function getProblem() {
		return $this->problem;
	}

	private function filter_error_message($message){
		if (is_array($message)){
			if (sizeof($message) > 0 && isset($message[0]['msg'])){
				return utf8_encode(stripslashes(htmlspecialchars($message[0]['msg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
			}
			else {
				return '';
			}
		}
		return stripslashes(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
	}
}