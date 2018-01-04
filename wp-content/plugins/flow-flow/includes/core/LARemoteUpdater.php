<?php namespace la\core;

abstract class LARemoteUpdater{
	protected $current_version;
	protected $context;
	
	private $info = null;
	
	function __construct($context) {
		$this->context = $context;
		$this->current_version = $context['version'];
		$db = $context['db_manager'];
		if (false !== $db->getOption('registration_id')){
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
			add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
			add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
		}
	}
	
	public final function modify_transient( $transient ) {
		if( isset($transient->checked) && $transient->checked) {
			$info = $this->getInfo();
			if (version_compare($info->plugin['version'], $this->current_version) === 1){
				$plugin = $this->getPluginWithNewVersion($info);
				$transient->response[ $plugin->plugin ] = $plugin;
			}
		}
		return $transient;
	}
	
	public final function plugin_popup( $result, $action, $args ) {
		if( ! empty( $args->slug ) ) {
			if( $args->slug == $this->context['slug'] ) {
				return $this->getPlugin($this->getInfo());
			}
		}
		return $result;
	}
	
	public final function after_install( $response, $hook_extra, $result ) {
		/** @var \WP_Filesystem_Direct $wp_filesystem */
		global $wp_filesystem; // Get global FS object
		$slug = $this->context['slug'];
		$destination = WP_PLUGIN_DIR . '/' . $slug .'/';
		$wp_filesystem->move( $result['destination'], $destination );
		$result['destination'] = $destination;
		$result['destination_name'] = $slug;
		return $result;
	}
	
	public final function getInfo(){
		if (is_null($this->info)){
			$this->info = $this->get_repository_info();
		}
		return $this->info;
	}
	
	protected abstract function getPlugin($info);
	protected abstract function getPluginWithNewVersion($info);
	protected abstract function getUrlToPluginMetafileJson();
	
	private function get_repository_info(){
		$db = $this->context['db_manager'];
		$registration_id = $db->getOption('registration_id');
		
		$result = wp_remote_get($this->getUrlToPluginMetafileJson());
		if (!is_wp_error($result) && isset($result['response']) && isset($result['response']['code']) && $result['response']['code'] == 200) {
			$settings = $db->getGeneralSettings()->original();
			if (is_array($settings) && isset($settings['purchase_code'])){
				$json = json_decode($result['body']);
				$purchase_code = $settings['purchase_code'];
				
				$info = new \stdClass();
				$info->basename = $this->context['slug'];
				
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
				
				return $info;
			}
		}
		return null;
	}
}