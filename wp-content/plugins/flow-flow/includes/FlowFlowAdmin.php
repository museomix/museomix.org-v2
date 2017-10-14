<?php namespace flow;
use flow\cache\LAFacebookCacheManager;
use flow\tabs\FFAddonsTab;
use flow\tabs\FFAuthTab;
use flow\tabs\FFBackupTab;
use flow\tabs\FFLicenseTab;
use flow\tabs\FFModerationTab;
use flow\tabs\FFSourcesTab;
use flow\tabs\FFStreamsTab;
use flow\tabs\FFSuggestionsTab;
use flow\tabs\LAGeneralTab;

if ( ! defined( 'WPINC' ) ) die;
/**
 * Flow-Flow.
 *
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `FlowFlow.php`
 *
 * @package   FlowFlowAdmin
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FlowFlowAdmin extends LAAdminBase{
    protected static $instance = null;

    public static function get_instance($context) {
        if ( null == self::$instance ) {
            self::$instance = new self($context);
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     *
     * @param $context
     */
    protected function __construct($context) {
        parent::__construct($context);
    }

    protected function initPluginAdminPage(){
        $this->db->migrate();
    }

	protected function addPluginAdminMenu($displayAdminPageFunction) {
		return add_menu_page(
			'Flow-Flow — Social Stream',
			'Flow-Flow',
			'manage_options',
			$this->getPluginSlug(),
			$displayAdminPageFunction,
			'none'
		);
	}

    protected function enqueueAdminStylesAlways($plugin_directory){
        wp_enqueue_style($this->getPluginSlug() .'-admin-icon-styles', $plugin_directory . 'css/admin-icon.css', array(), FlowFlow::VERSION );
    }

    protected function enqueueAdminStylesOnlyAtPluginPage($plugin_directory){
        wp_enqueue_style($this->getPluginSlug() .'-admin-styles', $plugin_directory . 'css/admin.css', array(), FlowFlow::VERSION );
        wp_enqueue_style($this->getPluginSlug() .'-colorpickersliders', $plugin_directory . 'css/jquery-colorpickersliders.css', array(), FlowFlow::VERSION);

		// Load web font
        // Load web font
        wp_register_style('ff-fonts', '//fonts.googleapis.com/css?family=Montserrat:400,700|Lato:300,400' );
		wp_enqueue_style( 'ff-fonts' );

		//for preview
		//TODO move to filter
		FlowFlow::get_instance()->enqueue_styles();
	}

    protected function enqueueAdminScriptsAlways($plugin_directory){
        wp_enqueue_script( $this->getPluginSlug() . '-global-admin-script', $plugin_directory . 'js/global_admin.js', array( 'jquery' , 'backbone', 'underscore' ), FlowFlow::VERSION );
    }

	protected function enqueueAdminScriptsOnlyAtPluginPage($plugin_directory){
		parent::enqueueAdminScriptsOnlyAtPluginPage($plugin_directory);
        wp_enqueue_script( $this->getPluginSlug() . '-streams-script', $plugin_directory . 'js/streams.js', array( 'jquery' ), FlowFlow::VERSION );
        wp_enqueue_script( $this->getPluginSlug() . '-admin-script', $plugin_directory . 'js/admin.js', array( 'jquery', 'backbone', 'underscore' ), FlowFlow::VERSION );
		wp_localize_script($this->getPluginSlug() . '-admin-script', 'WP_FF_admin', array());
		wp_localize_script($this->getPluginSlug() . '-admin-script', 'isWordpress', (string)FF_USE_WP);
		wp_localize_script($this->getPluginSlug() . '-admin-script', '_ajaxurl', (string)FF_AJAX_URL);
		wp_enqueue_script( $this->getPluginSlug() . '-zeroclipboard', $plugin_directory . 'js/zeroclipboard/ZeroClipboard.min.js', array( 'jquery' ), FlowFlow::VERSION );
		wp_enqueue_script( $this->getPluginSlug() . '-tinycolor', $plugin_directory . 'js/tinycolor.js', array( 'jquery' ), FlowFlow::VERSION );
		wp_enqueue_script( $this->getPluginSlug() . '-colorpickersliders', $plugin_directory . 'js/jquery.colorpickersliders.js', array( 'jquery' ), FlowFlow::VERSION );

		//for preview
		//TODO move to filter
		FlowFlow::get_instance()->enqueue_scripts();
	}
	
	
	protected function displayPluginAdminPage($context) {
		/** @var LAFacebookCacheManager $facebookCache */
		$facebookCache = $context['facebook_cache'];
		
		$context['version'] = FlowFlow::VERSION;
		$context['options'] = FlowFlow::get_instance($context)->get_options();
		$context['auth_options'] = FlowFlow::get_instance($context)->get_auth_options();
		$context['extended_facebook_access_token'] = $facebookCache->getAccessToken();
		$context['extended_facebook_access_token_error'] = $facebookCache->getError();
		$this->db->dataInit();
		$context['streams'] = $this->db->streamsWithStatus();
		$context['sources'] = $this->db->sources();
		
		$context['form-action'] = '';
		$context['tabs'][] = new FFStreamsTab();
		$context['tabs'][] = new FFSourcesTab();
// 		$context['tabs'][] = new FFModerationTab();
		$context['tabs'][] = new LAGeneralTab();
		$context['tabs'][] = new FFAuthTab();
		
		if (FF_USE_WP){
			$activated = $this->db->registrationCheck();
			$context['activated'] = $activated;
			$context['tabs'][] = new FFBackupTab();
			$context['tabs'][] = new FFLicenseTab($activated);
			$context['tabs'][] = new FFAddonsTab();
			$context['tabs'][] = new FFSuggestionsTab();
		}
		
//		$context['facebook_auth_options'] = FlowFlow::get_instance()->get_auth_options();
//		$context['options'] = FlowFlow::get_instance()->get_options();
//		$context['js-vars'] = 'var STREAMS = ' .  json_encode($context['options']['streams']) . ';';
		$context['buttons-after-tabs'] = '<li id="request-tab"><span>Save changes</span> <i class="flaticon-paperplane"></i></li>';

		$context = apply_filters('ff_change_context', $context);

		include_once($context['root']  . 'views/admin.php');
	}

    protected function addActionLinks() {
        $links['settings'] = '<a href="' . admin_url('admin.php?page=' . $this->getPluginSlug()) . '">' . 'Settings' . '</a>';
        $links['docs'] = '<a target="_blank" href="http://docs.social-streams.com/">' . 'Documentation' . '</a>';
        return $links;
    }
}
