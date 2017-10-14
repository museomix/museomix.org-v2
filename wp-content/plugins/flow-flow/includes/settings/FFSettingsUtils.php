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
	public static function YepNope2ClassicStyleSafe($array, $key, $not_parsed_result = false){
		if (is_object($array)) $array = (array) $array;
		return isset($array[$key]) ? self::YepNope2ClassicStyle($array[$key], $not_parsed_result) : $not_parsed_result;
	}

    public static function YepNope2ClassicStyle($str, $not_parsed_result = false) {
        if (isset($str)){
            return ($str == 'yep') ? true : false;
        }
        return $not_parsed_result;
    }

	public static function notYepNope2ClassicStyleSafe($array, $key, $not_parsed_result = true){
		if (is_object($array)) $array = (array) $array;
		return isset($array[$key]) ? self::notYepNope2ClassicStyle($array[$key], $not_parsed_result) : $not_parsed_result;
	}

    public static function notYepNope2ClassicStyle($str, $not_parsed_result = true) {
        if (isset($str)){
            return ($str == 'nope') ? true : false;
        }
        return $not_parsed_result;
    }
} 