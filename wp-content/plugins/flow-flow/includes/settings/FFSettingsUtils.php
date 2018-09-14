<?php namespace flow\settings;
if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */

class FFSettingsUtils {
	const YEP = 'yep';
	const NOPE = 'nope';

	private static $length = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	private static $USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';

	public static function YepNope2ClassicStyleSafe($array, $key, $not_parsed_result = false){
		if (is_object($array)) $array = (array) $array;
		return isset($array[$key]) ? self::YepNope2ClassicStyle($array[$key], $not_parsed_result) : $not_parsed_result;
	}
	
	public static function YepNope2ClassicStyle($str, $not_parsed_result = false) {
		if (isset($str)){
			return ($str == self::YEP) ? true : false;
		}
		return $not_parsed_result;
	}
	
	public static function notYepNope2ClassicStyleSafe($array, $key, $not_parsed_result = true){
		if (is_object($array)) $array = (array) $array;
		return isset($array[$key]) ? self::notYepNope2ClassicStyle($array[$key], $not_parsed_result) : $not_parsed_result;
	}
	
	public static function notYepNope2ClassicStyle($str, $not_parsed_result = true) {
		if (isset($str)){
			return ($str == self::NOPE) ? true : false;
		}
		return $not_parsed_result;
	}

	public static function classicStyleDate($date, $style = 'classicStyleDate'){
		if ($style == 'agoStyleDate'){
			return '';
		}
		if (FF_USE_WP && $style == 'wpStyleDate'){
			$wpDateFormat = get_option('date_format') . ' ' . get_option('time_format');
			return date_i18n($wpDateFormat, $date);
		}
		$cur_time = time();
		$diff = $cur_time - $date;
		for ($i = sizeof(self::$length) - 1; ($i >= 0) && (($no = $diff / self::$length[$i]) <= 1); $i--) ;
		if ($i < 0) $i = 0;

		if ($i > 5)
			return FF_USE_WP ? date_i18n("M j Y",$date) : strftime('%h %e %Y', $date);
		return FF_USE_WP ? date_i18n("M j H:i",$date) : strftime('%h %e %H:%M', $date);
	}

	public static function get($url, $timeout = 60, $header = false, $log = true, $followLocation = true, $useIpv4 = true){
		$c = curl_init();
		curl_setopt($c, CURLOPT_USERAGENT, self::$USER_AGENT);
		curl_setopt($c, CURLOPT_URL,$url);
		curl_setopt($c, CURLOPT_POST, 0);
		curl_setopt($c, CURLOPT_FAILONERROR, true);

		// Enable if you have 'Network is unreachable' error
		if ($useIpv4) curl_setopt( $c, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		if ($followLocation) curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_AUTOREFERER, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($c, CURLOPT_VERBOSE, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		if (isset($_COOKIE['XDEBUG_SESSION']) && $_COOKIE['XDEBUG_SESSION'] == 'PHPSTORM')
			curl_setopt($c, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPSTORM');
		if ($timeout != null)   curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT_MS, 5000);
		if (is_array($header))  curl_setopt($c, CURLOPT_HTTPHEADER, $header);
		$page = ($followLocation) ? curl_exec($c) : self::curl_exec_follow($c);
		$error = curl_error($c);
		$errors = array();
		if (strlen($error) > 0){
			if ($log) {
				if (isset($_REQUEST['debug'])) {
					echo 'DEBUG:: <br>';
					var_dump($error);
					echo '<br>';
					var_dump(debug_backtrace());
					echo 'URL: ' . $url;
					echo '<br>-------<br>';
					error_log(print_r($error, true));
					error_log(print_r(debug_backtrace(), true));
					error_log($url);
				}
			}
			if (strpos($error, 'Failed to connect') !== false && strpos($error, 'Network is unreachable') !== false){
				curl_setopt($c, CURLOPT_FAILONERROR, false);
				$page = ($followLocation) ? curl_exec($c) : self::curl_exec_follow($c);
				$error2 = curl_error($c);
				if (strlen($error2) > 0){
					$error .= '. Please, enable "USE IPV4 PROTOCOL" option at the settings tab.';
					$errors[] = array('msg' => $error, 'url' => $url);
					error_log('FFFeedUtils line 110 :: ' . $error);
					error_log('FFFeedUtils line 111 :: ' . $error2);
				}
				curl_close($c);
				return array('response' => $page, 'errors' => $errors);
			}
			$errors[] = array('msg' => $error, 'url' => $url);
		}
		curl_close($c);
		return array('response' => $page, 'errors' => $errors);
	}

	/**
	 * @param int $templateWidth
	 * @param int $originalWidth
	 * @param int $originalHeight
	 * @return int|string
	 */
	public static function getScaleHeight($templateWidth, $originalWidth, $originalHeight){
		if (isset($originalWidth) && isset($originalHeight) && !empty($originalWidth)){
			$k = $templateWidth / $originalWidth;
			return (int)round( $originalHeight * $k );
		}
		return '';
	}
} 