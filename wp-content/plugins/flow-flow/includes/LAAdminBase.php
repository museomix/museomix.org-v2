<?php namespace flow;
use flow\db\LADBManager;

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
abstract class LAAdminBase {
	/** @var LADBManager $db */
	protected $db = null;
	protected $context = null;
	protected $plugin_slug = null;
	
	public function __construct($context) {
		$this->context      = $context;
		$this->plugin_slug  = $context['slug'];
		$this->db          = $context['db_manager'];

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_social_stream_admin_menu' ) );

		//$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->getPluginSlug() . '.php' );
		$plugin_basename = $this->getPluginSlug() . '/' . $this->getPluginSlug() . '.php';
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	}

	public function getPluginSlug() {
		return $this->plugin_slug;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public final function add_social_stream_admin_menu(){
		$this->addPluginAdminMenu(array( $this, 'display_plugin_admin_page'));
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public final function add_plugin_admin_menu(){
		$this->addPluginAdminSubMenu(array( $this, 'display_plugin_admin_subpage'));
	}

	/**
	 * Register and enqueue admin-specific style sheet and JavaScript.
	 *
	 * @since     1.0.0
	 */
	public final function enqueue_admin_scripts($hook) {
		$screen_id = 'social-apps_page_' . $this->getPluginSlug() . '-admin';
		$plugin_directory = plugins_url() . '/' . $this->getPluginSlug() . '/';
		
		$this->enqueueAdminStylesAlways($plugin_directory);
		$this->enqueueAdminScriptsAlways($plugin_directory);
		do_action('ff_enqueue_admin_resources');
		
		if ($hook == 'toplevel_page_flow-flow'){
			$this->enqueueAdminStylesOnlyAtNewsPage($plugin_directory);
			$this->enqueueAdminScriptsOnlyAtNewsPage($plugin_directory);
		}
		else if ( $screen_id == $hook ) {
			$this->initPluginAdminPage();
			$this->enqueueAdminStylesOnlyAtPluginPage($plugin_directory);
			$this->enqueueAdminScriptsOnlyAtPluginPage($plugin_directory);
			do_action('ff_enqueue_admin_resources_only_at_plugin_page');
		}
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public final function display_plugin_admin_page() {
		if (FF_USE_WP){
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.', $this->getPluginSlug()));
			}
			$this->context['admin_page_title'] = esc_html( get_admin_page_title() );
		}
		else {
			if (!isset($this->context['admin_page_title'])) $this->context['admin_page_title'] = 'Flow-Flow - Social Streams Plugin';
		}
		$this->displayPluginAdminPage($this->context);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public final function add_action_links( $links ) {
		return array_merge($this->addActionLinks(), $links);
	}
	
	protected abstract function initPluginAdminPage();
	protected abstract function addPluginAdminSubMenu($displayAdminPageFunction);
	
	protected abstract function enqueueAdminStylesAlways($plugin_directory);
	protected abstract function enqueueAdminScriptsAlways($plugin_directory);
	protected function enqueueAdminStylesOnlyAtNewsPage($plugin_directory){
		wp_enqueue_style($this->getPluginSlug() . '-news-styles', $plugin_directory . 'css/news.css', array(), $this->context['version']);
	}
	protected function enqueueAdminScriptsOnlyAtNewsPage($plugin_directory){
		wp_enqueue_script($this->getPluginSlug() . '-news', $plugin_directory . 'js/news.js', array('jquery', 'underscore'), $this->context['version']);
		wp_localize_script($this->getPluginSlug() . '-news', 'FFIADMIN', array(
				'assets_url' => $this->context['plugin_url'] . '/' . $this->context['slug'],
				'plugins' => $this->getPluginsState(),
				'requirements' => array(
						'php_status' => version_compare(phpversion(), '5.3', '>='),
						'php' => preg_replace("(-.+)", '', phpversion()),
						'wp_status' => (float)get_bloginfo('version') > 4,
						'wp' => get_bloginfo('version'),
						'memory_status' => preg_replace('/[^0-9]/', '', ini_get('memory_limit')) >= 32,
						'memory' => ini_get('memory_limit'),
						'upload_status' => preg_replace('/[^0-9]/', '', ini_get('upload_max_filesize')) >= 64,
						'upload' => ini_get('upload_max_filesize')
				)
		));
	}
	protected abstract function enqueueAdminStylesOnlyAtPluginPage($plugin_directory);
	protected abstract function enqueueAdminScriptsOnlyAtPluginPage($plugin_directory);
	
	protected function addActionLinks(){
		$links['settings'] = '<a href="' . admin_url('admin.php?page=' . $this->getPluginSlug()) . '-admin' . '">' . 'Settings' . '</a>';
		$links['docs'] = '<a target="_blank" href="' . $this->context['faq_url'] . '">' . 'Docs' . '</a>';
		return $links;
	}
	
	/**
	 * States:
	 * 0 - not installer
	 * 1 - installed
	 * 2 - activated
	 */
	private function getPluginsState(){
		$plugins = array(
			'flow-flow' => array(
					'flow-flow/flow-flow.php',
					'flow-flow',
			),
			'insta-flow' => array(
					'insta-flow/insta-flow.php',
					'insta-flow-admin',
			),
			'social-stacks' => array(
					'social-stacks/social-stacks.php',
					'social-stacks-admin',
			)
		);
		
		$result = array();
		foreach ($plugins as $k => $v){
			$state = 0;
			if(file_exists(WP_PLUGIN_DIR . '/' . $v[0])){
				$state = 1;
			}
			if(is_plugin_active($v[0])){
				$state = 2;
			}
			$result[$k] = array(
				'state' => $state,
				'plugin_page_slug' => $v[1]
			);
		}
		return $result;
	}
	
	private function addPluginAdminMenu($displayAdminPageFunction){
		$plugin_directory = $this->context['plugin_url'] . $this->getPluginSlug() . '/';
		
		$wp_version = (float)get_bloginfo('version');
		if ($wp_version > 3.8) { // From 3.8 WP supports SVG icons
			$icon = $plugin_directory . 'assets/social-streams-icon.svg';
		} else {
			$icon = 'dashicons-networking';
		}
		
		if ( empty ( $GLOBALS['admin_page_hooks']['flow-flow'] ) ){
			return add_menu_page(
				'Social Apps',
				'Social Apps',
				'manage_options',
				'flow-flow',
				$displayAdminPageFunction,
				$icon
			);
		}
	}
	
	private function displayPluginAdminPage($context){
		if (FF_USE_WP){
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.', $this->getPluginSlug()));
			}
		}
		$context = $this->context;
		$activated = $this->db->registrationCheck();
		$this->db->dataInit();
		$context['activated'] = $activated;
		include_once($context['root']  . 'views/news.php');
	}
}
