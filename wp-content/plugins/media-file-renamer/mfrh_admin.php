<?php

include "common/admin.php";

class Meow_MFRH_Admin extends MeowApps_Admin {

	public function __construct( $prefix, $mainfile, $domain ) {
		parent::__construct( $prefix, $mainfile, $domain );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	function admin_notices() {
		if ( isset( $_GET['reset'] ) ) {
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' );
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' );
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' );
		}
		$method = apply_filters( 'mfrh_method', 'media_title' );
		$sync_alt = get_option( 'mfrh_sync_alt' );
		$sync_meta_title = get_option( 'mfrh_sync_media_title' );

		$force_rename = get_option( 'mfrh_force_rename', false );
		$numbered_files = get_option( 'mfrh_numbered_files', false );

		if ( $force_rename && $numbered_files ) {
			update_option( 'mfrh_force_rename', false, false );
			?>
	    <div class="notice notice-warning is-dismissible">
	      <p><?php _e( 'Force Rename and Numbered Files cannot be used at the same time. Please use Force Rename only when you are trying to repair a broken install. For now, Force Rename has been disabled.', 'media-file-renamer' ); ?></p>
	    </div>
	    <?php
		}

		if ( $sync_alt && $method == 'alt_text' ) {
			update_option( 'mfrh_sync_alt', false, false );
			?>
	    <div class="notice notice-warning is-dismissible">
	      <p><?php _e( 'The option Sync ALT was turned off since it does not make sense to have it with this Auto-Rename mode.', 'media-file-renamer' ); ?></p>
	    </div>
	    <?php
		}

		if ( $sync_meta_title && $method == 'media_title' ) {
			update_option( 'mfrh_sync_media_title', false, false );
			?>
	    <div class="notice notice-warning is-dismissible">
	        <p><?php _e( 'The option Sync Media Title was turned off since it does not make sense to have it with this Auto-Rename mode.', 'media-file-renamer' ); ?></p>
	    </div>
	    <?php
		}
	}

	function common_url( $file ) {
		return trailingslashit( plugin_dir_url( __FILE__ ) ) . 'common/' . $file;
	}

	function app_menu() {

		$method = apply_filters( 'mfrh_method', 'media_title' );

		// SUBMENU > Settings
		add_submenu_page( 'meowapps-main-menu', 'Media File Renamer', 'Media Renamer', 'manage_options',
			'mfrh_settings-menu', array( $this, 'admin_settings' ) );

			// SUBMENU > Settings > Basic Settings
			add_settings_section( 'mfrh_settings', null, null, 'mfrh_settings-menu' );
			add_settings_field( 'mfrh_auto_rename', "Auto Rename",
				array( $this, 'admin_auto_rename_callback' ),
				'mfrh_settings-menu', 'mfrh_settings' );
			add_settings_field( 'mfrh_on_upload', "On Upload",
				array( $this, 'admin_on_upload_callback' ),
				'mfrh_settings-menu', 'mfrh_settings' );

			add_settings_field( 'mfrh_rename_slug', "Sync Slug<br /><i>Permalink</i>",
				array( $this, 'admin_rename_slug_callback' ),
				'mfrh_settings-menu', 'mfrh_settings' );

			register_setting( 'mfrh_settings', 'mfrh_auto_rename' );
			register_setting( 'mfrh_settings', 'mfrh_on_upload' );
			register_setting( 'mfrh_settings', 'mfrh_rename_slug' );

			// SUBMENU > Settings > Side Settings
			add_settings_section( 'mfrh_side_settings', null, null, 'mfrh_side_settings-menu' );
			add_settings_field( 'mfrh_update_posts', __( 'Posts', 'media-file-renamer' ),
				array( $this, 'admin_update_posts_callback' ),
				'mfrh_side_settings-menu', 'mfrh_side_settings' );
			add_settings_field( 'mfrh_update_postmeta', __( 'Post Meta', 'media-file-renamer' ),
				array( $this, 'admin_update_postmeta_callback' ),
				'mfrh_side_settings-menu', 'mfrh_side_settings' );

			register_setting( 'mfrh_side_settings', 'mfrh_update_posts' );
			register_setting( 'mfrh_side_settings', 'mfrh_update_postmeta' );

			// SUBMENU > Settings > Advanced Settings
			add_settings_section( 'mfrh_advanced_settings', null, null, 'mfrh_advanced_settings-menu' );
			add_settings_field( 'mfrh_undo', "Undo",
				array( $this, 'admin_undo_callback' ),
				'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );
			add_settings_field( 'mfrh_manual_rename', "Manual Rename<br />(Pro)",
				array( $this, 'admin_manual_rename_callback' ),
				'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );
			add_settings_field( 'mfrh_numbered_files', "Numbered Files<br />(Pro)",
				array( $this, 'admin_numbered_files_callback' ),
				'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );

			if ( $method == 'media_title' || $method == 'post_title' ) {
				add_settings_field( 'mfrh_sync_alt', "Sync ALT<br />(Pro)",
					array( $this, 'admin_sync_alt_callback' ),
					'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );
			}
			if ( $method == 'post_title' || $method == 'alt_text' ) {
				add_settings_field( 'mfrh_sync_media_title', "Sync Media Title<br />(Pro)",
					array( $this, 'admin_sync_media_title_callback' ),
					'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );
			}

			register_setting( 'mfrh_advanced_settings', 'mfrh_undo' );
			register_setting( 'mfrh_advanced_settings', 'mfrh_manual_rename' );
			register_setting( 'mfrh_advanced_settings', 'mfrh_numbered_files' );
			register_setting( 'mfrh_advanced_settings', 'mfrh_sync_alt' );
			register_setting( 'mfrh_advanced_settings', 'mfrh_sync_media_title' );

			// SUBMENU > Settings > Developer Settings
			add_settings_section( 'mfrh_developer_settings', null, null, 'mfrh_developer_settings-menu' );
			add_settings_field( 'mfrh_utf8_filename', __( 'UTF-8 Filename<br />(Pro)', 'media-file-renamer' ),
				array( $this, 'admin_utf8_filename_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );
			add_settings_field( 'mfrh_force_rename', __( 'Force Rename<br />(Pro)', 'media-file-renamer' ),
				array( $this, 'admin_force_rename_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );
			add_settings_field( 'mfrh_log', __( 'Logs', 'media-file-renamer' ),
				array( $this, 'admin_log_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );
			add_settings_field( 'mfrh_logsql', __( 'SQL Logs<br />(Pro)', 'media-file-renamer' ),
				array( $this, 'admin_logsql_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );
			add_settings_field( 'mfrh_rename_guid', "Sync GUID",
				array( $this, 'admin_rename_guid_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );
			add_settings_field( 'mfrh_rename_on_save', "Rename on Post Save",
				array( $this, 'admin_rename_on_save_callback' ),
				'mfrh_developer_settings-menu', 'mfrh_developer_settings' );

			register_setting( 'mfrh_developer_settings', 'mfrh_rename_guid' );
			register_setting( 'mfrh_developer_settings', 'mfrh_utf8_filename' );
			register_setting( 'mfrh_developer_settings', 'mfrh_force_rename' );
			register_setting( 'mfrh_developer_settings', 'mfrh_log' );
			register_setting( 'mfrh_developer_settings', 'mfrh_logsql' );
			register_setting( 'mfrh_developer_settings', 'mfrh_rename_on_save' );
	}

	function admin_settings() {
		?>
		<div class="wrap">
			<?php echo $this->display_title( "Media File Renamer" );  ?>

			<div class="meow-row">
				<div class="meow-box meow-col meow-span_2_of_2">
					<h3>How to use</h3>
					<div class="inside">
						<?php echo _e( 'This plugin works out of the box, the default settings are the best for most installs. However, you should have a look at the <a target="_blank" href="https://meowapps.com/media-file-renamer/">tutorial</a>.', 'media-file-renamer' ) ?>
						<p class="submit">
							<a class="button button-primary" href="upload.php?page=rename_media_files">
								<?php echo _e( "Access the Renamer Dashboard", 'media-file-renamer' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>

			<div class="meow-row">

					<div class="meow-col meow-span_1_of_2">

						<div class="meow-box">
							<h3>Settings</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'mfrh_settings' ); ?>
							    <?php do_settings_sections( 'mfrh_settings-menu' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

						<div class="meow-box">
							<h3>Side Updates</h3>
							<div class="inside">
								<p><?php _e( 'When the files are renamed, many links to them on your WordPress might be broken. Those options are updating the references to those files. <b>Give it a try, every install is different and it might not work for certain kind of references.</b>', 'media-file-renamer' );
								?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'mfrh_side_settings' ); ?>
							    <?php do_settings_sections( 'mfrh_side_settings-menu' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

					</div>

					<div class="meow-col meow-span_1_of_2">
						<?php $this->display_serialkey_box( "https://meowapps.com/media-file-renamer/" ); ?>

						<div class="meow-box">
							<h3>Advanced Settings</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'mfrh_advanced_settings' ); ?>
							    <?php do_settings_sections( 'mfrh_advanced_settings-menu' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

						<div class="meow-box">
							<h3>Developer Settings</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'mfrh_developer_settings' ); ?>
							    <?php do_settings_sections( 'mfrh_developer_settings-menu' ); ?>
									<?php _e( 'Do you want to clear/reset the logs? Click <a href="?page=mfrh_settings-menu&reset=true">here</a>.</b>', 'media-file-renamer' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

					</div>

			</div>

		</div>
		<?php
	}

	/*
		OPTIONS CALLBACKS
	*/

	function admin_rename_slug_callback( $args ) {
    $value = get_option( 'mfrh_rename_slug', null );
		$html = '<input type="checkbox" id="mfrh_rename_slug" name="mfrh_rename_slug" value="1" ' .
			checked( 1, get_option( 'mfrh_rename_slug' ), false ) . '/>';
    $html .= __( '<label>Slug = Filename</label><br /><small>Better to keep this un-checked as the link might have been referenced somewhere else.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_manual_rename_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_manual_rename" name="mfrh_manual_rename" value="1" ' .
			checked( 1, apply_filters( 'mfrh_manual', false ), false ) . '/>';
    $html .= '<label>Enable</label><br /><small>Manual field will be enabled in the Media Library and the Media Edit Screen.</small>';
    echo $html;
  }

	function admin_numbered_files_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_numbered_files" name="mfrh_numbered_files" value="1" ' .
			checked( 1, apply_filters( 'mfrh_numbered', false ), false ) . '/>';
    $html .= __( '<label>Enable Numbering</label><br /><small>Identical filenames will be allowed by the plugin and a number will be appended automatically (myfile.jpg, myfile-2.jpg, myfile-3.jpg, etc).</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_rename_guid_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_rename_guid" name="mfrh_rename_guid" value="1" ' .
			checked( 1, get_option( 'mfrh_rename_guid' ), false ) . '/>';
    $html .= __( '<label>GUID = Filename</label><br/><small>The GUID will be renamed like the new filename.<br /><small>Better to keep this un-checked.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_sync_alt_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_sync_alt" name="mfrh_sync_alt" value="1" ' .
			checked( 1, get_option( 'mfrh_sync_alt' ), false ) . '/>';
		$method = apply_filters( 'mfrh_method', 'media_title' );
		$what = __( "Error!", 'media-file-renamer' );
		if ( $method == "media_title" )
			$what = __( "Title of Media", 'media-file-renamer' );
		else if ( $method == "post_title" )
			$what = __( "Attached Post Title", 'media-file-renamer' );
    $html .= __( "<label>ALT = <b>$what</b></label><br /><small>Keep in mind that the HTML of your posts and pages WILL NOT be modified, as that is simply too dangerous for a plug-in.</small>", 'media-file-renamer' );
    echo $html;
  }

	function admin_sync_media_title_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_sync_media_title" name="mfrh_sync_media_title" value="1" ' .
			checked( 1, get_option( 'mfrh_sync_media_title' ), false ) . '/>';
		$method = apply_filters( 'mfrh_method', 'media_title' );
		$what = __( "Error!", 'media-file-renamer' );
		if ( $method == "alt_text" )
			$what = __( "Media ALT", 'media-file-renamer' );
		else if ( $method == "post_title" )
			$what = __( "Attached Post Title", 'media-file-renamer' );
    $html .= __( "<label>Media Title = <b>$what</b></label><br /><small>Keep in mind that the HTML of your posts and pages WILL NOT be modified, as that is simply too dangerous for a plug-in.</small>", 'media-file-renamer' );
    echo $html;
  }

	function admin_undo_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_undo" name="mfrh_undo" value="1" ' .
			checked( 1, get_option( 'mfrh_undo', false ), false ) . '/>';
    $html .= __( '<label>Enable</label><br /><small>A little undo icon will be added in the Rename column (Media Library). When clicked, the filename will be renamed back to the original.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_auto_rename_callback( $args ) {
    $value = apply_filters( 'mfrh_method', 'media_title' );
		$html = '<label><select id="mfrh_auto_rename" name="mfrh_auto_rename">
		  <option ' . selected( 'media_title', $value, false ) . 'value="media_title">Title of Media</option>
		  <option ' .
				disabled( $this->is_registered(), false, false ) . ' ' .
				selected( 'post_title', $value, false ) . 'value="post_title">Attached Post Title (Pro)</option>
			<option ' .
				disabled( $this->is_registered(), false, false ) . ' ' .
				selected( 'alt_text', $value, false ) . 'value="alt_text">Alternative Text (Pro)</option>
			<option ' . selected( 'none', $value, false ) . 'value="none">None</option>
		</select></label><small><br />' . __( 'If the plugin considers that it is too dangerous to rename the file directly at some point, it will be flagged internally <b>as to be renamed</b>. The list of those flagged files can be found in Media > File Renamer and they can be renamed from there.', 'media-file-renamer' ) . '</small>';
    echo $html;
  }

	function admin_on_upload_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_on_upload" name="mfrh_on_upload" value="1" ' .
			checked( 1, get_option( 'mfrh_on_upload', false ), false ) . '/>';
    $html .= __( '<label>Enable</label><br /><small>During upload, the filename will be renamed based on the title of the media if there is any EXIF with the file. Otherwise, it will optimize the upload filename.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_update_postmeta_callback( $args ) {
    $value = get_option( 'mfrh_update_postmeta', true );
		$html = '<input type="checkbox" id="mfrh_update_postmeta" name="mfrh_update_postmeta" value="1" ' .
			checked( 1, get_option( 'mfrh_update_postmeta', true ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br /><small>Update the references in the <b>custom fields</b> of the posts (including pages and custom types metadata).</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_update_posts_callback( $args ) {
    $value = get_option( 'mfrh_update_posts', true );
		$html = '<input type="checkbox" id="mfrh_update_posts" name="mfrh_update_posts" value="1" ' .
			checked( 1, get_option( 'mfrh_update_posts', true ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br /><small>Update the references to the renamed files in the <b>content</b> and <b>excerpt</b> of the posts (pages and custom types included).</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_rename_on_save_callback( $args ) {
    $value = get_option( 'mfrh_rename_on_save', null );
		$html = '<input type="checkbox" id="mfrh_rename_on_save" name="mfrh_rename_on_save" value="1" ' .
			checked( 1, get_option( 'mfrh_rename_on_save' ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br/><small>You can modify the titles of your media while editing a post but, of course, the plugin can\'t update the HTML at this stage. With this option, the plugin will update the filenames and HTML after that you saved the post. This option is <b>NOT RECOMMENDED.</b></small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_utf8_filename_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . '
			type="checkbox" id="mfrh_utf8_filename" name="mfrh_utf8_filename" value="1" ' .
			checked( 1, apply_filters( 'mfrh_utf8', false ), false ) . '/>';
    $html .= __( '<label>Allow non-ASCII filenames</label><br /><small>This usually doesn\'t work well on Windows installs.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_force_rename_callback( $args ) {
    $value = get_option( 'mfrh_force_rename', false );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_force_rename" name="mfrh_force_rename" value="1" ' .
			checked( 1, get_option( 'mfrh_force_rename' ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br/><small>Update the references to the file even if the file renaming itself was not successful. You might want to use that option if your install is broken and you are trying to link your Media to files for which the filenames has been altered (after a migration for exemple)</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_log_callback( $args ) {
    $value = get_option( 'mfrh_log', null );
		$html = '<input type="checkbox" id="mfrh_log" name="mfrh_log" value="1" ' .
			checked( 1, get_option( 'mfrh_log' ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br/><small>Simple logging that explains which actions has been run. The file is <a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'media-file-renamer.log">media-file-renamer.log</a>.</small>', 'media-file-renamer' );
    echo $html;
  }

	function admin_logsql_callback( $args ) {
    $value = get_option( 'mfrh_logsql', null );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_logsql" name="mfrh_logsql" value="1" ' .
			checked( 1, get_option( 'mfrh_logsql' ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br/><small>The files <a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'mfrh_sql.log">mfrh_sql.log</a> and <a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'mfrh_sql_revert.log">mfrh_sql_revert.log</a> will be created and they will include the raw SQL queries which were run by the plugin. If there is an issue, the revert file can help you reverting the changes more easily.</small>', 'media-file-renamer' );
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
