<?php namespace flow;

use la\core\LAActivatorBase;
use flow\db\FFDBMigrationManager;
use flow\db\FFDBManager;
use flow\settings\FFSnapshotManager;
use flow\cache\FFFacebookCacheAdapter;
use flow\db\FFDB;

class FlowFlowActivator extends LAActivatorBase{
	/**
	 * Use this method fpr old php version
	 * @deprecated
	 */
	public function initWPWidget(){
		if (!defined('FF_ENABLE_WIDGET') || FF_ENABLE_WIDGET){
			$widget = new FlowFlowWPWidget();
			$widget->setContext($this->context);
			register_widget($widget);
		}
	}
	
	/**
	 * Use this method fpr old php version
	 * @deprecated
	 */
	public function initVCIntegration(){
		$db = $this->context['db_manager'];
		
		//Important!
		//It will be execute before migrations!
		//Need to check exist tables and fields!
		$streams = array();
		if (FFDB::existTable($db->streams_table_name)) $streams = FFDB::streams($db->streams_table_name);
		
		$stream_options = array();
		if(sizeof($streams)){
			foreach($streams as $id => $stream){
				$stream_options['Stream #' . $id . ( $stream['name'] ? ' - ' . $stream['name'] : '')] = $id;
			}
		}
		vc_map( array(
				"name" => __("Social Stream"),
				'admin_enqueue_css' => array($this->context['plugin_url'] . $this->context['slug'] . '/css/admin-icon.css'),
				'front_enqueue_css' => array($this->context['plugin_url'] . $this->context['slug'] . '/css/admin-icon.css'),
				'icon' => 'streams-icon',
				"description" => __("Flow-Flow plugin social stream"),
				"base" => "ff",
				"category" => __('Social'),
				"weight" => 0,
				"params" => array(
						array(
								'type' => 'dropdown',
								'class' => '',
								'admin_label' => true,
								"holder" => "div",
								"class" => "",
								"heading" => __("Choose stream to place on page:" ),
								"description" => "Please create and edit stream on plugin's page in admin.",
								"param_name" => "id",
								"value" => $stream_options,
								"std" => '--'
						)
				)
		));
	}
	
	protected function checkPlugin(){
		$mm = new FFDBMigrationManager($this->context);
		$mm->migrate();
		unset($mm);
	}
	
	protected function initContext($file){
		/** @var wpdb $wpdb */
		$wpdb = $GLOBALS['wpdb'];
		
		$context = array(
				'root'              => plugin_dir_path($file),
				'slug'              => 'flow-flow',
				'slug_down'         => 'flow_flow',
				'plugin_url'        => plugin_dir_url(dirname($file).'/'),
				'admin_url'         => admin_url('admin-ajax.php'),
				'table_name_prefix' => $wpdb->prefix . 'ff_',
				'version' 			=> '3.2.25',
				'faq_url' 			=> 'http://docs.social-streams.com/'
		);
		$adapter = new FFFacebookCacheAdapter();
		$context['facebook_cache'] = $adapter;
		$context['db_manager'] = new FFDBManager($context);
		$adapter->setContext($context);
		return $context;
	}
	
	protected function checkEnvironment(){
		if(version_compare(PHP_VERSION, '5.3.0') == -1){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires PHP version 5.3.0 or higher. Pls update your PHP version or ask hosting support to do this for you, you are using old and unsecure one' );
		}
		
		if(!function_exists('curl_version')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires curl extension for php. Please install/enable this extension or ask your hosting to help you with this.' );
		}
		
		if(!function_exists('mysqli_connect')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( '<b>Flow-Flow Social Stream</b> plugin requires mysqli extension for MySQL. Please install/enable this extension on your server or ask your hosting to help you with this. <a href="http://php.net/manual/en/mysqli.installation.php">Installation guide</a>' );
		}
	}
	
	protected function singleSiteActivate(){
		$this->checkPlugin();
	}
	
	protected function singleSiteDeactivate(){
		wp_clear_scheduled_hook( 'flow_flow_load_cache' );
		wp_clear_scheduled_hook( 'flow_flow_load_cache_4disabled' );
		wp_clear_scheduled_hook( 'flow_flow_email_notification' );
	}
	
	protected function beforePluginLoad(){
		parent::beforePluginLoad();
		
		if (! defined('FF_AJAX_URL')) {
			$admin = function_exists('current_user_can') && current_user_can('manage_options');
			if (!$admin && defined('FF_ALTERNATE_GET_DATA') && FF_ALTERNATE_GET_DATA){
				$this->setContextValue('ajax_url', plugins_url( 'ff.php', __FILE__ ));
			}
			else {
				$this->setContextValue('ajax_url', admin_url('admin-ajax.php'));
			}
		}
		
		new FlowFlowUpdater($this->context);
	}
	
	protected function registrationCronActions(){
		$this->addCronInterval('minute', array('interval' => MINUTE_IN_SECONDS, 'display' => 'Once Minute'));
		$this->addCronInterval('sex_hours', array('interval' => MINUTE_IN_SECONDS * 60 * 6, 'display' => 'Sex hours'));
		add_filter('cron_schedules', array($this, 'getCronIntervals'));
		
		$time = time();
		$ff = FlowFlow::get_instance($this->context);
		
		add_action('flow_flow_load_cache', array($ff, 'refreshCache'));
		if(false == wp_next_scheduled('flow_flow_load_cache')){
			wp_schedule_event($time, 'minute', 'flow_flow_load_cache');
		}
		
		add_action('flow_flow_load_cache_4disabled', array($ff, 'refreshCache4Disabled'));
		if(false == wp_next_scheduled('flow_flow_load_cache_4disabled')){
			wp_schedule_event($time, 'sex_hours', 'flow_flow_load_cache_4disabled');
		}

		add_action('flow_flow_email_notification', array($ff, 'emailNotification'));
		if(false == wp_next_scheduled('flow_flow_email_notification')){
			wp_schedule_event($time, 'daily', 'flow_flow_email_notification');
		}
	}
	
	protected function registrationAjaxActions(){
		/** @var FFDBManager $dbm */
		$dbm = $this->context['db_manager'];
		$slug_down = $this->context['slug_down'];
		$ff = FlowFlow::get_instance($this->context);

		// public endpoints
		add_action('wp_ajax_fetch_posts', array( $ff, 'processAjaxRequest'));
		add_action('wp_ajax_nopriv_fetch_posts', array( $ff, 'processAjaxRequest'));
        add_action('wp_ajax_load_cache', array( $ff, 'processAjaxRequestBackground'));
        add_action('wp_ajax_nopriv_load_cache', array( $ff, 'processAjaxRequestBackground'));
        add_action('wp_ajax_' . $slug_down . '_load_comments_and_carousel', array( $ff, 'loadCommentsAndCarousel'));
        add_action('wp_ajax_nopriv_' . $slug_down . '_load_comments_and_carousel', array( $ff, 'loadCommentsAndCarousel'));
		// roles detect
		add_action('wp_ajax_' . $slug_down . '_moderation_apply_action', array( $ff, 'moderation_apply'));

		// secured endpoints
		add_action('wp_ajax_' . $slug_down . '_social_auth',			array( $dbm, 'social_auth' ));
		add_action('wp_ajax_' . $slug_down . '_save_sources_settings',	array( $dbm, 'save_sources_settings' ));
		add_action('wp_ajax_' . $slug_down . '_get_stream_settings',	array( $dbm, 'get_stream_settings' ));
		add_action('wp_ajax_' . $slug_down . '_ff_save_settings',		array( $dbm, 'ff_save_settings_fn' ));
		add_action('wp_ajax_' . $slug_down . '_save_stream_settings',	array( $dbm, 'save_stream_settings' ));
		add_action('wp_ajax_' . $slug_down . '_create_stream',			array( $dbm, 'create_stream' ));
		add_action('wp_ajax_' . $slug_down . '_clone_stream',			array( $dbm, 'clone_stream' ));
		add_action('wp_ajax_' . $slug_down . '_delete_stream',			array( $dbm, 'delete_stream' ));
		
		new FFSnapshotManager($this->context, $dbm->snapshot_table_name);
		
		if (!FF_USE_WP_CRON){
			add_action('wp_ajax_' . $slug_down . '_refresh_cache', array($ff, 'refreshCache'));
			add_action('wp_ajax_nopriv_' . $slug_down . '_refresh_cache', array($ff, 'refreshCache'));
		}
	}
	
	protected function renderAdminSide(){
		new FlowFlowAdmin($this->context);
	}
	
	protected function renderPublicSide(){
		$ff = FlowFlow::get_instance($this->context);
		add_action('init',					array($ff, 'register_shortcodes'));
		add_action('init',					array($ff, 'load_plugin_textdomain'));
		add_action('wp_enqueue_scripts',	array($ff, 'enqueue_scripts'));
		add_action('wpmu_new_blog',			array($ff, 'activate_new_site'));
	}

	protected function afterPluginLoad(){
		add_action( 'widgets_init', array($this, 'initWPWidget'));
		add_action( 'vc_before_init', array($this, 'initVCIntegration'));
	}
}