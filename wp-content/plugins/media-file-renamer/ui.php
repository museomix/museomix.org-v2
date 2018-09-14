<?php

class Meow_MFRH_UI {
	private $core = null;
	private $admin = null;

	function __construct( $core, $admin ) {
		$this->core = $core;
		$this->admin = $admin;
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_rename_metabox' ) );
		add_action( 'wp_ajax_mfrh_rename_media', array( $this, 'wp_ajax_mfrh_rename_media' ) );
		add_action( 'wp_ajax_mfrh_undo_media', array( $this, 'wp_ajax_mfrh_undo_media' ) );
		add_action( 'wp_ajax_mfrh_lock_media', array( $this, 'wp_ajax_mfrh_lock_media' ) );
		add_action( 'wp_ajax_mfrh_analyze_media', array( $this, 'wp_ajax_mfrh_analyze_media' ) );
		add_action( 'wp_ajax_mfrh_render_column', array( $this, 'wp_ajax_mfrh_render_column' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'media_send_to_editor', array( $this, 'media_send_to_editor' ), 20, 3 );
		add_filter( 'option_active_plugins', array( $this, 'active_plugins' ) );

		// Column for Media Library
		$method = apply_filters( 'mfrh_method', 'media_title' );
		if ( $method != 'none' ) {
			add_filter( 'manage_media_columns', array( $this, 'add_media_columns' ) );
			add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
		}

		// Media Library Bulk Actions
		add_filter( 'bulk_actions-upload', array( $this, 'library_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-upload', array( $this, 'library_bulk_actions_handler' ), 10, 3 );
	}

	function active_plugins( $plugins ) {
		if ( // Media File Renamer is doing Ajax
			wp_doing_ajax() &&
			isset( $_REQUEST['action'] ) &&
			substr( $_REQUEST['action'], 0, 5 ) == 'mfrh_'
		) {
			// Remove the all active plugins except for this plugin itself and a few supported plugins
			foreach ( $plugins as $i => $plugin ) {
				if ( preg_match( '/\/media-file-renamer(-pro)?\.php$/', $plugin ) ) continue;
				if ( preg_match( '/^polylang(-pro)\//', $plugin ) ) continue; // Polylang
				unset( $plugins[$i] );
			}
		}
		return $plugins;
	}

	/**
	 * Renders a view within the views directory.
	 * @param string $view The name of the view to render
	 * @param array $data
	 * An associative array of variables to bind to the view.
	 * Each key turns into a variable name.
	 * @return string Rendered view
	 */
	function render_view( $view, $data = null ) {
		ob_start();
		if ( is_array( $data ) ) extract( $data );
		include( __DIR__ . "/views/$view.php" );
		return ob_get_clean();
	}

	/**
	 * Loads some scripts & styles for certain pages
	 * @param string $page The current page identifier
	 */
	function admin_enqueue_scripts( $page ) {
		global $mfrh_version;
		$base = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'mfrh_style', $base . 'style.css', array(), $mfrh_version );

		switch ( $page ) {
			case 'upload.php': // Media Library
				wp_enqueue_script( 'mfrh_media-library', $base . 'scripts/media-library.js', array( 'jquery' ), $mfrh_version );
				break;
		}
	}

	function admin_head() {
		if ( !empty( $_GET['mfrh_rename'] ) ) {
			$mfrh_rename = $_GET['mfrh_rename'];
			$this->core->rename( $mfrh_rename );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_rename' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_unlock'] ) ) {
			$mfrh_unlock = $_GET['mfrh_unlock'];
			delete_post_meta( $mfrh_unlock, '_manual_file_renaming' );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_unlock' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_undo'] ) ) {
			$mfrh_undo = $_GET['mfrh_undo'];
			$original_filename = get_post_meta( $mfrh_undo, '_original_filename', true );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_undo' ), $_SERVER['REQUEST_URI'] );
			$this->core->rename( $mfrh_undo, $original_filename );

			$fp = get_attached_file( $mfrh_undo );
			$path_parts = mfrh_pathinfo( $fp );
			$basename = $path_parts['basename'];
			if ( $basename == $original_filename )
				delete_post_meta( $mfrh_undo, '_original_filename' );
		}
		if ( !empty( $_GET['mfrh_lock'] ) ) {
			$mfrh_lock = $_GET['mfrh_lock'];
			add_post_meta( $mfrh_lock, '_manual_file_renaming', true, true );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_lock' ), $_SERVER['REQUEST_URI'] );
		}

		echo $this->render_view( 'admin-head' );
	}

	function admin_menu() {
		$method = apply_filters( 'mfrh_method', 'media_title' );
		if ( $method != 'none' ) {
			add_media_page( 'Media File Renamer', __( 'Rename', 'media-file-renamer' ), 'manage_options', 'rename_media_files', array( $this, 'rename_media_files' ) );
		}
	}

	function rename_media_files() {
		global $wpdb;
		if ( $locked = ( isset( $_GET ) && isset( $_GET['mfrh_lockall'] ) ) ) {
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
			$wpdb->query( "INSERT INTO $wpdb->postmeta (meta_key, meta_value, post_id)
				SELECT '_manual_file_renaming', 1, p.ID
				FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'"
			);
		}

		if ( isset( $_GET ) && isset( $_GET['mfrh_unlockall'] ) ) {
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
		}

		$checkFiles = null;
		if ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) )
			$checkFiles = $this->core->check_text();
		// FLAGGING
		// if ( get_option( 'mfrh_flagging' ) ) {
		// 	$this->core->file_counter( $flagged, $total, true );
		// }
		$all_media = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
		$manual_media = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming' AND meta_value = 1" );

		echo $this->render_view( 'menu-screen', array(
			'wpdb'  => $wpdb,
			'ui'    => $this,
			'core'  => $this->core,
			'admin' => $this->admin,
			'locked'       => $locked,
			'checkFiles'   => $checkFiles,
			'all_media'    => $all_media,
			'manual_media' => $manual_media
		) );
	}

	function add_rename_metabox() {
		add_meta_box( 'mfrh_media', 'Filename', array( $this, 'attachment_fields' ), 'attachment', 'side', 'high' );
	}

	function attachment_fields( $post ) {
		$info = mfrh_pathinfo( get_attached_file( $post->ID ) );
		$basename = $info['basename'];
		$is_manual = apply_filters( 'mfrh_manual', false );
		$html = '<input type="text" readonly class="widefat" name="mfrh_new_filename" value="' . $basename. '" />';
		$html .= __( '<p class="description">This feature is for <a target="_blank" href="https://store.meowapps.com/">Pro users</a> only.</p>', 'media-file-renamer' );
		echo apply_filters( "mfrh_admin_attachment_fields", $html, $post );
		return $post;
	}

	/**
	 *
	 * 'RENAME' LINK IN MEDIA LIBRARY
	 *
	 */

	function add_media_columns( $columns ) {
		$columns['mfrh_column'] = __( 'Rename', 'media-file-renamer' );
		return $columns;
	}

	function manage_media_custom_column( $column_name, $id ) {
		if ( $column_name !== 'mfrh_column' ) return;
		echo $this->render_column( $id );
	}

	/**
	 * Renders a custom column content for a specific post
	 * @param int $id The post id to render
	 * @return string Rendered content
	 */
	function render_column( $id ) {
		$r = $this->render_view( 'column', array(
			'ui'    => $this,
			'core'  => $this->core,
			'admin' => $this->admin,
			'id'    => $id
		) );
		return $r;
	}

	function generate_explanation( $file ) {

		static $previous = array();

		$smallDiv = '<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">';

		if ( $file['post_title'] == "" ) {
			echo " <a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br /><small>" . __( 'This title cannot be used for a filename.', 'media-file-renamer' ) . "</small>";
		}
		else if ( $file['desired_filename_exists'] ) {
			echo "<a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br />$smallDiv" . __( 'The ideal filename already exists. If you would like to use a count and rename it, enable the <b>Numbered Files</b> option in the plugin settings.', 'media-file-renamer' ) . "</div>";
		}
		else {
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
			$mfrh_scancheck = ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) ) ? '&mfrh_scancheck' : '';
			$mfrh_to_rename = ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) ? '&to_rename=1' : '';
			$modify_url = "post.php?post=" . $file['post_id'] . "&action=edit";
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";

			$isNew = true;
			if ( in_array( $file['desired_filename'], $previous ) )
				$isNew = false;
			else
				array_push( $previous, $file['desired_filename'] );

			echo "<a class='button button-primary auto-rename' href='?" . $page . $mfrh_scancheck . $mfrh_to_rename . "&mfrh_rename=" . $file['post_id'] . "'>" . __( 'Auto-Rename', 'media-file-renamer' ) . "</a>";
			echo "<a title='" . __( 'Click to lock it to manual only.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_lock=" . $file['post_id'] . "' class='lock'><span style='font-size: 16px; margin-top: 5px;' class='dashicons dashicons-unlock'></span></a>";

			if ( $file['case_issue'] ) {
				echo '<br />' . $smallDiv .
					sprintf( __( 'Rename in lowercase, to %s. You can also <a href="%s">edit this media</a>.', 'media-file-renamer' ),
					$file['desired_filename'], $modify_url ) . "</div>";
			}
			else {
				echo '<br />' . $smallDiv .
					sprintf( __( 'Rename to %s. You can also <a href="%s">EDIT THIS MEDIA</a>.', 'media-file-renamer' ),
					$file['desired_filename'], $modify_url ) . "</div>";
			}

			if ( !$isNew ) {
				echo $smallDiv . "<i>";
				echo __( 'The first media you rename will actually get this filename; the next will be either not renamed or will have a counter appended to it.', 'media-file-renamer' );
				echo '</i></div>';
			}
		}
	}

	/**
	 *
	 * BULK ACTIONS IN MEDIA LIBRARY
	 *
	 */

	function library_bulk_actions( $bulk_actions ) {
		$bulk_actions['mfrh_lock_all'] = __( 'Lock (Renamer)', 'media-file-renamer');
		$bulk_actions['mfrh_unlock_all'] = __( 'Unlock (Renamer)', 'media-file-renamer');
		$bulk_actions['mfrh_rename_all'] = __( 'Rename (Renamer)', 'media-file-renamer');
		return $bulk_actions;
	}

	function library_bulk_actions_handler( $redirect_to, $doaction, $ids ) {
		if ( $doaction == 'mfrh_lock_all' ) {
			foreach ( $ids as $post_id ) {
				add_post_meta( $post_id, '_manual_file_renaming', true, true );
			}
		}
		if ( $doaction == 'mfrh_unlock_all' ) {
			foreach ( $ids as $post_id ) {
				delete_post_meta( $post_id, '_manual_file_renaming' );
			}
		}
		if ( $doaction == 'mfrh_rename_all' ) {
			foreach ( $ids as $post_id ) {
				$this->core->rename( $post_id );
			}
		}
		return $redirect_to;
	}

	/**
	 *
	 * BULK MEDIA RENAME PAGE
	 *
	 */

	function wp_ajax_mfrh_rename_media() {
		$subaction = $_POST['subaction'];
		if ( $subaction == 'getMediaIds' ) {
			$all = intval( $_POST['all'] );
			global $wpdb;
			$ids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
			if ( !$all ) {
				$idsToRemove = $wpdb->get_col( "SELECT m.post_id FROM $wpdb->postmeta m
					WHERE m.meta_key = '_manual_file_renaming' and m.meta_value = 1" );
				$ids = array_values( array_diff( $ids, $idsToRemove ) );
			}
			else {
				// We rename all, so we should unlock everything.
				$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
			}
			$reply = array();
			$reply['ids'] = $ids;
			$reply['total'] = count( $ids );
			echo json_encode( $reply );
			die;
		}
		else if ( $subaction == 'renameMediaId' ) {
			$id = intval( $_POST['id'] );
			$newName = array_key_exists( 'newName', $_POST ) ? $_POST['newName'] : null;
			if ( isset( $newName ) ) { // Manual Rename
				if ( !$this->admin->is_registered() ) {
					wp_send_json_error( __( 'This feature is for Pro users only', 'media-file-renamer' ) );
				} else if ( !get_option( 'mfrh_manual_rename' ) ) {
					wp_send_json_error( __( 'You need to enable Manual Rename in the plugin settings', 'media-file-renamer' ) );
				}
			}
			$this->core->rename( $id, $newName );
			$file = get_attached_file( $id );
			wp_send_json_success( array (
				'filename' => mfrh_basename( $file ),
				'ids' => $this->core->get_posts_by_attached_file( $file )
			) );
		}
		echo 0;
		die();
	}

	function wp_ajax_mfrh_undo_media() {
		$subaction = $_POST['subaction'];
		if ( $subaction == 'getMediaIds' ) {
			global $wpdb;
			$ids = $wpdb->get_col( "
				SELECT p.ID FROM $wpdb->posts p
				WHERE post_status = 'inherit' AND post_type = 'attachment'" );
			$reply = array();
			$reply['ids'] = $ids;
			$reply['total'] = count( $ids );
			echo json_encode( $reply );
			die;
		}
		else if ( $subaction == 'undoMediaId' ) {
			$id = intval( $_POST['id'] );
			$original_filename = get_post_meta( $id, '_original_filename', true );
			$this->core->rename( $id, $original_filename );
			delete_post_meta( $id, '_original_filename' );
			$file = get_attached_file( $id );
			wp_send_json_success( array (
				'filename' => mfrh_basename( $file ),
				'ids' => $this->core->get_posts_by_attached_file( $file )
			) );
		}
		echo 0;
		die();
	}

	/**
	 * An ajax action to lock a media.
	 *
	 * Ajax parameters:
	 * - id : The post id to lock
	 * - subaction : (optional)
	 *     + 'unlock' : Unlocks the post
	 */
	function wp_ajax_mfrh_lock_media() {
		if ( !isset( $_POST['id'] ) )
			wp_send_json_error( __( 'Invalid request', 'media-file-renamer' ) );

		// Default operation
		if ( !isset( $_POST['subaction'] ) ) {
			if ( !$this->core->lock( (int) $_POST['id'] ) )
				wp_send_json_error( __( 'Failed to lock', 'media-file-renamer' ) );
			wp_send_json_success();
		}

		// Optional operations
		switch ( $_POST['subaction'] ) {
			case 'unlock':
				if ( !$this->core->unlock( (int) $_POST['id'] ) )
					wp_send_json_error( __( 'Failed to unlock', 'media-file-renamer' ) );
				wp_send_json_success();
				break;
		}

		wp_send_json_error( __( 'Invalid request', 'media-file-renamer' ) );
	}

	/**
	 * An ajax action that analyzes a media
	 *
	 * Ajax parameters:
	 * - id : The post id to analyze
	 */
	function wp_ajax_mfrh_analyze_media() {
		if ( !isset( $_POST['id'] ) )
			wp_send_json_error( __( 'Invalid request', 'media-file-renamer' ) );

		if ( !$post = get_post( (int) $_POST['id'], ARRAY_A ) )
			wp_send_json_error( __( 'No such post', 'media-file-renamer' ) );

		$result = array ();
		$this->core->check_attachment( $post, $result );
		wp_send_json_success( $result );
	}

	/**
	 * An ajax action that simply calls render_column() and returns the result.
	 *
	 * Ajax parameters:
	 * - id : The post id to render
	 */
	function wp_ajax_mfrh_render_column() {
		if ( !isset( $_POST['id'] ) ) wp_send_json_error();
		wp_send_json_success( $this->render_column( (int) $_POST['id'] ) );
	}

	/**
	 *
	 * ERROR/INFO MESSAGE HANDLING
	 *
	 */

	function admin_notices() {
		$screen = get_current_screen();
		if ( ( $screen->base == 'post' && $screen->post_type == 'attachment' ) ||
			( $screen->base == 'media' && isset( $_GET['attachment_id'] ) ) ) {
			$id = isset( $_GET['post'] ) ? $_GET['post'] : $_GET['attachment_id'];
			if ( $this->core->check_attachment( get_post( $id, ARRAY_A ), $output ) ) {
				if ( $output['desired_filename_exists'] ) {
					echo '<div class="error"><p>
						The file ' . $output['desired_filename'] . ' already exists. Please give a new title for this media.
					</p></div>';
				}
			}
			if ( $this->core->wpml_media_is_installed() && !$this->core->is_real_media( $id ) ) {
				echo '<div class="error"><p>
					This attachment seems to be a virtual copy (or translation). Media File Renamer will not make any modification from here.
				</p></div>';
			}
		}
	}

	function media_send_to_editor( $html, $id, $attachment ) {
		$this->core->check_attachment( get_post( $id, ARRAY_A ), $output );
		return $html;
	}
}
