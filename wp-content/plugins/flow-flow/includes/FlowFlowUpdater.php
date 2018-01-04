<?php namespace flow;
use la\core\LARemoteUpdater;

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
class FlowFlowUpdater extends LARemoteUpdater{
	
	protected function getUrlToPluginMetafileJson(){
		return 'http://flow.looks-awesome.com/service/update/flow-flow.json';
	}
	
	protected function getPlugin($info){
		$plugin = array(
				'name'              => $info->plugin["name"],
				'slug'              => $info->basename,
				'plugin'            => $info->basename . '/' . $info->basename . '.php',
				'version'           => $info->plugin['version'],
				'author'            => $info->author["name"],
				'author_profile'    => $info->author["url"],
				'last_updated'      => $info->plugin['published_at'],
				'homepage'          => $info->plugin["url"],
				'short_description' => $info->plugin["description"],
				'sections'          => array(
						'description'   => $info->plugin["description"],
						'changelog'       => $info->plugin["changelog"],
				)
		);
		if (isset($info->plugin['download_url'])) $plugin['download_link'] = $info->plugin['download_url'];
		return (object) $plugin;
	}
	
	protected function getPluginWithNewVersion($info){
		$plugin = array();
		$plugin['url'] = $info->plugin["url"];
		$plugin['slug'] = 'flow-flow';
		$plugin['new_version'] = $info->plugin['version'];
		$plugin['plugin'] = 'flow-flow/flow-flow.php';
		if (isset($info->plugin['download_url'])) $plugin['package'] = $info->plugin['download_url'];
		return (object) $plugin;
	}
}