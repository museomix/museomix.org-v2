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
	protected $plugin_screen_hook_suffix = null;

	protected function __construct($context) {
		$this->context      = $context;
		$this->plugin_slug  = $context['slug'];
		$this->db          = $context['db_manager'];

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		//$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->getPluginSlug() . '.php' );
		$plugin_basename = $this->getPluginSlug() . '/' . $this->getPluginSlug() . '.php';
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}

	public function getPluginSlug() {
		return $this->plugin_slug;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public final function add_plugin_admin_menu(){
		$displayAdminPageFunction = array( $this, 'display_plugin_admin_page' );
		$this->plugin_screen_hook_suffix = $this->addPluginAdminMenu($displayAdminPageFunction);
	}

	public final function init_admin_page() {
		$this->initPluginAdminPage();
	}

	/**
	 * Register and enqueue admin-specific style sheet and JavaScript.
	 *
	 * @since     1.0.0
	 */
	public final function enqueue_admin_scripts() {
		$plugin_directory = plugins_url() . '/' . $this->getPluginSlug() . '/';
		$this->enqueueAdminStylesAlways($plugin_directory);
		$this->enqueueAdminScriptsAlways($plugin_directory);
		do_action('ff_enqueue_admin_resources');
		if (isset( $this->plugin_screen_hook_suffix)) {
			$screen = get_current_screen();
			if ( $this->plugin_screen_hook_suffix == $screen->id ) {
				$this->initPluginAdminPage();
				$this->enqueueAdminStylesOnlyAtPluginPage($plugin_directory);
				$this->enqueueAdminScriptsOnlyAtPluginPage($plugin_directory);
				do_action('ff_enqueue_admin_resources_only_at_plugin_page');
			}
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

	protected abstract function addPluginAdminMenu($displayAdminPageFunction);
	protected abstract function initPluginAdminPage();
	protected abstract function displayPluginAdminPage($context);
	protected abstract function enqueueAdminStylesAlways($plugin_directory);
	protected abstract function enqueueAdminScriptsAlways($plugin_directory);
	protected abstract function enqueueAdminStylesOnlyAtPluginPage($plugin_directory);
	protected function enqueueAdminScriptsOnlyAtPluginPage($plugin_directory){
		//wp_localize_script($this->getPluginSlug() . '-admin-script', 'la_plugin_slug_down', $this->context['slug_down']);
	}
	protected abstract function addActionLinks();
}
