<?php namespace flow;
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
class FlowFlowUpdater {
	private $info = null;

	function __construct($context) {
		$db = $context['db_manager'];
		if (false !== $db->getOption('registration_id')){
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
			add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
			add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
		}
	}

	public function modify_transient( $transient ) {
		if( isset($transient->checked) && $transient->checked) {
			$info = $this->get_repository_info();
			if (version_compare($info->plugin['version'], FlowFlow::VERSION) === 1){
				$plugin = array();
				$plugin['url'] = $info->plugin["url"];
				$plugin['slug'] = 'flow-flow';
		 		$plugin['new_version'] = $info->plugin['version'];
				$plugin['plugin'] = 'flow-flow/flow-flow.php';
				if (isset($info->plugin['download_url'])) $plugin['package'] = $info->plugin['download_url'];
				$transient->response[ 'flow-flow/flow-flow.php' ] = (object) $plugin;
			}
		}
		return $transient;
	}

	public function plugin_popup( $result, $action, $args ) {
		if( ! empty( $args->slug ) ) {
			if( $args->slug == 'flow-flow' ) {
				$info = $this->get_repository_info();
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
		}
		return $result;
	}

	public function after_install( $response, $hook_extra, $result ) {
		/** @var \WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem; // Get global FS object
		$destination = WP_PLUGIN_DIR . '/flow-flow/';
		$wp_filesystem->move( $result['destination'], $destination );
		$result['destination'] = $destination;
		$result['destination_name'] = 'flow-flow';
		return $result;
	}

	private function get_repository_info(){
		if ($this->info == null){
			global $flow_flow_context;
			$db = $flow_flow_context['db_manager'];
			$registration_id = $db->getOption('registration_id');

			$result = wp_remote_get('http://flow.looks-awesome.com/service/update/flow-flow.json');
			if (!is_wp_error($result) && isset($result['response']) && isset($result['response']['code']) && $result['response']['code'] == 200) {
				$settings = $db->getGeneralSettings()->original();
				$json = json_decode($result['body']);
				$purchase_code = $settings['purchase_code'];

				$info = new \stdClass();
				$info->basename = 'flow-flow';

				$info->plugin = array();
				$info->plugin["name"] = $json->item->name;
				$info->plugin['version'] = $json->item->version;
				$info->plugin['published_at'] = $json->item->updated_at;
				$info->plugin["url"] = $json->item->url;
				$info->plugin["description"] = $json->item->description;
				$info->plugin["changelog"] = $json->item->changelog;
				$info->plugin['download_url'] = $json->item->download_url . "?action=la_update&registration_id={$registration_id}&purchase_code={$purchase_code}";

				$info->author = array();
				$info->author["url"] = $json->author->url;
				$info->author["name"] = $json->author->name;

				$this->info = $info;
			}
		}
		return $this->info;
	}
}