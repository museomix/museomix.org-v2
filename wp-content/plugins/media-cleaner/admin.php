<?php

include "common/admin.php";

class Meow_WPMC_Admin extends MeowApps_Admin {

	public function __construct( $prefix, $mainfile, $domain ) {
		parent::__construct( $prefix, $mainfile, $domain );
		add_action( 'admin_menu', array( $this, 'app_menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'pre_update_option', array( $this, 'pre_update_option' ), 10, 3 );
	}

	/**
	 * Filters and performs validation for certain options
	 * @param mixed $value Option value
	 * @param string $option Option name
	 * @param mixed $old_value The current value of the option
	 * @return mixed The actual value to be stored
	 */
	function pre_update_option( $value, $option, $old_value ) {
		if ( strpos( $option, 'wpmc_' ) !== 0 ) return $value; // Never touch extraneous options
		$validated = $this->validate_option( $option, $value );
		if ( $validated instanceof WP_Error ) {
			// TODO: Show warning for invalid option value
			return $old_value;
		}
		return $validated;
	}

	/**
	 * Validates certain option values
	 * @param string $option Option name
	 * @param mixed $value Option value
	 * @return mixed|WP_Error Validated value if no problem
	 */
	function validate_option( $option, $value ) {
		switch ( $option ) {
		case 'wpmc_dirs_filter':
		case 'wpmc_files_filter':
			if ( $value && @preg_match( $value, '' ) === false ) return new WP_Error( 'invalid_option', __( "Invalid Regular-Expression", 'media-cleaner' ) );
			break;
		}
		return $value;
	}

	function admin_notices() {

		$mediasBuffer = get_option( 'wpmc_medias_buffer', null );
		$postsBuffer = get_option( 'wpmc_posts_buffer', null );
		$analysisBuffer = get_option( 'wpmc_analysis_buffer', null );
		$delay = get_option( 'wpmc_delay', null );

		if ( !is_numeric( $mediasBuffer ) || $mediasBuffer < 1 )
			update_option( 'wpmc_medias_buffer', 100 );
		if ( !is_numeric( $postsBuffer ) || $postsBuffer < 1 )
			update_option( 'wpmc_posts_buffer', 5 );
		if ( !is_numeric( $analysisBuffer ) || $analysisBuffer < 1 )
			update_option( 'wpmc_analysis_buffer', 100 );
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

			// SUBMENU > Settings > Settings (Scanning)
			add_settings_section( 'wpmc_settings', null, null, 'wpmc_settings-menu' );
			add_settings_field( 'wpmc_method', "Method",
				array( $this, 'admin_method_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
				add_settings_field( 'wpmc_media_library', "Media Library",
					array( $this, 'admin_media_library_callback' ),
					'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_posts', "Posts",
				array( $this, 'admin_posts_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_postmeta', "Post Meta",
				array( $this, 'admin_postmeta_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_widgets', "Widgets",
				array( $this, 'admin_widgets_callback' ),
				'wpmc_settings-menu', 'wpmc_settings' );
			// add_settings_field( 'wpmc_shortcode', "Shortcodes<br />(Pro)",
			// 	array( $this, 'admin_shortcode_callback' ),
			// 	'wpmc_settings-menu', 'wpmc_settings' );
			// add_settings_field( 'wpmc_background', "Background CSS<br />(Pro)",
			// 	array( $this, 'admin_background_callback' ),
			// 	'wpmc_settings-menu', 'wpmc_settings' );
			add_settings_field( 'wpmc_debuglogs', "Logs",
				array( $this, 'admin_debuglogs_callback' ),
				'wpmc_settings-menu', 'wpmc_settings', array( "Enable" ) );

			// SUBMENU > Settings > Filters
			add_settings_section( 'wpmc_filters_settings', null, null, 'wpmc_filters_settings-menu' );
			add_settings_field( 'wpmc_thumbnails_only', "Thumbnails Only",
				array( $this, 'admin_thumbnails_only_callback' ),
				'wpmc_filters_settings-menu', 'wpmc_filters_settings' );

			add_settings_field(
				'wpmc_dirs_filter',
				'Directories Filter',
				array( $this, 'admin_dirs_filter_callback' ),
				'wpmc_filters_settings-menu',
				'wpmc_filters_settings'
			);

			add_settings_field(
				'wpmc_files_filter',
				'Files Filter',
				array( $this, 'admin_files_filter_callback' ),
				'wpmc_filters_settings-menu',
				'wpmc_filters_settings'
			);

			// SUBMENU > Settings > UI
			add_settings_section( 'wpmc_ui_settings', null, null, 'wpmc_ui_settings-menu' );
			add_settings_field( 'wpmc_hide_thumbnails', "Thumbnails",
				array( $this, 'admin_hide_thumbnails_callback' ),
				'wpmc_ui_settings-menu', 'wpmc_ui_settings' );
			add_settings_field( 'wpmc_hide_warning', "Warning Message",
				array( $this, 'admin_hide_warning_callback' ),
				'wpmc_ui_settings-menu', 'wpmc_ui_settings' );
			add_settings_field( 'wpmc_results_per_page', "Results Per Page",
				array( $this, 'admin_results_per_page' ),
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
		// register_setting( 'wpmc_settings', 'wpmc_shortcode' );
		// register_setting( 'wpmc_settings', 'wpmc_background' );
		register_setting( 'wpmc_settings', 'wpmc_widgets' );
		register_setting( 'wpmc_settings', 'wpmc_media_library' );
		register_setting( 'wpmc_settings', 'wpmc_postmeta' );
		register_setting( 'wpmc_settings', 'wpmc_debuglogs' );

		register_setting( 'wpmc_filters_settings', 'wpmc_thumbnails_only' );
		register_setting( 'wpmc_filters_settings', 'wpmc_dirs_filter' );
		register_setting( 'wpmc_filters_settings', 'wpmc_files_filter' );

		register_setting( 'wpmc_ui_settings', 'wpmc_hide_thumbnails' );
		register_setting( 'wpmc_ui_settings', 'wpmc_hide_warning' );
		register_setting( 'wpmc_ui_settings', 'wpmc_results_per_page' );

		register_setting( 'wpmc_advanced_settings', 'wpmc_medias_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_posts_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_analysis_buffer' );
		register_setting( 'wpmc_advanced_settings', 'wpmc_delay' );
	}

	function admin_medias_buffer_callback( $args ) {
		$value = get_option( 'wpmc_medias_buffer', 100 );
		$html = '<input type="number" style="width: 100%;" id="wpmc_medias_buffer" name="wpmc_medias_buffer" value="' . $value . '" />';
		$html .= '<br /><span class="description">The number of media entries to read at a time. This is fast, so the value should be between 50 and 1000.</label>';
		echo $html;
	}

	function admin_posts_buffer_callback( $args ) {
		$value = get_option( 'wpmc_posts_buffer', 5 );
		$html = '<input type="number" style="width: 100%;" id="wpmc_posts_buffer" name="wpmc_posts_buffer" value="' . $value . '" />';
		$html .= '<br /><span class="description">The number of posts (and any other post types) to analyze at a time. This is the most intense part of the process. Recommended value is between 1 (slow server) and 20 (excellent server).</label>';
		echo $html;
	}

	function admin_analysis_buffer_callback( $args ) {
		$value = get_option( 'wpmc_analysis_buffer', 100 );
		$html = '<input type="number" style="width: 100%;" id="wpmc_analysis_buffer" name="wpmc_analysis_buffer" value="' . $value . '" />';
		$html .= '<br /><span class="description">The number of media entries or files to analyze at a time. This is the main part of the process, but is is much faster than analyzing each post. Recommended value is between 20 (slow server) and 1000 (excellent server).</label>';
		echo $html;
	}

	function admin_delay_callback( $args ) {
		$value = get_option( 'wpmc_delay', 100 );
		$html = '<input type="number" style="width: 100%;" id="wpmc_delay" name="wpmc_delay" value="' . $value . '" />';
		$html .= '<br /><span class="description">Time to wait between each request (in milliseconds). The overall process is intensive so this gives the chance to your server to chill out a bit. A very good server doesn\'t need it, but a slow/shared hosting might even reject requests if they are too fast and frequent. Recommended value is actually 0, 100 for safety, 2000 or 5000 if your hosting is kind of cheap.</label>';
		echo $html;
	}

	function admin_settings() {
		?>
		<div class="wrap">
			<?php
				echo $this->display_title( "Media Cleaner" );
			?>
			<div class="meow-section meow-group">
				<div class="meow-box meow-col meow-span_2_of_2">
					<h3>How to use</h3>
					<div class="inside">
						<?php echo _e( "You can choose two kind of methods, analyzing your Media Library for images which are not in used, or in your Filesystem for images which aren't registered in the Media Library or not in used. <b>Those checks can be very expensive in term of resources and might fail so you might want to play with those options depending on your install and what you need.</b> Check the <a target=\"_blank\" href=\"//meowapps.com/media-cleaner/tutorial/\">tutorial</a> for more information.", 'media-cleaner' ); ?>
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

					<div class="meow-box">
						<h3>Filters</h3>
						<div class="inside">
							<form method="post" action="options.php">
							<?php settings_fields( 'wpmc_filters_settings' ); ?>
							<?php do_settings_sections( 'wpmc_filters_settings-menu' ); ?>
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

					<!--
					<?php if ( get_option( 'wpmc_shortcode', false ) ): ?>
					<div class="meow-box">
						<h3>Shortcodes</h3>
						<div class="inside"><small>
							<p>Here are the shortcodes registered in your WordPress by your theme and other plugins.</p>
							<?php
								global $shortcode_tags;
								try {
									if ( is_array( $shortcode_tags ) ) {
										$my_shortcodes = array();
										foreach ( $shortcode_tags as $sc )
											if ( $sc != '__return_false' ) {
												if ( is_string( $sc ) )
													array_push( $my_shortcodes, str_replace( '_shortcode', '', (string)$sc ) );
											}
										$my_shortcodes = implode( ', ', $my_shortcodes );
									}
								}
								catch (Exception $e) {
									$my_shortcodes = "";
								}
								echo $my_shortcodes;
							?>
						</small></div>
					</div>
					<?php endif; ?>
					-->

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
		</select><small><br />' . __( 'Check the <a target="_blank" href="//meowapps.com/media-cleaner/tutorial/">tutorial</a> for more information.', 'media-cleaner' ) . '</small>';

		// TODO: This is temporary
		$html .= '<br /><br /><small><b style="color: red;">Notice: </b><b> Those settings will be simplified soon. Have a look <a href="https://trello.com/c/qrCuITg8/55-cleaner-simplification-of-the-settings" target="_blank">here</a> and let me know in the comments if you like the idea or not.</b></small>';

		echo $html;
	}


	// function admin_shortcode_callback( $args ) {
	//   $value = get_option( 'wpmc_shortcode', null );
	// 	$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="wpmc_shortcode" name="wpmc_shortcode" value="1" ' .
	// 		checked( 1, get_option( 'wpmc_shortcode' ), false ) . '/>';
	//   $html .= '<label>Resolve</label><br /><small>The shortcodes you are using in your <b>posts</b> and/or <b>widgets</b> (depending on your options) will be resolved and analyzed. You don\'t need to have this option enabled for the WP Gallery (as it is covered by the Galleries option).</small>';
	//   echo $html;
	// }

	// function admin_background_callback( $args ) {
	//   $value = get_option( 'wpmc_background', null );
	// 	$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="wpmc_background" name="wpmc_background" value="1" ' .
	// 		checked( 1, get_option( 'wpmc_background' ), false ) . '/>';
	//   $html .= '<label>Analyze</label><br /><small>When parsing HTML, the CSS inline background will also be analyzed. A few page builders are using this.</small>';
	//   echo $html;
	// }

	function admin_debuglogs_callback( $args ) {
		global $wpmc;
		$debuglogs = get_option( 'wpmc_debuglogs' );
		$clearlogs = isset ( $_GET[ 'clearlogs' ] ) ? $_GET[ 'clearlogs' ] : 0;
		if ( $clearlogs && file_exists( plugin_dir_path( __FILE__ ) . '/media-cleaner.log' ) ) {
			unlink( plugin_dir_path( __FILE__ ) . '/media-cleaner.log' );
		}
		$html = '<input type="checkbox" id="wpmc_debuglogs" name="wpmc_debuglogs" value="1" ' .
			checked( 1, $debuglogs, false ) . '/>';
		$html .= '<label for="wpmc_debuglogs"> '  . $args[0] . '</label><br>';
		$html .= '<small>' . __( 'Creates an internal log file, for debugging purposes.', 'media-cleaner' );
		if ( $debuglogs && !file_exists( plugin_dir_path( __FILE__ ) . '/media-cleaner.log' ) ) {
			if ( !$wpmc->log( "Testing the logging feature. It works!" ) ) {
				$html .= sprintf( __( '<br /><b>Cannot create the logging file. Logging will not work. The plugin as a whole might not be able to work neither.</b>', 'media-cleaner' ), plugin_dir_url( __FILE__ ) );
			}
		}
		if ( file_exists( plugin_dir_path( __FILE__ ) . '/media-cleaner.log' ) ) {
			$html .= sprintf( __( '<br />The <a target="_blank" href="%smedia-cleaner.log">log file</a> is available. You can also <a href="?page=wpmc_settings-menu&clearlogs=true">clear</a> it.', 'media-cleaner' ), plugin_dir_url( __FILE__ ) );
		}
		$html .= '</small>';
		echo $html;
	}

	function admin_media_library_callback( $args ) {
		$value = get_option( 'wpmc_media_library', true );
		$html = '<input type="checkbox" id="wpmc_media_library" name="wpmc_media_library" value="1" ' .
			disabled( get_option( 'wpmc_method', 'media' ) == 'files', false, false ) . ' ' .
			checked( 1, get_option( 'wpmc_media_library' ), false ) . '/>';
		$html .= '<label>Check</label><br /><small>Checks if the file is linked to a media. Only makes sense with the Filesystem scan.</small>';
		echo $html;
	}

	function admin_posts_callback( $args ) {
		$value = get_option( 'wpmc_posts', true );
		$html = '<input type="checkbox" id="wpmc_posts" name="wpmc_posts" value="1" ' .
			checked( 1, get_option( 'wpmc_posts' ), false ) . '/>';
		$html .= '<label>Analyze</label><br /><small>Check if the media/file is used by any post types.</small>';
		echo $html;
	}

	function admin_postmeta_callback( $args ) {
		$value = get_option( 'wpmc_postmeta', true );
		$html = '<input type="checkbox" id="wpmc_postmeta" name="wpmc_postmeta" value="1" ' .
			checked( 1, get_option( 'wpmc_postmeta' ), false ) . '/>';
		$html .= '<label>Analyze</label><br /><small>Checks if the media/file is used in the meta.</small>';
		echo $html;
	}

	function admin_widgets_callback( $args ) {
		$value = get_option( 'wpmc_widgets', false );
		$html = '<input type="checkbox" id="wpmc_widgets" name="wpmc_widgets" value="1" ' .
			checked( 1, get_option( 'wpmc_widgets' ), false ) . '/>';
		$html .= '<label>Analyze</label><br /><small>Checks if the media/file is used by any widget.</small>';
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
		$html = '<input type="checkbox" id="wpmc_hide_warning" name="wpmc_hide_warning" value="1" ' .
			checked( 1, get_option( 'wpmc_hide_warning' ), false ) . '/>';
		$html .= '<label>Hide</label><br /><small>Have you read it twice? If yes, hide it :)</small>';
		echo $html;
	}

	function admin_results_per_page( $args ) {
		$value = get_option( 'wpmc_results_per_page', 20 );
		$html = <<< HTML
<input step="1" min="1" max="999" name="wpmc_results_per_page" id="wpmc_results_per_page" maxlength="3" value="{$value}" type="number">
HTML;
		echo $html;
	}

	function admin_thumbnails_only_callback( $args ) {
		$value = get_option( 'wpmc_thumbnails_only', false );
		$html = '<input type="checkbox" id="wpmc_thumbnails_only" name="wpmc_thumbnails_only" value="1" ' .
			disabled( get_option( 'wpmc_method', 'media' ) == 'files', false, false ) . ' ' .
			checked( 1, get_option( 'wpmc_thumbnails_only' ), false ) . '/>';
		$html .= '<label>Enable</label><br /><small>Restrict the filesystem scan to thumbnails (files containing the resolution). If none of the checks above are selected, you will get the list of all the thumbnails and be able to remove them.</small>';
		echo $html;
	}

	function admin_dirs_filter_callback( $args ) {
		$value = get_option( 'wpmc_dirs_filter', '' );
		$invalid = @preg_match( $value, '' ) === false;
		?>
<input type="text" id="wpmc_dirs_filter" name="wpmc_dirs_filter" value="<?php echo $value; ?>" placeholder="/regex/" autocomplete="off" data-needs-validation style="font-family: monospace;">
<?php
	}

	function admin_files_filter_callback( $args ) {
		$value = get_option( 'wpmc_files_filter', '' );
		$invalid = @preg_match( $value, '' ) === false;
		?>
<input type="text" id="wpmc_files_filter" name="wpmc_files_filter" value="<?php echo $value; ?>" placeholder="/regex/" autocomplete="off" data-needs-validation style="font-family: monospace;">
<?php
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
