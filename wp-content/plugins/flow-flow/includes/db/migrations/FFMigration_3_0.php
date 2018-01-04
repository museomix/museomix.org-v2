<?php namespace flow\db\migrations;
use flow\db\FFDB;
use la\core\db\migrations\ILADBMigration;

if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFMigration_3_0 implements ILADBMigration{

	public function version() {
		return '3.0';
	}

	public function execute($conn, $manager) {
		if (FFDB::existColumn($manager->streams_table_name, 'layout')){
			$conn->query("ALTER TABLE ?n DROP `layout`",  $manager->streams_table_name);
		}

		$streams = $this->streams($conn, $manager->streams_table_name);
		foreach ( $streams as $stream ) {
			$options = unserialize($stream['value']);

			$options->{"layout"} = "masonry";
			$options->{"upic-pos"} = "timestamp";
			$options->{"upic-style"} = "round";
			$options->{"icon-style"} =  "label1";
			$options->{"icons-style"} =  "outline";
			$options->{"c-desktop"} =  "5";
			$options->{"c-laptop"} =  "4";
			$options->{"c-tablet-l"} =  "3";
			$options->{"c-tablet-p"} =  "2";
			$options->{"c-smart-l"} =  "2";
			$options->{"c-smart-p"} =  "1";
			$options->{"s-desktop"} =  "15";
			$options->{"s-laptop"} =  "15";
			$options->{"s-tablet-l"} =  "10";
			$options->{"s-tablet-p"} =  "10";
			$options->{"s-smart-l"} =  "5";
			$options->{"s-smart-p"} =  "5";
			$options->{"m-c-desktop"} =  "5";
			$options->{"m-c-laptop"} =  "4";
			$options->{"m-c-tablet-l"} =  "3";
			$options->{"m-c-tablet-p"} =  "2";
			$options->{"m-c-smart-l"} =  "2";
			$options->{"m-c-smart-p"} =  "1";
			$options->{"m-s-desktop"} =  "15";
			$options->{"m-s-laptop"} =  "15";
			$options->{"m-s-tablet-l"} =  "10";
			$options->{"m-s-tablet-p"} =  "10";
			$options->{"m-s-smart-l"} =  "5";
			$options->{"m-s-smart-p"} =  "5";
			$options->{"j-h-desktop"} =  "260";
			$options->{"j-h-laptop"} =  "240";
			$options->{"j-h-tablet-l"} =  "220";
			$options->{"j-h-tablet-p"} =  "200";
			$options->{"j-h-smart-l"} =  "180";
			$options->{"j-h-smart-p"} =  "160";
			$options->{"j-s-desktop"} =  "0";
			$options->{"j-s-laptop"} =  "0";
			$options->{"j-s-tablet-l"} =  "0";
			$options->{"j-s-tablet-p"} =  "0";
			$options->{"j-s-smart-l"} =  "0";
			$options->{"j-s-smart-p"} =  "0";
			$options->{"g-ratio-w"} =  "1";
			$options->{"g-ratio-h"} =  "2";
			$options->{"g-ratio-img"} =  "1/2";
			$options->{"g-overlay"} =  "nope";
			$options->{"m-overlay"} =  "nope";
			$options->{"template"} = array('header', 'text', 'image', 'meta');
			$value = serialize($options);

			if ( false === $conn->query( 'UPDATE ?n SET `value` = ?s WHERE `id` = ?s',
					$manager->streams_table_name, $value, $stream['id'] ) ) {
				throw new \Exception();
			}

			$options->id = $stream['id'];
			$manager->generateCss($options);
		}
	}

	private function streams($conn, $table_name){
		if (false !== ($result = $conn->getAll('SELECT `id`, `name`, `value` FROM ?n ORDER BY `id`',
				$table_name))){
			return $result;
		}
		return array();
	}
}