<?php

class Meow_WPMC_Core {

	public $checkers = null;
	public $admin = null;
	public $debug = false;

	public $last_analysis = null;
	public $last_analysis_ids = null;

	public function __construct( $admin ) {
		$this->admin = $admin;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_inline_js' ) );
		add_action( 'wp_ajax_wpmc_scan', array( $this, 'wp_ajax_wpmc_scan' ) );
		add_action( 'wp_ajax_wpmc_get_all_issues', array( $this, 'wp_ajax_wpmc_get_all_issues' ) );
		add_action( 'wp_ajax_wpmc_get_all_deleted', array( $this, 'wp_ajax_wpmc_get_all_deleted' ) );
		add_action( 'wp_ajax_wpmc_scan_do', array( $this, 'wp_ajax_wpmc_scan_do' ) );
		add_action( 'wp_ajax_wpmc_prepare_do', array( $this, 'wp_ajax_wpmc_prepare_do' ) );
		add_action( 'wp_ajax_wpmc_delete_do', array( $this, 'wp_ajax_wpmc_delete_do' ) );
		add_action( 'wp_ajax_wpmc_ignore_do', array( $this, 'wp_ajax_wpmc_ignore_do' ) );
		add_action( 'wp_ajax_wpmc_recover_do', array( $this, 'wp_ajax_wpmc_recover_do' ) );
		add_filter( 'media_row_actions', array( $this, 'media_row_actions' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		}

		// Checkers
		require( 'wpmc_checkers.php' );
		$this->checkers = new Meow_WPMC_Checkers( $this );
	}

	/*******************************************************************************
	 * METABOX FOR USAGE
	 ******************************************************************************/

	function add_metabox() {
 		add_meta_box( 'mfrh_media_usage_box', 'Media Cleaner', array( $this, 'display_metabox' ), 'attachment', 'side', 'default' );
 	}

	function display_metabox( $post ) {
		if ( $this->wpmc_check_media( $post->ID ) ) {
			if ( $this->last_analysis == "CONTENT" ) {
				$result = array();
				foreach ( $this->last_analysis_ids as $id )
					array_push( $result, "<a href='post.php?post=$id&action=edit'>$id</a>" );
				echo "Seems to be used in your content (" . implode( ', ', $result ) . ").";
			}
			else if ( $this->last_analysis == "BACKGROUND_OR_HEADER" ) {
				echo "Seems to be used as a background or header.";
			}
			else if ( $this->last_analysis == "GALLERY" ) {
				echo "Seems to be used in a gallery.";
			}
			else if ( $this->last_analysis == "META" ) {
				echo "Seems to be used as a meta.";
			}
			else {
				echo "Used: " . $this->last_analysis;
			}
		}
		else {
			echo "Doesn't seem to be used.";
		}
	}

	/*******************************************************************************
	 * ASYNCHRONOUS AJAX FUNCTIONS
	 ******************************************************************************/

	function wp_ajax_wpmc_delete_do () {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success += ( $this->wpmc_delete( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_ignore_do () {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success += ( $this->wpmc_ignore( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_recover_do () {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success +=  ( $this->wpmc_recover( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_scan_do () {
		// For debug, to pretend there is a timeout
		// header("HTTP/1.0 408 Request Timeout");
		// exit;
		ob_start();
		$type = $_POST['type'];
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			if ( $type == 'file' ) {
				if ( $this->debug )
					error_log( "Check File: {$piece}" );
				$success += ( apply_filters( 'wpmc_check_file', true, $piece ) ? 1 : 0 );
				if ( $this->debug )
					error_log( "Success " . $success . "\n" );
			} elseif ( $type == 'media' ) {
				if ( $this->debug )
					error_log( "Check Media: {$piece}" );
				$success += ( $this->wpmc_check_media( $piece ) ? 1 : 0 );
				if ( $this->debug )
					error_log( "Success " . $success . "\n" );
			}
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'type' => $type, 'data' => $data, 'success' => $success ),
				'message' => __( "Items checked.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_get_all_deleted () {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$ids = $wpdb->get_col( "SELECT id FROM $table_name WHERE ignored = 0 AND deleted = 1" );
		echo json_encode(
			array(
				'results' => array( 'ids' => $ids ),
				'success' => true,
				'message' => __( "List generated.", 'media-cleaner' )
			)
		);
		die;
	}

	function wp_ajax_wpmc_get_all_issues () {
		global $wpdb;
		$isTrash = ( isset( $_POST['isTrash'] ) && $_POST['isTrash'] == 1 ) ? true : false;
		$table_name = $wpdb->prefix . "wpmcleaner";
		if ( $isTrash )
			$ids = $wpdb->get_col( "SELECT id FROM $table_name WHERE ignored = 0 AND deleted = 1" );
		else
			$ids = $wpdb->get_col( "SELECT id FROM $table_name WHERE ignored = 0 AND deleted = 0" );
		echo json_encode(
			array(
				'results' => array( 'ids' => $ids ),
				'success' => true,
				'message' => __( "List generated.", 'media-cleaner' )
			)
		);
		die;
	}

	function wp_ajax_wpmc_prepare_do() {
		$limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
		$limitsize = get_option( 'wpmc_posts_buffer', 10 );
		if ( empty( $limit ) )
			$this->wpmc_reset_issues();
		if ( !get_option( 'wpmc_galleries', false ) ) {
			echo json_encode( array(
				'results' => array(),
				'success' => true,
				'finished' => true,
				'message' => __( "Galleries check is off. Prepare finished.", 'media-cleaner' )
			) );
			die();
		}
		$galleries_images = get_transient( "wpmc_galleries_images" );
		if ( empty( $galleries_images ) )
			$galleries_images = array();
		global $wpdb;
		$posts = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p
			WHERE p.post_status != 'inherit'
			AND p.post_type != 'attachment'
			LIMIT %d, %d", $limit, $limitsize
			)
		);
		foreach ( $posts as $post ) {
			$galleries = get_post_galleries_images( $post );
			foreach ( $galleries as $gallery ) {
				foreach ( $gallery as $image ) {
					array_push( $galleries_images, $image );
				}
			}
		}
		set_transient( "wpmc_galleries_images", $galleries_images, 60 * 60 * 2 );
		$finished = count( $posts ) < $limitsize;
		echo json_encode(
			array(
				'success' => true,
				'finished' => $finished,
				'limit' => $limit + $limitsize,
				'message' => __( "Galleries prepared.", 'media-cleaner' ) )
		);
		die();
	}

	function wp_ajax_wpmc_scan() {
		global $wpdb;

		$method = get_option( 'wpmc_method', 'media' );
		if ( !$this->admin->is_registered() )
			$method = 'media';
		$path = isset( $_POST['path'] ) ? $_POST['path'] : null;
		$limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
		$limitsize = get_option( 'wpmc_medias_buffer', 500 );

		if ( $method == 'files' ) {
			$output = apply_filters( 'wpmc_list_uploaded_files', array(
				'results' => array(), 'success' => false, 'message' => __( "Unavailable.", 'media-cleaner' )
			), $path );
			echo json_encode( $output );
			die();
		}

		if ( $method == 'media' ) {
			// Prevent double scanning by removing filesystem entries that we have DB entries for
			$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p
				WHERE p.post_status = 'inherit'
				AND p.post_type = 'attachment'
				LIMIT %d, %d", $limit, $limitsize
				)
			);
			$finished = count( $results ) < $limitsize;
			echo json_encode(
				array(
					'results' => $results,
					'success' => true,
					'finished' => $finished,
					'limit' => $limit + $limitsize,
					'message' => __( "Medias retrieved.", 'media-cleaner' ) )
			);
			die();
		}

		// No task.
		echo json_encode( array( 'success' => false, 'message' => __( "No task.", 'media-cleaner' ) ) );
		die();
	}

	/**
	 *
	 * HELPERS
	 *
	 */

	function wpmc_trashdir() {
		$upload_folder = wp_upload_dir();
		return trailingslashit( $upload_folder['basedir'] ) . 'wpmc-trash';
	}

	function wpmc_check_db() {
		global $wpdb;
		$tbl_m = $wpdb->prefix . 'wpmcleaner';
		if ( !$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = '%s' AND table_name = '%s';", $wpdb->dbname, $tbl_m ) ) ) {
			wpmc_activate();
		}
	}

	/**
	 *
	 * DELETE / SCANNING / RESET
	 *
	 */

	function wpmc_recover_file( $path ) {
		$basedir = wp_upload_dir();
		$originalPath = trailingslashit( $basedir['basedir'] ) . $path;
		$trashPath = trailingslashit( $this->wpmc_trashdir() ) . $path;
		$path_parts = pathinfo( $originalPath );
		if ( !file_exists( $path_parts['dirname'] ) && !wp_mkdir_p( $path_parts['dirname'] ) ) {
			die( 'Failed to create folder.' );
		}
		if ( !file_exists( $trashPath ) ) {
			if ( $this->debug )
				error_log( "The file $originalPath actually does not exist in the trash." );
			return true;
		}
		if ( !rename( $trashPath, $originalPath ) ) {
			die( 'Failed to move the file.' );
		}
		return true;
	}

	function wpmc_recover( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$issue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), OBJECT );
		$issue->path = stripslashes( $issue->path );

		// Files
		if ( $issue->type == 0 ) {
			$this->wpmc_recover_file( $issue->path );
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 0 WHERE id = %d", $id ) );
			return true;
		}
		// Media
		else if ( $issue->type == 1 ) {

			// Copy the main file back
			$fullpath = get_attached_file( $issue->postId );
			$mainfile = $this->wpmc_clean_uploaded_filename( $fullpath );
			$baseUp = pathinfo( $mainfile );
			$baseUp = $baseUp['dirname'];
			$file = $this->wpmc_clean_uploaded_filename( $fullpath );
			if ( !$this->wpmc_recover_file( $file ) )
				error_log( "Could not recover $file." );

			// If images, copy the other files as well
			$meta = wp_get_attachment_metadata( $issue->postId );
			$isImage = isset( $meta, $meta['width'], $meta['height'] );
			$sizes = $this->wpmc_get_image_sizes();
			if ( $isImage && isset( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $name => $attr ) {
					if  ( isset( $attr['file'] ) ) {
						$filepath = wp_upload_dir();
						$filepath = $filepath['basedir'];
						$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
						$file = $this->wpmc_clean_uploaded_filename( $filepath );
						if ( !$this->wpmc_recover_file( $file ) )
							error_log( "Could not recover $file." );
					}
				}
			}
			if ( !wp_untrash_post( $issue->postId ) )
				die( "Failed to untrash Media {$issue->postId}." );
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 0 WHERE id = %d", $id ) );
			return true;
		}
	}

	function wpmc_trash_file( $fileIssuePath ) {
		global $wpdb;
		$basedir = wp_upload_dir();
		$originalPath = trailingslashit( $basedir['basedir'] ) . $fileIssuePath;
		$trashPath = trailingslashit( $this->wpmc_trashdir() ) . $fileIssuePath;
		$path_parts = pathinfo( $trashPath );

		try {
			if ( !file_exists( $path_parts['dirname'] ) && !wp_mkdir_p( $path_parts['dirname'] ) ) {
				return false;
			}
			// Rename the file (move). 'is_dir' is just there for security (no way we should move a whole directory)
			if ( is_dir( $originalPath ) ) {
				error_log( "Attempted to delete a directory instead of a file ($originalPath). Can't do that." );
				return false;
			}
			if ( !file_exists( $originalPath ) ) {
				if ( $this->debug )
					error_log( "The file $originalPath actually does not exist." );
				return true;
			}
			if ( !rename( $originalPath, $trashPath ) ) {
				return false;
			}
		}
		catch ( Exception $e ) {
			return false;
		}
		$this->wpmc_clean_dir( dirname( $originalPath ) );
		return true;
	}

	function wpmc_ignore( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET ignored = 1 WHERE id = %d", $id ) );
		return true;
	}

	function wpmc_endsWith( $haystack, $needle )
	{
	  $length = strlen( $needle );
	  if ( $length == 0 )
	    return true;
	  return ( substr( $haystack, -$length ) === $needle );
	}

	function wpmc_clean_dir( $dir ) {
		if ( !file_exists( $dir ) )
			return;
		else if ( $this->wpmc_endsWith( $dir, 'uploads' ) )
			return;
		$found = array_diff( scandir( $dir ), array( '.', '..' ) );
		if ( count( $found ) < 1 ) {
			if ( rmdir( $dir ) ) {
				$this->wpmc_clean_dir( dirname( $dir ) );
			}
		}
	}

	function wpmc_delete( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$issue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), OBJECT );
		$regex = "^(.*)(\\s\\(\\+.*)$";
		$issue->path = preg_replace('/'.$regex.'/i', '$1', $issue->path); // remove " (+ 6 files)" from path

		// Make sure there isn't a media DB entry
		if ( $issue->type == 0 ) {
			$attachmentid = $this->wpmc_find_attachment_id_by_file( $issue->path );
			if ( $attachmentid ) {
				error_log( "File Cleaner: Issue listed as filesystem but Media {$attachmentid} exists." );
			}
		}

		if ( $issue->type == 0 ) {

			if ( $issue->deleted == 0 ) {
				// Move file to the trash
				if ( $this->wpmc_trash_file( $issue->path ) )
					$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 1 WHERE id = %d", $id ) );
				return true;
			}
			else {
				// Delete file from the trash
				$trashPath = trailingslashit( $this->wpmc_trashdir() ) . $issue->path;
				if ( !unlink( $trashPath ) )
					error_log( 'Failed to delete the file.' );
				$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id ) );
				$this->wpmc_clean_dir( dirname( $trashPath ) );
				return true;
			}
		}

		if ( $issue->type == 1 ) {
			if ( $issue->deleted == 0 && MEDIA_TRASH ) {
				// Move Media to trash
				// Let's copy the images to the trash so that it can be recovered.
				$fullpath = get_attached_file( $issue->postId );
				$mainfile = $this->wpmc_clean_uploaded_filename( $fullpath );
				$baseUp = pathinfo( $mainfile );
				$baseUp = $baseUp['dirname'];
				$file = $this->wpmc_clean_uploaded_filename( $fullpath );
				if ( !$this->wpmc_trash_file( $file ) )
					error_log( "Could not trash $file." );

				// If images, check the other files as well
				$meta = wp_get_attachment_metadata( $issue->postId );
				$isImage = isset( $meta, $meta['width'], $meta['height'] );
				$sizes = $this->wpmc_get_image_sizes();
				if ( $isImage && isset( $meta['sizes'] ) ) {
					foreach ( $meta['sizes'] as $name => $attr ) {
						if  ( isset( $attr['file'] ) ) {
							$filepath = wp_upload_dir();
							$filepath = $filepath['basedir'];
							$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
							$file = $this->wpmc_clean_uploaded_filename( $filepath );
							if ( !$this->wpmc_trash_file( $file ) )
								error_log( "Could not trash $file." );
						}
					}
				}
				wp_delete_attachment( $issue->postId, false );
				$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 1 WHERE id = %d", $id ) );
				return true;
			}
			else {
				// Trash Media definitely by recovering it (to be like a normal Media) and remove it through the
				// standard WordPress workflow
				if ( MEDIA_TRASH )
					$this->wpmc_recover( $id );
				wp_delete_attachment( $issue->postId, true );
				$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id ) );
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * SCANNING / RESET
	 *
	 */

	function wpmc_check_is_ignore( $file ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$count = $wpdb->get_var( "SELECT COUNT(*)
			FROM $table_name
			WHERE ignored = 1
			AND path LIKE '%".  esc_sql( $wpdb->esc_like( $file ) ) . "%'" );
		if ( $this->debug && $count > 0 )
			error_log("{$file} found in IGNORE");
		return ($count > 0);
	}

	function wpmc_find_attachment_id_by_file ($file) {
		global $wpdb;
		$postmeta_table_name = $wpdb->prefix . 'postmeta';
		$file = $this->wpmc_clean_uploaded_filename( $file );
		$sql = $wpdb->prepare( "SELECT post_id
			FROM {$postmeta_table_name}
			WHERE meta_key = '_wp_attached_file'
			AND meta_value = %s", $file
		);
		$ret = $wpdb->get_var( $sql );
		if ( $this->debug && empty( $ret ) )
			error_log( "File $file not found as _wp_attached_file (Library)." );
		else if ( $this->debug ) {
			error_log( "File $file found as Media $ret." );
		}
		return $ret;
	}

	function wpmc_get_image_sizes() {
		$sizes = array();
		global $_wp_additional_image_sizes;
		foreach ( get_intermediate_image_sizes() as $s ) {
			$crop = false;
			if ( isset( $_wp_additional_image_sizes[$s] ) ) {
				$width = intval( $_wp_additional_image_sizes[$s]['width'] );
				$height = intval( $_wp_additional_image_sizes[$s]['height'] );
				$crop = $_wp_additional_image_sizes[$s]['crop'];
			} else {
				$width = get_option( $s.'_size_w' );
				$height = get_option( $s.'_size_h' );
				$crop = get_option( $s.'_crop' );
			}
			$sizes[$s] = array( 'width' => $width, 'height' => $height, 'crop' => $crop );
		}
		return $sizes;
	}


	// From a fullpath to the shortened and cleaned path (for example '2013/02/file.png')
	function wpmc_clean_uploaded_filename( $fullpath ) {
		$upload_folder = wp_upload_dir();
		$basedir = $upload_folder['basedir'];
		$file = str_replace( $basedir, '', $fullpath );
		$file = trim( $file,  "/" );
		return $file;
	}

	function wpmc_check_media( $attachmentId ) {

		$this->last_analysis = "N/A";

		// Is it an image?
		$meta = wp_get_attachment_metadata( $attachmentId );
		$isImage = isset( $meta, $meta['width'], $meta['height'] );

		// Get the main file
		global $wpdb;
		$fullpath = get_attached_file( $attachmentId );
		$mainfile = $this->wpmc_clean_uploaded_filename( $fullpath );
		$baseUp = pathinfo( $mainfile );
		$baseUp = $baseUp['dirname'];
		$size = 0;
		$countfiles = 0;
		$issue = 'NO_CONTENT';
		if ( file_exists( $fullpath ) ) {
			$size = filesize( $fullpath );

			// ANALYSIS
			$this->last_analysis = "NONE";
			if ( $this->wpmc_check_is_ignore( $mainfile ) ) {
				$this->last_analysis = "IGNORED";
				return true;
			}
			if ( $this->checkers->has_background_or_header( $mainfile ) ) {
				$this->last_analysis = "BACKGROUND_OR_HEADER";
				return true;
			}
			if ( $this->checkers->has_content( $mainfile ) ) {
				$this->last_analysis = "CONTENT";
				return true;
			}
			if ( $this->checkers->check_in_gallery( $mainfile ) ) {
				$this->last_analysis = "GALLERY";
				return true;
			}
			if ( $this->checkers->has_meta( $mainfile, $attachmentId ) ) {
				$this->last_analysis = "META";
				return true;
			}

			// If images, check the other files as well
			$countfiles = 0;
			$sizes = $this->wpmc_get_image_sizes();
			if ( $isImage && isset( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $name => $attr ) {
					if  ( isset( $attr['file'] ) ) {
						$filepath = wp_upload_dir();
						$filepath = $filepath['basedir'];
						$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
						if ( file_exists( $filepath ) ) {
							$size += filesize( $filepath );
						}
						$file = $this->wpmc_clean_uploaded_filename( $attr['file'] );
						$countfiles++;
						if ( $this->debug )
							error_log("checking MEDIA-IMAGE {$filepath}");

						// ANALYSIS
						if ( $this->checkers->check_in_gallery( $filepath ) ) {
							$this->last_analysis = "GALLERY";
							return true;
						}
						if ( $this->checkers->has_background_or_header( $filepath ) ) {
							$this->last_analysis = "BACKGROUND_OR_HEADER";
							return true;
						}
						if ( $this->checkers->has_meta( $filepath, $attachmentId ) ) {
							$this->last_analysis = "META";
							return true;
						}

					}
				}
			}
		} else {
			$issue = 'ORPHAN_MEDIA';
		}

		$table_name = $wpdb->prefix . "wpmcleaner";
		$wpdb->insert( $table_name,
			array(
				'time' => current_time('mysql'),
				'type' => 1,
				'size' => $size,
				'path' => $mainfile . ( $countfiles > 0 ? ( " (+ " . $countfiles . " files)" ) : "" ),
				'postId' => $attachmentId,
				'issue' => $issue
				)
			);
		return false;
	}

	// Delete all issues
	function wpmc_reset_issues( $includingIgnored = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		if ( $includingIgnored ) {
			$wpdb->query( "DELETE FROM $table_name WHERE deleted = 0" );
		}
		else {
			$wpdb->query( "DELETE FROM $table_name WHERE ignored = 0 AND deleted = 0" );
		}
		delete_transient( "wpmc_galleries_images" );
		delete_transient( 'wpmc_posts_with_shortcode' );
	}

	/**
	 *
	 * DASHBOARD
	 *
	 */

	function admin_inline_js() {
		echo "<script type='text/javascript'>\n";
		echo 'var wpmc_cfg = {
			delay: ' . get_option( 'wpmc_delay', 100 ) . ',
			analysisBuffer: ' . get_option( 'wpmc_analysis_buffer', 5 ) . ',
			isPro: ' . ( $this->admin->is_registered()  ? '1' : '0') . ',
			scanFiles: ' . ( ( get_option( 'wpmc_method', 'media' ) == 'files' && $this->admin->is_registered() ) ? '1' : '0' ) . ',
			scanMedia: ' . ( get_option( 'wpmc_method', 'media' ) == 'media' ? '1' : '0' ) . ' };';
		echo "\n</script>";
	}

	function echo_issue( $issue ) {
		if ( $issue == 'NO_CONTENT' ) {
			_e( "Seems not in use.", 'media-cleaner' );
		}
		else if ( $issue == 'NO_MEDIA' ) {
			_e( "Not in Media Library.", 'media-cleaner' );
		}
		else if ( $issue == 'ORPHAN_RETINA' ) {
			_e( "Orphan retina.", 'media-cleaner' );
		}
		else if ( $issue == 'ORPHAN_MEDIA' ) {
			_e( "File not found.", 'media-cleaner' );
		}
		else {
			echo $issue;
		}
	}

	function media_row_actions( $actions, $post ) {
		global $current_screen;
		if ( 'upload' != $current_screen->id )
		    return $actions;
		global $wpdb;
		$table_name = $wpdb->prefix . "wpmcleaner";
		$res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE postId = %d", $post->ID ) );
		if ( !empty( $res ) && isset( $actions['delete'] ) )
			$actions['delete'] = "<a href='?page=media-cleaner&view=deleted'>Delete with Media Cleaner</a>";
		if ( !empty( $res ) && isset( $actions['trash'] ) )
			$actions['trash'] = "<a href='?page=media-cleaner'>Trash with Media Cleaner</a>";
		if ( !empty( $res ) && isset( $actions['untrash'] ) ) {
			$actions['untrash'] = "<a href='?page=media-cleaner&view=deleted'>Restore with Media Cleaner</a>";
		}
		return $actions;
	}

	function wpmc_screen() {
		global $wplr;
		$this->wpmc_check_db();
		?>
		<div class='wrap'>

			<?php
				echo $this->admin->display_title( "Media Cleaner" );
				global $wpdb;
				$posts_per_page = get_user_meta( get_current_user_id(), 'upload_per_page', true );
				if ( empty( $posts_per_page ) )
					$posts_per_page = 20;
				$view = isset ( $_GET[ 'view' ] ) ? sanitize_text_field( $_GET[ 'view' ] ) : "issues";
				$paged = isset ( $_GET[ 'paged' ] ) ? sanitize_text_field( $_GET[ 'paged' ] ) : 1;
				$reset = isset ( $_GET[ 'reset' ] ) ? $_GET[ 'reset' ] : 0;
				if ( $reset ) {
					wpmc_reset();
				}
				$s = isset ( $_GET[ 's' ] ) ? sanitize_text_field( $_GET[ 's' ] ) : null;
				$table_name = $wpdb->prefix . "wpmcleaner";
				$issues_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ignored = 0 AND deleted = 0" );
				$total_size = $wpdb->get_var( "SELECT SUM(size) FROM $table_name WHERE ignored = 0 AND deleted = 0" );
				$trash_total_size = $wpdb->get_var( "SELECT SUM(size) FROM $table_name WHERE ignored = 0 AND deleted = 1" );
				$ignored_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ignored = 1" );
				$deleted_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE deleted = 1" );

				if ( $view == 'deleted' ) {
					$items_count = $deleted_count;
					$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
						FROM $table_name WHERE ignored = 0 AND deleted = 1 AND path LIKE %s
						ORDER BY time
						DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
				}
				else if ( $view == 'ignored' ) {
					$items_count = $ignored_count;
					$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
						FROM $table_name
						WHERE ignored = 1 AND deleted = 0 AND path LIKE %s
						ORDER BY time
						DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
				}
				else {
					$items_count = $issues_count;
					$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
						FROM $table_name
						WHERE ignored = 0 AND deleted = 0  AND path LIKE %s
						ORDER BY time
						DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
				}
			?>

			<style>
				#wpmc-pages {
					float: right;
					position: relative;
					top: 12px;
				}

				#wpmc-pages a {
					text-decoration: none;
					border: 1px solid black;
					padding: 2px 5px;
					border-radius: 4px;
					background: #E9E9E9;
					color: lightslategrey;
					border-color: #BEBEBE;
				}

				#wpmc-pages .current {
					font-weight: bold;
				}
			</style>

			<div style='margin-top: 0px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

				<!-- SCAN -->
				<?php if ( $view != 'deleted' ) { ?>
					<a id='wpmc_scan' onclick='wpmc_scan()' class='button-primary' style='float: left;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-search"></span><?php _e("Start Scan", 'media-cleaner'); ?></a>
				<?php } ?>

				<!-- PAUSE -->
				<?php if ( $view != 'deleted' ) { ?>
					<a id='wpmc_pause' onclick='wpmc_pause()' class='button' style='float: left; margin-left: 5px; display: none;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-pause"></span><?php _e("Pause", 'media-cleaner'); ?></a>
				<?php } ?>

				<!-- DELETE SELECTED -->
				<a id='wpmc_delete' onclick='wpmc_delete()' class='button' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-no"></span><?php _e("Delete", 'media-cleaner'); ?></a>
				<?php if ( $view == 'deleted' ) { ?>
					<a id='wpmc_recover' onclick='wpmc_recover()' class='button-secondary' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-repeat"></span><?php _e( "Recover", 'media-cleaner' ); ?></a>
				<?php } ?>

				<!-- IGNORE SELECTED -->
				<a id='wpmc_ignore' onclick='wpmc_ignore()' class='button' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-yes"></span><?php _e("Ignore", 'media-cleaner'); ?></a>

				<!-- RESET -->
				<?php if ( $view != 'deleted' ) { ?>
					<a id='wpmc_reset' href='?page=media-cleaner&reset=1' class='button-primary' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-sos"></span><?php _e("Reset", 'media-cleaner'); ?></a>
				<?php } ?>

				<!-- DELETE ALL -->
				<?php if ( $view == 'deleted' ) { ?>
					<a id='wpmc_recover_all' onclick='wpmc_recover_all()' class='button-primary' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-repeat"></span><?php _e("Recover all", 'media-cleaner'); ?></a>
					<a id='wpmc_delete_all' onclick='wpmc_delete_all(true)' class='button button-red' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-trash"></span><?php _e("Empty trash", 'media-cleaner'); ?></a>
				<?php } else { ?>
					<a id='wpmc_delete_all' onclick='wpmc_delete_all()' class='button button-red' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-trash"></span><?php _e("Delete all", 'media-cleaner'); ?></a>
				<?php } ?>

				<form id="posts-filter" action="upload.php" method="get" style='float: right;'>
					<p class="search-box" style='margin-left: 5px; float: left;'>
						<input type="search" name="s" style="width: 120px;" value="<?php echo $s ? $s : ""; ?>">
						<input type="hidden" name="page" value="media-cleaner">
						<input type="hidden" name="view" value="<?php echo $view; ?>">
						<input type="hidden" name="paged" value="<?php echo $paged; ?>">
						<input type="submit" class="button" value="<?php _e( 'Search', 'media-cleaner' ) ?>"><span style='border-right: #A2A2A2 solid 1px; margin-left: 5px; margin-right: 3px;'>&nbsp;</span>
					</p>
				</form>

				<!-- PROGRESS -->
				<span style='margin-left: 12px; font-size: 15px; top: 5px; position: relative; color: #747474;' id='wpmc_progression'></span>

			</div>

			<p>
				<?php
					$method = get_option( 'wpmc_method', 'media' );
					if ( !$this->admin->is_registered() )
						$method = 'media';

					$hide_warning = get_option( 'wpmc_hide_warning', false );

					if ( !$hide_warning ) {
						_e( "<div class='notice notice-error'><p><b style='color: red;'>Important.</b> <b>Backup your DB and your /uploads directory before using Media Cleaner. </b> The deleted files will be temporarily moved to the <b>uploads/wpmc-trash</b> directory. After testing your website, you can check the <a href='?page=media-cleaner&s&view=deleted'>trash</a> to either empty it or recover the media and files. The Media Cleaner does its best to be safe to use. However, WordPress being a very dynamic and pluggable system, it is impossible to predict all the situations in which your files are used. <b style='color: red;'>Again, please backup!</b> <br /><br /><b style='color: red;'>Be thoughtful.</b> Don't blame Media Cleaner if it deleted too many or not enough of your files. It makes cleaning possible and this task is only possible this way; don't post a bad review because it broke your install. <b>If you have a proper backup, there is no risk</b>. Sorry for the lengthy message, but better be safe than sorry. You can disable this big warning in the options if you have a Pro license. Make sure you read this warning twice. Media Cleaner is awesome and always getting better so I hope you will enjoy it. Thank you :)</p></div>", 'media-cleaner' );
					}

					if ( !MEDIA_TRASH ) {
						_e( "<div class='notice notice-warning'><p>The trash for the Media Library is disabled. Any media removed by the plugin will be <b>permanently deleted</b>. To enable it, modify your wp-config.php file and add this line:<br /><b>define( 'MEDIA_TRASH', true );</b></p></div>" );
					}

					if ( !$this->admin->is_registered() ) {
						echo "<div class='notice notice-info'><p>";
						_e( "<b>This version is not Pro.</b> This plugin is a lot of work so please consider in getting the Pro version in order to receive support and to help the plugin to evolve. Also, the Pro version will also give you the option <b>to scan the <u>physical files</u> in your /uploads folder</b>. You can <a target='_blank' href='http://meowapps.com/media-cleaner'>get a serial for the Pro version here</a></b>.", 'media-cleaner' );
						echo "</p></div>";
					}

					$anychecks = get_option( 'wpmc_posts', true ) || get_option(' wpmc_galleries', false ) ||
						get_option(' wpmc_postmeta', true );
					$check_library = get_option(' wpmc_media_library', false );

					if ( $method == 'media' ) {
						if ( !$anychecks )
							_e( "<div class='error'><p>Media Cleaner will analyze your Media Library. There is however <b>nothing marked to be check</b> in the Settings.</p></div>", 'media-cleaner' );
						else
							_e( "<div class='notice notice-success'><p>Media Cleaner will analyze your Media Library.</p></div>", 'media-cleaner' );
					}
					else if ( $method == 'files' ) {
						if ( !$anychecks && !$check_library )
							_e( "<div class='error'><p>Media Cleaner will analyze your Filesystem. There is however <b>nothing marked to be check</b> in the Settings.</p></div>", 'media-cleaner' );
						else
							_e( "<div class='notice notice-success'><p>Media Cleaner will analyze your Filesystem.</p></div>", 'media-cleaner' );
					}

					echo sprintf( __( 'There are <b>%s issue(s)</b> with your files, accounting for <b>%s MB</b>. Your trash contains <b>%s MB.</b>', 'media-cleaner' ), number_format( $issues_count, 0 ), number_format( $total_size / 1000000, 2 ), number_format( $trash_total_size / 1000000, 2 ) );
				?>
			</p>

			<div id='wpmc-pages'>
			<?php
			echo paginate_links(array(
				'base' => '?page=media-cleaner&s=' . urlencode($s) . '&view=' . $view . '%_%',
				'current' => $paged,
				'format' => '&paged=%#%',
				'total' => ceil( $items_count / $posts_per_page ),
				'prev_next' => false
			));
			?>
			</div>

			<ul class="subsubsub">
				<li class="all"><a <?php if ( $view == 'issues' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=issues'><?php _e( "Issues", 'media-cleaner' ); ?></a><span class="count">(<?php echo $issues_count; ?>)</span></li> |
				<li class="all"><a <?php if ( $view == 'ignored' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=ignored'><?php _e( "Ignored", 'media-cleaner' ); ?></a><span class="count">(<?php echo $ignored_count; ?>)</span></li> |
				<li class="all"><a <?php if ( $view == 'deleted' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=deleted'><?php _e( "Trash", 'media-cleaner' ); ?></a><span class="count">(<?php echo $deleted_count; ?>)</span></li>
			</ul>

			<table id='wpmc-table' class='wp-list-table widefat fixed media'>

				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column"><input id="wpmc-cb-select-all" type="checkbox"></th>
						<?php if ( !get_option( 'wpmc_hide_thumbnails', false ) ): ?>
						<th style='width: 64px;'><?php _e( 'Thumb', 'media-cleaner' ) ?></th>
						<?php endif; ?>
						<th style='width: 50px;'><?php _e( 'Type', 'media-cleaner' ) ?></th>
						<th style='width: 80px;'><?php _e( 'Origin', 'media-cleaner' ) ?></th>

						<?php if ( !empty( $wplr ) ):  ?>
							<th style='width: 70px;'><?php _e( 'LR ID', 'media-cleaner' ) ?></th>
						<?php endif; ?>

						<th><?php _e( 'Path', 'media-cleaner' ) ?></th>
						<th style='width: 220px;'><?php _e( 'Issue', 'media-cleaner' ) ?></th>
						<th style='width: 80px; text-align: right;'><?php _e( 'Size', 'media-cleaner' ) ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
						foreach ( $items as $issue ) {
							$regex = "^(.*)(\\s\\(\\+.*)$";
							$issue->path = preg_replace( '/' .$regex . '/i', '$1', $issue->path );
					?>
					<tr>
						<td><input type="checkbox" name="id" value="<?php echo $issue->id ?>"></td>
						<?php if ( !get_option( 'wpmc_hide_thumbnails', false ) ): ?>
						<td>
							<?php
								if ( $issue->deleted == 0 ) {
									if ( $issue	->type == 0 ) {
										// FILE
										$upload_dir = wp_upload_dir();
										$url = htmlspecialchars( $upload_dir['baseurl'] . '/' . $issue->path, ENT_QUOTES );
										echo "<a target='_blank' href='" . $url .
											"'><img style='max-width: 48px; max-height: 48px;' src='" . $url . "' /></a>";
									}
									else {
										// MEDIA
										$attachmentsrc = wp_get_attachment_image_src( $issue->postId, 'thumbnail' );
										$attachmentsrc_clean = htmlspecialchars( $attachmentsrc[0], ENT_QUOTES );
										echo "<a target='_blank' href='" . $attachmentsrc_clean .
											"'><img style='max-width: 48px; max-height: 48px;' src='" .
											$attachmentsrc_clean . "' />";
									}
								}
								if ( $issue->deleted == 1 ) {
									$upload_dir = wp_upload_dir();
									$url = htmlspecialchars( $upload_dir['baseurl'] . '/wpmc-trash/' . $issue->path, ENT_QUOTES );
									echo "<a target='_blank' href='" . $url .
										"'><img style='max-width: 48px; max-height: 48px;' src='" . $url . "' /></a>";
								}
							?>
						</td>
						<?php endif; ?>
						<td><?php echo $issue->type == 0 ? 'FILE' : 'MEDIA'; ?></td>
						<td><?php echo $issue->type == 0 ? 'Filesystem' : ("<a href='post.php?post=" .
							$issue->postId . "&action=edit'>ID " . $issue->postId . "</a>"); ?></td>
						<?php if ( !empty( $wplr ) ) { $info = $wplr->get_sync_info( $issue->postId ); ?>
							<td style='width: 70px;'><?php echo ( !empty( $info ) && $info->lr_id ? $info->lr_id : "" ); ?></td>
						<?php } ?>
						<td><?php echo stripslashes( $issue->path ); ?></td>
						<td><?php $this->echo_issue( $issue->issue ); ?></td>
						<td style='text-align: right;'><?php echo number_format( $issue->size / 1000, 2 ); ?> KB</td>
					</tr>
					<?php } ?>
				</tbody>

				<tfoot>
					<tr><th></th>
					<?php if ( !get_option( 'hide_thumbnails', false ) ): ?>
					<th></th>
					<?php endif; ?>
					<th><?php _e( 'Type', 'media-cleaner' ) ?></th><th><?php _e( 'Origin', 'media-cleaner' ) ?></th>
					<?php if ( !empty( $wplr ) ):  ?>
						<th style='width: 70px;'><?php _e( 'LR ID', 'media-cleaner' ) ?></th>
					<?php endif; ?>
					<th><?php _e( 'Path', 'media-cleaner' ) ?></th><th><?php _e( 'Issue', 'media-cleaner' ) ?></th><th style='width: 80px; text-align: right;'><?php _e( 'Size', 'media-cleaner' ) ?></th></tr>
				</tfoot>

			</table>
		</wrap>

		<?php
	}

	function admin_menu() {
		//load_plugin_textdomain( 'media-cleaner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_media_page( 'Media Cleaner', 'Cleaner', 'manage_options', 'media-cleaner', array( $this, 'wpmc_screen' ) );
	}

	function wp_enqueue_scripts () {
		wp_enqueue_style( 'media-cleaner-css', plugins_url( '/media-cleaner.css', __FILE__ ) );
		wp_enqueue_script( 'media-cleaner', plugins_url( '/media-cleaner.js', __FILE__ ), array( 'jquery' ), "3.7.0", true );
	}
}


/*
	INSTALL / UNINSTALL
*/

register_activation_hook( __FILE__, 'wpmc_activate' );
register_deactivation_hook( __FILE__, 'wpmc_uninstall' );
register_uninstall_hook( __FILE__, 'wpmc_uninstall' );

function wpmc_reset () {
	wpmc_uninstall();
	wpmc_activate();
}

function wpmc_activate () {
	global $wpdb;
	$table_name = $wpdb->prefix . "wpmcleaner";
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
		type TINYINT(1) NOT NULL,
		postId BIGINT(20) NULL,
		path TINYTEXT NULL,
		size INT(9) NULL,
		ignored TINYINT(1) NOT NULL DEFAULT 0,
		deleted TINYINT(1) NOT NULL DEFAULT 0,
		issue TINYTEXT NOT NULL,
		UNIQUE KEY id (id)
	) " . $charset_collate . ";";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $sql );

	$upload_folder = wp_upload_dir();
	$basedir = $upload_folder['basedir'];
	if ( !is_writable( $basedir ) ) {
		echo '<div class="error"><p>' . __( 'The directory for uploads is not writable. Media Cleaner will only be able to scan.', 'media-cleaner' ) . '</p></div>';
	}
}

function wpmc_uninstall () {
	global $wpdb;
	$table_name = $wpdb->prefix . "wpmcleaner";
	$wpdb->query("DROP TABLE IF EXISTS $table_name");
}
