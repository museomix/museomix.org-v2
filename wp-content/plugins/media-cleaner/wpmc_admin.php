<?php

include "common/admin.php";

class Meow_WPMC_Admin extends MeowApps_Admin {

	public function __construct( $prefix, $mainfile, $domain ) {
		parent::__construct( $prefix, $mainfile, $domain );
		add_action( 'admin_menu', array( $this, 'app_menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	function admin_notices() {

		$mediasBuffer = get_option( 'wpmc_medias_buffer', 500 );
		$postsBuffer = get_option( 'wpmc_posts_buffer', 10 );
		$analysisBuffer = get_option( 'wpmc_analysis_buffer', 5 );
		$delay = get_option( 'wpmc_delay', 100 );

		if ( !is_numeric( $mediasBuffer ) || $mediasBuffer < 1 )
			update_option( 'wpmc_medias_buffer', 500 );
		if ( !is_numeric( $postsBuffer ) || $postsBuffer < 1 )
			update_option( 'wpmc_posts_buffer', 10 );
		if ( !is_numeric( $analysisBuffer ) || $analysisBuffer < 1 )
			update_option( 'wpmc_analysis_buffer', 5 );
		if ( !is_numeric( $delay ) )
			update_option( 'wpmc_delay', 100 );

		if ( !$this->is_registered() && get_option( 'wpmc_method', 'media' ) == 'files' ) {
	    _e( "<div class='error'><p>The Pro version is required to scan files. You can <a target='_blank' href='http://meowapps.com/media-cleaner'>get a serial for the Pro version here</a>.</p></div>", 'media-cleaner' );
	  }
	}

	function common_url( $file ) {
		return trailingslashit( plugin_dir_url( __FILE__ ) ) . 'common/' . $file;
	}

	function app_menu() {

		// SUBMENU > Settings
		add_submenu_page( 'meowapps-main-menu', 'Media Cleaner', 'Media Cleaner', 'manage_options',
			'wpmc_settings-menu', array( $this, 'admin_settings' ) );

			// SUBMENU > Settings > Settings
			add_settings_section( 'wpmc_settings', null, null, 'wpmc_settings-menu' );
			add_settings_field( 'wpmc_method', "Method",
				array( $this, 'admin_method_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			if ( get_option( 'wpmc_method', 'media' ) == 'files' ) {
				add_settings_field( 'wpmc_media_library', "Media Library",
					array( $this, 'admin_media_library_callback' ),
					'wpmc_settings-menu', 'wpmc_settings' );
			}
			add_settings_field( 'wpmc_posts', "Posts",
				array( $this, 'admin_posts_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_postmeta', "Post Meta",
				array( $this, 'admin_postmeta_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_galleries', "Galleries",
				array( $this, 'admin_galleries_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_widgets', "Widgets",
				array( $this, 'admin_widgets_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );

			if ( get_option( 'wpmc_posts', true ) ) {
				add_settings_field( 'wpmc_shortcode', "Shortcodes<br />(Pro)",
					array( $this, 'admin_shortcode_callback' ),
					'wpmc_settings-menu', 'wpmc_settings' );
			}
			add_settings_field( 'wpmc_utf8', "UTF-8",
				array( $this, 'admin_utf8_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );

			// SUBMENU > Settings > UI
			add_settings_section( 'wpmc_ui_settings', null, null, 'wpmc_ui_settings-menu' );
			add_settings_field( 'wpmc_hide_thumbnails', "Thumbnails",
				array( $this, 'admin_hide_thumbnails_callback' ),
				'wpmc_ui_settings-menu', 'wpmc_ui_settings' );
			add_settings_field( 'wpmc_hide_warning', "Warning Message (Pro)",
				array( $this, 'admin_hide_warning_callback' ),
				'wpmc_ui_settings-menu', 'wpmc_ui_settings' );

			// SUBMENU > Settings > Advanced
			add_settings_section( 'wpmc_advanced_settings', null, null, 'wpmc_advanced_settings-menu' );
			add_settings_field( 'wpmc_medias_buffer', "Medias Buffer",
				array( $this, 'admin_medias_buffer_callback' ),
				'wpmc_advanced_settings-menu', 'wpmc_advanced_settings' );
			add_settings_field( 'wpmc_posts_buffer', "Posts Buffer",
				array( $this, 'admin_posts_buffer_callback' ),
				'wpmc_advanced_settings-menu', 'wpmc_advanced_settings' );
			add_settings_field( 'wpmc_analysis_buffer', "Analysis Buffer",
				array( $this, 'admin_analysis_buffer_callback' ),
				'wpmc_advanced_settings-menu', 'wpmc_advanced_settings' );
			add_settings_field( 'wpmc_delay', "Delay (in ms)",
				array( $this, 'admin_delay_callback' ),
				'wpmc_advanced_settings-menu', 'wpmc_advanced_settings' );

		// SETTINGS
		register_setting( 'wpmc_settings', 'wpmc_method' );
		register_setting( 'wpmc_settings', 'wpmc_posts' );
		register_setting( 'wpmc_settings', 'wpmc_shortcode' );
		register_setting( 'wpmc_settings', 'wpmc_galleries' );
		register_setting( 'wpmc_settings', 'wpmc_widgets' );
		register_setting( 'wpmc_settings', 'wpmc_media_library' );
		register_setting( 'wpmc_settings', 'wpmc_postmeta' );
		register_setting( 'wpmc_settings', 'wpmc_utf8' );

		register_setting( 'wpmc_ui_settings', 'wpmc_hide_thumbnails' );
		register_setting( 'wpmc_ui_settings', 'wpmc_hide_warning' );

		register_setting( 'wpmc_advanced_settings', 'wpmc_medias_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_posts_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_analysis_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_delay' );
	}

	function admin_medias_buffer_callback( $args ) {
    $value = get_option( 'wpmc_medias_buffer', 500 );
    $html = '<input type="number" style="width: 100%;" id="wpmc_medias_buffer" name="wpmc_medias_buffer" value="' . $value . '" />';
    $html .= '<br /><span class="description">The number of medias to read in one time during the preparation phase. This is fast, so the value should be between 50 and 1000.</label>';
    echo $html;
  }

	function admin_posts_buffer_callback( $args ) {
    $value = get_option( 'wpmc_posts_buffer', 10 );
    $html = '<input type="number" style="width: 100%;" id="wpmc_posts_buffer" name="wpmc_posts_buffer" value="' . $value . '" />';
    $html .= '<br /><span class="description">The number of posts to read in one time during the preparation phase. This takes a bit of time in case galleries are being used. Recommended value is between 5 and 20.</label>';
    echo $html;
  }

	function admin_analysis_buffer_callback( $args ) {
    $value = get_option( 'wpmc_analysis_buffer', 5 );
    $html = '<input type="number" style="width: 100%;" id="wpmc_analysis_buffer" name="wpmc_analysis_buffer" value="' . $value . '" />';
    $html .= '<br /><span class="description">The number of medias or files to analyse in one time. It is the main part of the process and it depends on the scanning options. Recommended value is 5 but can be more on a powerful server, or 1 if your server is not powerful or your WordPress very bloated.</label>';
    echo $html;
  }

	function admin_delay_callback( $args ) {
    $value = get_option( 'wpmc_delay', 100 );
    $html = '<input type="number" style="width: 100%;" id="wpmc_delay" name="wpmc_delay" value="' . $value . '" />';
    $html .= '<br /><span class="description">This is a delay Media Cleaner will wait between each request. The process is intensive so this gives the server time to relax a little (hosting services sometimes require this). Recommended value is actually 0, 100 (ms) is for safety, some servers require 2000 or 5000 (2 or 5 seconds).</label>';
    echo $html;
  }

	function admin_settings() {
		?>
		<div class="wrap">
			<?php
				echo $this->display_title( "Media Cleaner" );
			?>
			<p>This plugin will help you cleaning your WordPress install.</p>
			<div class="meow-section meow-group">
				<div class="meow-box meow-col meow-span_2_of_2">
					<h3>How to use</h3>
					<div class="inside">
						<?php echo _e( "You can choose two kind of methods, analyzing your Media Library for images which are not in used, or in your Filesystem for images which aren't registered in the Media Library or not in used. <b>Those checks can be very expensive in term of resources and might fail so you might want to play with those options depending on your install and what you need. I am working actively on making the plugin to work fine even on huge installs with all those options.</b>", 'media-cleaner' ); ?>
						<p class="submit">
							<a class="button button-primary" href="upload.php?page=media-cleaner"><?php echo _e( "Access Media Cleaner Dashboard", 'media-cleaner' ); ?></a>
						</p>
					</div>
				</div>
			</div>

			<div class="meow-section meow-group">

				<div class="meow-col meow-span_1_of_2">
					<div class="meow-box">
						<h3>Scanning</h3>
						<div class="inside">
							<form method="post" action="options.php">
							<?php settings_fields( 'wpmc_settings' ); ?>
					    <?php do_settings_sections( 'wpmc_settings-menu' ); ?>
					    <?php submit_button(); ?>
							</form>
						</div>
					</div>
				</div>

				<div class="meow-col meow-span_1_of_2">
					<?php $this->display_serialkey_box( "https://meowapps.com/media-cleaner/" ); ?>

					<div class="meow-box">
						<h3>UI</h3>
						<div class="inside">
							<form method="post" action="options.php">
							<?php settings_fields( 'wpmc_ui_settings' ); ?>
					    <?php do_settings_sections( 'wpmc_ui_settings-menu' ); ?>
					    <?php submit_button(); ?>
							</form>
						</div>
					</div>

					<div class="meow-box">
						<h3>Advanced</h3>
						<div class="inside">
							<form method="post" action="options.php">
							<?php settings_fields( 'wpmc_advanced_settings' ); ?>
					    <?php do_settings_sections( 'wpmc_advanced_settings-menu' ); ?>
					    <?php submit_button(); ?>
							</form>
						</div>
					</div>

					<?php if ( get_option( 'wpmc_shortcode', false ) ): ?>
					<div class="meow-box">
						<h3>Shortcodes</h3>
						<div class="inside">
							<p>Here are the shortcodes registered in your WordPress by your theme and other plugins.</p>
							<?php
								global $shortcode_tags;
						    try {
						      $allshortcodes = array_diff( $shortcode_tags, array(  ) );
						      $my_shortcodes = array();
						      foreach ( $allshortcodes as $sc )
						        if ( $sc != '__return_false' ) {
						          if ( is_string( $sc ) )
						            array_push( $my_shortcodes, str_replace( '_shortcode', '', (string)$sc ) );
						        }
						      $my_shortcodes = implode( '<br />', $my_shortcodes );
						    }
						    catch (Exception $e) {
						      $my_shortcodes = "";
						    }
								echo $my_shortcodes;
							?>
						</div>
					</div>
					<?php endif; ?>

				</div>

			</div>
		</div>
		<?php
	}



	/*
		OPTIONS CALLBACKS
	*/

	function admin_method_callback( $args ) {
    $value = get_option( 'wpmc_method', 'media' );
		$html = '<select id="wpmc_method" name="wpmc_method">
		  <option ' . selected( 'media', $value, false ) . 'value="media">Media Library</option>
		  <option ' . disabled( $this->is_registered(), false, false ) . ' ' . selected( 'files', $value, false ) . 'value="files">Filesystem (Pro)</option>
		</select><small><br /><br />' . __( '<b>Media Library</b>: The medias from Media Library which seem not being used in your WordPress will be marked as to be deleted. <br /><br /><b>Filesystem</b>: The files in your /uploads directory that don\'t seem being used in your WordPress will be marked as to be deleted. If the files are registered as a media in your Media Library, they will be considered as fine (even if they are not used in the content of your website).', 'media-cleaner' ) . '</small>';
    echo $html;
  }


	function admin_shortcode_callback( $args ) {
    $value = get_option( 'wpmc_shortcode', null );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="wpmc_shortcode" name="wpmc_shortcode" value="1" ' .
			checked( 1, get_option( 'wpmc_shortcode' ), false ) . '/>';
    $html .= '<label>Analyze</label><br /><small>The shortcodes you are using in your <b>posts</b> and/or <b>widgets</b> (depending on your options) will be resolved and analyzed. This process takes resources and if the scanning suddenly stops, this might be the cause. You don\'t need to have this option enabled for the WP Gallery (this is covered by the Galleries option).</small>';
    echo $html;
  }

	function admin_utf8_callback( $args ) {
    $value = get_option( 'wpmc_utf8', null );
		$html = '<input type="checkbox" id="wpmc_utf8" name="wpmc_utf8" value="1" ' .
			checked( 1, get_option( 'wpmc_utf8' ), false ) . '/>';
    $html .= __( '<label>Do not skip UTF-8 filenames</label><br /><small>PHP does not always work well with UTF-8 on all systems. If the scanning suddenly stops, this might be the cause.</small>', 'media-cleaner' );
    echo $html;
  }

	function admin_media_library_callback( $args ) {
    $value = get_option( 'wpmc_media_library', null );
		$html = '<input type="checkbox" id="wpmc_media_library" name="wpmc_media_library" value="1" ' .
			checked( 1, get_option( 'wpmc_media_library' ), false ) . '/>';
    $html .= '<label>Check</label><br /><small>Checks if the file is part of a Media (as a full-size or alternative size). This option is of course only available for the Filesystem method.</small>';
    echo $html;
  }

	function admin_galleries_callback( $args ) {
    $value = get_option( 'wpmc_galleries', null );
		$html = '<input type="checkbox" id="wpmc_galleries" name="wpmc_galleries" value="1" ' .
			checked( 1, get_option( 'wpmc_galleries' ), false ) . '/>';
    $html .= '<label>Analyze</label><br /><small>Checks if the file is used in a <b>WP Gallery</b> (in any post type) or in a <b>WooCommerce Gallery</b>.</small>';
    echo $html;
  }

	function admin_posts_callback( $args ) {
    $value = get_option( 'wpmc_posts', true );
		$html = '<input type="checkbox" id="wpmc_posts" name="wpmc_posts" value="1" ' .
			checked( 1, get_option( 'wpmc_posts' ), false ) . '/>';
    $html .= '<label>Analyze</label><br /><small>Check if the file is used in posts (that includes any post-type: pages, products and others) and, in addition, in case of the Media, check if it used in the IMG\'s class.</small>';
    echo $html;
  }

	function admin_postmeta_callback( $args ) {
    $value = get_option( 'wpmc_postmeta', true );
		$html = '<input type="checkbox" id="wpmc_postmeta" name="wpmc_postmeta" value="1" ' .
			checked( 1, get_option( 'wpmc_postmeta' ), false ) . '/>';
    $html .= '<label>Analyze</label><br /><small>Checks if the file is used in the Post Meta. One basic usage of the Post Meta is to store information about the <b>Featured Image</b>. But it also used by certain gallery plugins.</small>';
    echo $html;
  }

	function admin_widgets_callback( $args ) {
    $value = get_option( 'wpmc_widgets', false );
		$html = '<input type="checkbox" id="wpmc_widgets" name="wpmc_widgets" value="1" ' .
			checked( 1, get_option( 'wpmc_widgets' ), false ) . '/>';
    $html .= '<label>Analyze</label><br /><small>Checks if the file is used in widgets.</small>';
    echo $html;
  }

	function admin_hide_thumbnails_callback( $args ) {
    $value = get_option( 'wpmc_hide_thumbnails', null );
		$html = '<input type="checkbox" id="wpmc_hide_thumbnails" name="wpmc_hide_thumbnails" value="1" ' .
			checked( 1, get_option( 'wpmc_hide_thumbnails' ), false ) . '/>';
    $html .= '<label>Hide</label><br /><small>If you prefer not to see the thumbnails.</small>';
    echo $html;
  }

	function admin_hide_warning_callback( $args ) {
    $value = get_option( 'wpmc_hide_warning', null );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="wpmc_hide_warning" name="wpmc_hide_warning" value="1" ' .
			checked( 1, get_option( 'wpmc_hide_warning' ), false ) . '/>';
    $html .= '<label>Hide</label><br /><small>Have you read it twice? If yes, hide it :)</small>';
    echo $html;
  }

	/**
	 *
	 * GET / SET OPTIONS (TO REMOVE)
	 *
	 */

	function old_getoption( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[$option] ) ) {
	        if ( $options[$option] == "off" ) {
	            return false;
	        }
	        if ( $options[$option] == "on" ) {
	            return true;
	        }
			return $options[$option];
	    }
		return $default;
	}

}

?>
