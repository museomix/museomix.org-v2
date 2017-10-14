<?php namespace flow\cache;
if ( ! defined( 'WPINC' ) ) die;

use flow\db\FFDB;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFImageSizeCacheManager {
	const FF_IMG_CACHE_SIZE = 1000;

	private static $instance = null;
	/**
	 * @return FFImageSizeCacheManager
	 */
	public static function get() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private $size;
	private $image_cache;
	private $table_name;
	private $new_images = array();

	function __construct() {
		global $flow_flow_context;
		$db = $flow_flow_context['db_manager'];
		$this->table_name = $db->image_cache_table_name;

		$sql = "SELECT `url`, `width`, `height`, `original_url`  FROM ?n ORDER BY `creation_time` DESC LIMIT ?i";
		if (false === ($result = FFDB::conn()->getIndCol('url', $sql, $this->table_name, self::FF_IMG_CACHE_SIZE))) {
			$result = array();
		}
		$this->size = sizeof($result);
		$this->image_cache = $result;
	}

	public function getOriginalUrl($url){
		$h = hash('md5', $url);
		if (array_key_exists($h, $this->image_cache)){
			return $this->image_cache[$h]['original_url'];
		}
		return '';
	}
	
	/**
	 * @param string $url
	 * @param string $original_url
	 *
	 * @return array
	 */
	public function size($url, $original_url = ''){
		$h = hash('md5', $url);
		if ($original_url != '') $url = $original_url;
		if (!array_key_exists($h, $this->image_cache)){
			try{
				$time = date("Y-m-d H:i:s", time());
				if ($url && !empty($url)) {
					if (isset($_REQUEST['debug'])){
						ini_set('upload_max_filesize', '16M');
						ini_set('post_max_size', '16M');
						ini_set('max_input_time', '60');
					}
					@list($width, $height) = getimagesize($url);
					if (empty($width) || empty($height)){
						@list($width, $height) = $this->alternativeGetImageSize($url);
						if (empty($width) || empty($height)){
							$width  = -1;
							$height = -1;
						}
					}
					$data = array('creation_time' => $time, 'width' => $width, 'height' => $height, 'original_url' => $original_url);
				} else $data = array('creation_time' => $time, 'width' => -1, 'height' => -1, 'original_url' => $original_url);
				if ($data['width'] > 0 && $data['height'] > 0){
					$this->image_cache[$h] = $data;
					$this->new_images[$h] = $data;
				}
				return $data;
			} catch (\Exception $e) {
				error_log($url);
				error_log($e->getMessage());
				error_log($e->getTraceAsString());
				return array('time' => time(), 'width' => -1, 'height' => -1, 'error' => $e->getMessage());
			}
		}
		return $this->image_cache[$h];
	}


	/**
	 * @param array $result
	 * @return void
	 */
	private function removeOldRecords( $result ) {
		//TODO
	}

	/**
	 * @return void
	 */
	public function save() {
		if (sizeof($this->new_images) > 0 && FFDB::beginTransaction()){

			foreach ( $this->new_images as $url => $image ) {
				FFDB::conn()->query('INSERT INTO ?n SET `url` = ?s, ?u ON DUPLICATE KEY UPDATE ?u',
					$this->table_name, $url, $image, array('creation_time' => $image['creation_time']));
			}
			FFDB::commit();
		}
		FFDB::rollback();
	}

	private function alternativeGetImageSize($url){
		$raw = $this->ranger($url);
		if ($raw === 'URL signature expired'){
			return array(-1, -1);
		}
		$im = imagecreatefromstring($raw);
		$width = imagesx($im);
		$height = imagesy($im);
		imagedestroy($im);
		return array($width, $height);
	}

	private function ranger($url){
		$headers = array(
			"Range: bytes=0-32768"
		);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 5000);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}
}