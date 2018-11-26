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

			add_settings_field( 'mfrh_convert_to_ascii', "Transliteration<br /><i>To ASCII</i> (Pro)",
				array( $this, 'admin_convert_to_ascii_callback' ),
				'mfrh_settings-menu', 'mfrh_settings' );

			register_setting( 'mfrh_settings', 'mfrh_auto_rename' );
			register_setting( 'mfrh_settings', 'mfrh_on_upload' );
			register_setting( 'mfrh_settings', 'mfrh_rename_slug' );
			register_setting( 'mfrh_settings', 'mfrh_convert_to_ascii' );

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

			if ( $method == 'media_title' || $method == 'post_title' || $method == 'product_title' ) {
				add_settings_field( 'mfrh_sync_alt', "Sync ALT<br />(Pro)",
					array( $this, 'admin_sync_alt_callback' ),
					'mfrh_advanced_settings-menu', 'mfrh_advanced_settings' );
			}
			if ( $method == 'post_title' || $method == 'product_title' || $method == 'alt_text' ) {
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
						<?php
						printf(
							/* Translators: %s: link to tutorial */
							esc_html__( 'This plugin works out of the box, the default settings are the best for most installs. However, you should have a look at the %s.', 'media-file-renamer' ),
							'<a target="_blank" href="https://meowapps.com/media-file-renamer/">' . esc_html__( 'tutorial', 'media-file-renamer' ) . '</a>'
						);
						?>
						<p class="submit">
							<a class="button button-primary" href="upload.php?page=rename_media_files">
								<?php esc_html_e( "Access the Renamer Dashboard", 'media-file-renamer' ); ?>
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
    $html .= '<label>' .esc_html__( 'Update slug with filename', 'media-file-renamer' ). '</label><br />';
    $html .= '<small>' .esc_html__( 'Better to keep this un-checked as the link might have been referenced somewhere else.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_convert_to_ascii_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_convert_to_ascii" name="mfrh_convert_to_ascii" value="1" ' .
			checked( 1, apply_filters( 'mfrh_converts', false ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'Replace accents, umlauts, cyrillic, diacritics, by their ASCII equivalent.', 'media-file-renamer' )
		      .'<br /><i>' .esc_html__( 'Examples: ', 'media-file-renamer' ). 'tête -> tete, schön -> schon, Добро -> dobro, etc.</i></small>';
		echo $html;
	}

	function admin_manual_rename_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_manual_rename" name="mfrh_manual_rename" value="1" ' .
			checked( 1, apply_filters( 'mfrh_manual', false ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'Manual field will be enabled in the Media Library and the Media Edit Screen.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_numbered_files_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_numbered_files" name="mfrh_numbered_files" value="1" ' .
			checked( 1, apply_filters( 'mfrh_numbered', false ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable Numbering', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'Identical filenames will be allowed by the plugin and a number will be appended automatically (myfile.jpg, myfile-2.jpg, myfile-3.jpg, etc).', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_rename_guid_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_rename_guid" name="mfrh_rename_guid" value="1" ' .
			checked( 1, get_option( 'mfrh_rename_guid' ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Update GUID with Filename', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'The GUID will be renamed like the new filename. Better to keep this un-checked.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_sync_alt_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_sync_alt" name="mfrh_sync_alt" value="1" ' .
			checked( 1, get_option( 'mfrh_sync_alt' ), false ) . '/>';

		$what = '';
		$method = apply_filters( 'mfrh_method', 'media_title' );
		switch ( $method ) {
		case 'media_title':
			$what = __( "Title of Media", 'media-file-renamer' );
			break;
		case 'post_title':
			$what = __( "Attached Post Title", 'media-file-renamer' );
			break;
		case 'product_title':
			$what = __( "Title of Product", 'media-file-renamer' );
			break;
		default:
			$what = __( "Error!", 'media-file-renamer' );
		}
		$label = sprintf(
			/* Translators: %1$s: update target name, %2$s: update resourse name */
			esc_html__( 'Update %1$s with %2$s', 'media-file-renamer' ),
			esc_html__( 'ALT', 'media-file-renamer' ),
			'<b>' .$what. '</b>'
		);
		$html .= '<label>' .$label. '</label><br />';
		$html .= '<small>' .esc_html__( 'Keep in mind that the HTML of your posts and pages WILL NOT be modified, as that is simply too dangerous for a plug-in.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_sync_media_title_callback( $args ) {
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_sync_media_title" name="mfrh_sync_media_title" value="1" ' .
			checked( 1, get_option( 'mfrh_sync_media_title' ), false ) . '/>';
		$what = __( "Error!", 'media-file-renamer' );
		$method = apply_filters( 'mfrh_method', 'media_title' );
		switch ( $method ) {
		case 'alt_text':
			$what = __( "Media ALT", 'media-file-renamer' );
			break;
		case 'post_title':
			$what = __( "Attached Post Title", 'media-file-renamer' );
			break;
		case 'product_title':
			$what = __( "Title of Product", 'media-file-renamer' );
			break;
		}
		$label = sprintf(
			/* Translators: %1$s: update target name, %2$s: update resourse name */
			esc_html__( 'Update %1$s with %2$s', 'media-file-renamer' ),
			esc_html__( 'Media Title', 'media-file-renamer' ),
			'<b>' .$what. '</b>'
		);
		$html .= '<label>' .$label. '</label><br />';
		$html .= '<small>' .esc_html__( 'Keep in mind that the HTML of your posts and pages WILL NOT be modified, as that is simply too dangerous for a plug-in.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_undo_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_undo" name="mfrh_undo" value="1" ' .
			checked( 1, get_option( 'mfrh_undo', false ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'A little undo icon will be added in the Rename column (Media Library). When clicked, the filename will be renamed back to the original.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_auto_rename_callback( $args ) {
		$r = '';
		$value = apply_filters( 'mfrh_method', 'media_title' );

		// Available options
		$options = array (
			0 => array (
				'value' => 'media_title',
				'label' => 'Title of Media'
			),
			10 => array (
				'value' => 'post_title',
				'label' => 'Attached Post Title (Pro)',
				'disabled' => !$this->is_registered()
			),
			20 => array (
				'value' => 'alt_text',
				'label' => 'Alternative Text (Pro)',
				'disabled' => !$this->is_registered()
			),
			100 => array (
				'value' => 'none',
				'label' => 'None'
			)
		);
		//// WooCommerce suppport
		$x = is_plugin_active( 'woocommerce/woocommerce.php' );
		$options[1] = array (
			'value' => 'product_title',
			'label' => 'Title of Product (Pro)',
			'disabled' => !$x || !$this->is_registered(),
			'hidden'   => !$x
		);
		// Convert the options to HTML
		ksort( $options );
		foreach ( $options as $option ) {
			$option['selected'] = $value == $option['value'];
			$r .= $this->elm( 'option', $option, $option['label'] );
		}
		// Wrap with <select>
		$r = $this->elm( 'select', array (
			'id'   => 'mfrh_auto_rename',
			'name' => 'mfrh_auto_rename'
		), $r );
		// Add a note
		$r = "<label>{$r}</label>" . '<small><br />' . __( 'If the plugin considers that it is too dangerous to rename the file directly at some point, it will be flagged internally <b>as to be renamed</b>. The list of those flagged files can be found in Media > File Renamer and they can be renamed from there.', 'media-file-renamer' ) . '</small>';

		echo $r;
	}

	function admin_on_upload_callback( $args ) {
		$html = '<input type="checkbox" id="mfrh_on_upload" name="mfrh_on_upload" value="1" ' .
			checked( 1, get_option( 'mfrh_on_upload', false ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'During upload, the filename will be renamed based on the title of the media if there is any EXIF with the file. Otherwise, it will optimize the upload filename.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_update_postmeta_callback( $args ) {
    $value = get_option( 'mfrh_update_postmeta', true );
		$html = '<input type="checkbox" id="mfrh_update_postmeta" name="mfrh_update_postmeta" value="1" ' .
			checked( 1, get_option( 'mfrh_update_postmeta', true ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$desc = sprintf(
			/* Translators: %s: custom fields */
			esc_html__( 'Update the references in the %s of the posts (including pages and custom types metadata).', 'media-file-renamer' ),
			'<b>'.esc_html__( 'custom fields', 'media-file-renamer' ).'</b>'
		);
		$html .= '<small>' .$desc. '</small>';
    echo $html;
  }

	function admin_update_posts_callback( $args ) {
    $value = get_option( 'mfrh_update_posts', true );
		$html = '<input type="checkbox" id="mfrh_update_posts" name="mfrh_update_posts" value="1" ' .
			checked( 1, get_option( 'mfrh_update_posts', true ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$desc = sprintf(
			/* Translators: %1$s: content, %2$s: excerpt, */
			esc_html__( 'Update the references to the renamed files in the %1$s and %2$s of the posts (pages and custom types included).', 'media-file-renamer' ),
			'<b>'.esc_html__( 'content', 'media-file-renamer' ).'</b>',
			'<b>'.esc_html__( 'excerpt', 'media-file-renamer' ).'</b>'
		);
		$html .= '<small>' .$desc. '</small>';
    echo $html;
  }

	function admin_rename_on_save_callback( $args ) {
    $value = get_option( 'mfrh_rename_on_save', null );
		$html = '<input type="checkbox" id="mfrh_rename_on_save" name="mfrh_rename_on_save" value="1" ' .
			checked( 1, get_option( 'mfrh_rename_on_save' ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable (NOT RECOMMENDED)', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'You can modify the titles of your media while editing a post but, of course, the plugin can\'t update the HTML at this stage. With this option, the plugin will update the filenames and HTML after that you saved the post.', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_force_rename_callback( $args ) {
    $value = get_option( 'mfrh_force_rename', false );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_force_rename" name="mfrh_force_rename" value="1" ' .
			checked( 1, get_option( 'mfrh_force_rename' ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$html .= '<small>' .esc_html__( 'Update the references to the file even if the file renaming itself was not successful. You might want to use that option if your install is broken and you are trying to link your Media to files for which the filenames has been altered (after a migration for exemple)', 'media-file-renamer' ). '</small>';
    echo $html;
  }

	function admin_log_callback( $args ) {
    $value = get_option( 'mfrh_log', null );
		$html = '<input type="checkbox" id="mfrh_log" name="mfrh_log" value="1" ' .
			checked( 1, get_option( 'mfrh_log' ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$desc = sprintf(
			/* Translators: %s: link to media-file-renamer.log */
			esc_html__( 'Simple logging that explains which actions has been run. The file is %s.', 'media-file-renamer' ),
			'<a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'media-file-renamer.log">media-file-renamer.log</a>'
		);
		$html .= '<small>' .$desc. '</small>';
    echo $html;
  }

	function admin_logsql_callback( $args ) {
    $value = get_option( 'mfrh_logsql', null );
		$html = '<input ' . disabled( $this->is_registered(), false, false ) . ' type="checkbox" id="mfrh_logsql" name="mfrh_logsql" value="1" ' .
			checked( 1, get_option( 'mfrh_logsql' ), false ) . '/>';
		$html .= '<label>' .esc_html__( 'Enable', 'media-file-renamer' ). '</label><br />';
		$desc = sprintf(
			/* Translators: %1$s: link to mfrh_sql.log, %2$s: link to mfrh_sql_revert.log */
			esc_html__( 'The files %1$s and %2$s will be created and they will include the raw SQL queries which were run by the plugin. If there is an issue, the revert file can help you reverting the changes more easily.', 'media-file-renamer' ),
			'<a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'mfrh_sql.log">mfrh_sql.log</a>',
			'<a target="_blank" href="' . plugin_dir_url( __FILE__ ) . 'mfrh_sql_revert.log">mfrh_sql_revert.log</a>'
		);
		$html .= '<small>' .$desc. '</small>';
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

	/**
	 * TODO: Move to the common library
	 * Composes HTML expression for a single element
	 * @param string $tag Tag name
	 * @param array|string $attrs Attributes
	 * @param string $content='' Content. Null omits the closing tag
	 * @return string HTML expression for an element
	 */
	public function elm( $tag, $attrs = null, $content = '' ) {
		$r = "<{$tag}";
		if ( $attrs ) $r .= is_string( $attrs ) ? " {$attrs}" : $this->attrs( $attrs );
		$r .= '>';
		if ( is_null( $content ) ) return $r;
		return "{$r}{$content}</{$tag}>";
	}

	/**
	 * TODO: Move to the common library
	 * Converts an associative array to HTML attributes
	 * @param array $map An associative array
	 * @return string HTML expression for attributes
	 */
	public function attrs( $map ) {
		$r = '';
		foreach ( $map as $attr => $value ) $r .= $this->attr( $attr, $value );
		return $r;
	}

	/**
	 * TODO: Move to the common library
	 * Composes HTML expression for a single attribute.
	 * If the value was exact FALSE, returns empty string
	 * @param string $name
	 * @param mixed $value
	 * @return string HTML expression for an attribute
	 */
	public function attr( $name, $value ) {
		if ( is_null( $value ) ) return '';
		if ( is_bool( $value ) ) return $value ? " {$name}" : '';
		return " {$name}=\"" . ( esc_attr( (string) $value ) ) . '"';
	}
}

?>
