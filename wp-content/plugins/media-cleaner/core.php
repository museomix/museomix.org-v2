<?php

class Meow_WPMC_Core {

	public $admin = null;
	public $last_analysis = null; //TODO: Is it actually used?
	public $engine = null;
	private $regex_file = '/[A-Za-z0-9-_,.\(\)\s]+[.]{1}(MIMETYPES)/';
	public $current_method = 'media';
	private $refcache = array();
	public $servername = null;
	public $upload_folder = null;
	public $contentDir = null; // becomes 'wp-content/uploads'
	private $check_postmeta = null;
	private $check_posts = null;
	private $check_widgets = null;
	private $debug_logs = null;
	public $site_url = null;

	public function __construct( $admin ) {
		$this->admin = $admin;
		$this->site_url = get_site_url();
		$this->current_method = get_option( 'wpmc_method', 'media' );
		$types = "jpg|jpeg|jpe|gif|png|tiff|bmp|csv|pdf|xls|xlsx|doc|docx|tiff|mp3|mp4|wav|lua";
		$this->regex_file = str_replace( "MIMETYPES", $types, $this->regex_file );
		$this->servername = str_replace( 'http://', '', str_replace( 'https://', '', $this->site_url ) );
		$this->upload_folder = wp_upload_dir();
		$this->contentDir = substr( $this->upload_folder['baseurl'], 1 + strlen( $this->site_url ) );
		$this->check_postmeta = get_option( 'wpmc_postmeta', false );
		$this->check_posts = get_option( 'wpmc_posts', false );
		$this->check_widgets = get_option( 'wpmc_widgets', false );
		$this->debug_logs = get_option( 'wpmc_debuglogs', false );
		add_action( 'wpmc_initialize_parsers', array( $this, 'initialize_parsers' ), 10, 0 );

		require __DIR__ . '/engine.php';
		require __DIR__ . '/ui.php';
		require __DIR__ . '/api.php';
		$this->engine = new Meow_WPMC_Engine( $this, $admin );
		new Meow_WPMC_UI( $this, $admin );
		new Meow_WPMC_API( $this, $admin, $this->engine );
	}

	function initialize_parsers() {
		include_once( 'parsers.php' );
		new MeowApps_WPMC_Parsers();
	}

	function deepsleep( $seconds ) {
		$start_time = time();
		while( true ) {
			if ( ( time() - $start_time ) > $seconds ) {
				return false;
			}
			get_post( array( 'posts_per_page' => 50 ) );
		}
	}

	private $start_time;
	private $time_elapsed = 0;
	private $item_scan_avg_time = 0;
	private $wordpress_init_time = 0.5;
	private $max_execution_time;
	private $items_checked = 0;
	private $items_count = 0;

	function get_max_execution_time() {
		if ( isset( $this->max_execution_time ) )
			return $this->max_execution_time;

		$this->max_execution_time = ini_get( "max_execution_time" );
		if ( empty( $this->max_execution_time ) || $this->max_execution_time < 5 )
			$this->max_execution_time = 30;

		return $this->max_execution_time;
	}

	function timeout_check_start( $count ) {
		$this->start_time = time();
		$this->items_count = $count;
		$this->get_max_execution_time();
	}

	function timeout_check() {
		$this->time_elapsed = time() - $this->start_time;
		$this->time_remaining = $this->max_execution_time - $this->wordpress_init_time - $this->time_elapsed;
		if ( $this->time_remaining - $this->item_scan_avg_time < 0 ) {
			error_log("Media Cleaner Timeout! Check the Media Cleaner logs for more info.");
			$this->log( "Timeout! Some info for debug:" );
			$this->log( "Elapsed time: $this->time_elapsed" );
			$this->log( "WP init time: $this->wordpress_init_time" );
			$this->log( "Remaining time: $this->time_remaining" );
			$this->log( "Scan time per item: $this->item_scan_avg_time" );
			$this->log( "PHP max_execution_time: $this->max_execution_time" );
			header("HTTP/1.0 408 Request Timeout");
			exit;
		}
	}

	function timeout_check_additem() {
		$this->items_checked++;
		$this->time_elapsed = time() - $this->start_time;
		$this->item_scan_avg_time = ceil( ( $this->time_elapsed / $this->items_checked ) * 10 ) / 10;
	}

	function array_to_ids_or_urls( &$meta, &$ids, &$urls ) {
		foreach ( $meta as $k => $m ) {
			if ( is_numeric( $m ) ) {
				// Probably a Media ID
				if ( $m > 0 )
					array_push( $ids, $m );
			}
			else if ( is_array( $m ) ) {
				// If it's an array with a width, probably that the index is the Media ID
				if ( isset( $m['width'] ) && is_numeric( $k ) ) {
					if ( $k > 0 )
						array_push( $ids, $k );
				}
			}
			else if ( !empty( $m ) ) {
				// If it's a string, maybe it's a file (with an extension)
				if ( preg_match( $this->regex_file, $m ) )
					array_push( $urls, $m );
			}
		}
	}

	function get_favicon() {
		// Yoast SEO plugin
		$vals = get_option( 'wpseo_titles' );
		if ( !empty( $vals ) ) {
			$url = $vals['company_logo'];
			if ( $this->is_url( $url ) )
				return $this->clean_url( $url );
		}
	}

	function get_urls_from_html( $html ) {
		if ( empty( $html ) )
			return array();

		// Resolve src-set and shortcodes
		$html = do_shortcode( $html );
		$html = wp_make_content_images_responsive( $html );

		// Create the DOM Document
		$dom = new DOMDocument();
		@$dom->loadHTML( $html );
		$results = array();

		// <meta> tags in <head> area
		$metas = $dom->getElementsByTagName( 'meta' );
		foreach ( $metas as $meta ) {
			$property = $meta->getAttribute( 'property' );
			if ( $property == 'og:image' || $property == 'og:image:secure_url' || $property == 'twitter:image' ) {
				$url = $meta->getAttribute( 'content' );
				if ( $this->is_url( $url ) ) {
					$src = $this->clean_url( $url );
					if ( !empty( $src ) ) {
						array_push( $results, $src );
					}
				}
			}
		}

		// IFrames (by Mike Meinz)
		$iframes = $dom->getElementsByTagName( 'iframe' );
		foreach($iframes as $iframe) {
			$iframe_src = $iframe->getAttribute( 'src' );
			// Ignore if the iframe src is not on this server
			if ( ( strpos( $iframe_src, $this->servername ) !== false) || ( substr( $iframe_src, 0, 1 ) == "/" ) ) {
				// Create a new DOM Document to hold iframe
				$iframe_doc = new DOMDocument();
				// Load the url's contents into the DOM
				libxml_use_internal_errors( true ); // ignore html formatting problems
				$rslt = $iframe_doc->loadHTMLFile( $iframe_src );
				libxml_clear_errors();
				libxml_use_internal_errors( false );
				if ( $rslt ) {
					// Get the resulting html
					$iframe_html = $iframe_doc->saveHTML();
					if ( $iframe_html !== false ) {
						// Scan for links in the iframe
						$iframe_urls = $this->get_urls_from_html( $iframe_html ); // Recursion
						if ( !empty( $iframe_urls ) ) {
							$results = array_merge( $results, $iframe_urls );
						}
					}
				}
				else {
					$err = 'ERROR: Failed to load iframe: ' . $iframe_src;
					error_log( $err );
					$this->core->log( $err );
				}
			}
		}


		// Images, src, srcset
		$imgs = $dom->getElementsByTagName( 'img' );
		foreach ( $imgs as $img ) {
			$src = $this->clean_url( $img->getAttribute('src') );
    			array_push( $results, $src );
			$srcset = $img->getAttribute('srcset');
			if ( !empty( $srcset ) ) {
				$setImgs = explode( ',', trim( $srcset ) );
				foreach ( $setImgs as $setImg ) {
					$finalSetImg = explode( ' ', trim( $setImg ) );
					if ( is_array( $finalSetImg ) ) {
						array_push( $results, $this->clean_url( $finalSetImg[0] ) );
					}
				}
			}
		}

		// Links, href
		$urls = $dom->getElementsByTagName( 'a' );
		foreach ( $urls as $url ) {
			$url_href = $url->getAttribute('href'); // mm change
			if ( $this->is_url( $url_href ) ) { // mm change
				$src = $this->clean_url( $url_href );  // mm change
				if ( !empty( $src ) )
					array_push( $results, $src );
			}
		}

		// <link> tags in <head> area
		$urls = $dom->getElementsByTagName( 'link' );
		foreach ( $urls as $url ) {
			$url_href = $url->getAttribute( 'href' );
			if ( $this->is_url( $url_href ) ) {
				$src = $this->clean_url( $url_href );
				if ( !empty( $src ) ) {
					array_push( $results, $src );
				}
			}
		}

		// PDF
		preg_match_all( "/((https?:\/\/)?[^\\&\#\[\] \"\?]+\.pdf)/", $html, $res );
		if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
			foreach ( $res[1] as $url ) {
				if ( $this->is_url($url) )
					array_push( $results, $this->clean_url( $url ) );
			}
		}

		// Background images
		preg_match_all( "/url\(\'?\"?((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png))\'?\"?/", $html, $res );
		if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
			foreach ( $res[1] as $url ) {
				if ( $this->is_url($url) )
					array_push( $results, $this->clean_url( $url ) );
			}
		}

		return $results;
	}

	// Parse a meta, visit all the arrays, look for the attributes, fill $ids and $urls arrays
	function get_from_meta( $meta, $lookFor, &$ids, &$urls ) {
		foreach ( $meta as $key => $value ) {
			if ( is_object( $value ) || is_array( $value ) )
				$this->get_from_meta( $value, $lookFor, $ids, $urls );
			else if ( in_array( $key, $lookFor ) ) {
				if ( empty( $value ) )
					continue;
				else if ( is_numeric( $value ) )
					array_push( $ids, $value );
				else
					if ( $this->is_url( $value ) )
						array_push( $urls, $this->clean_url( $value ) );
			}
		}
	}

	function get_images_from_themes( &$ids, &$urls ) {
		// USE CURRENT THEME AND WP API
		$ch = get_custom_header();
		if ( !empty( $ch ) && !empty( $ch->url ) ) {
			array_push( $urls, $this->clean_url( $ch->url ) );
		}
		if ( $this->is_url( $ch->thumbnail_url ) ) {
			array_push( $urls, $this->clean_url( $ch->thumbnail_url ) );
		}
		if ( !empty( $ch ) && !empty( $ch->attachment_id ) ) {
			array_push( $ids, $ch->attachment_id );
		}
		$cl = get_custom_logo();
		if ( $this->is_url( $cl ) ) {
			$urls = array_merge( $this->get_urls_from_html( $cl ), $urls );
		}
		$cd = get_background_image();
		if ( $this->is_url( $cd ) ) {
			array_push( $urls, $this->clean_url( $cd ) );
		}
		$photography_hero_image = get_theme_mod( 'photography_hero_image' );
		if ( !empty( $photography_hero_image ) ) {
			array_push( $ids, $photography_hero_image );
		}
		$author_profile_picture = get_theme_mod( 'author_profile_picture' );
		if ( !empty( $author_profile_picture ) ) {
			array_push( $ids, $author_profile_picture );
		}
		if ( function_exists ( 'get_uploaded_header_images' ) ) {
			$header_images = get_uploaded_header_images();
			if ( !empty( $header_images ) ) {
				foreach( $header_images as $hi ) {
					if ( !empty ( $hi['attachment_id'] ) ) {
						array_push( $ids, $hi['attachment_id'] );
					}
				}
			}
		}
	}

	function log( $data = null, $force = false ) {
		if ( !$this->debug_logs && !$force )
			return;
		$fh = @fopen( trailingslashit( dirname(__FILE__) ) . '/media-cleaner.log', 'a' );
		if ( !$fh )
			return false;
		$date = date( "Y-m-d H:i:s" );
		if ( is_null( $data ) )
			fwrite( $fh, "\n" );
		else
			fwrite( $fh, "$date: {$data}\n" );
		fclose( $fh );
		return true;
	}

	/**
	 *
	 * HELPERS
	 *
	 */

	function get_trashdir() {
		return trailingslashit( $this->upload_folder['basedir'] ) . 'wpmc-trash';
	}

	/**
	 *
	 * DELETE / SCANNING / RESET
	 *
	 */

	function recover_file( $path ) {
		$originalPath = trailingslashit( $this->upload_folder['basedir'] ) . $path;
		$trashPath = trailingslashit( $this->get_trashdir() ) . $path;
		$path_parts = pathinfo( $originalPath );
		if ( !file_exists( $path_parts['dirname'] ) && !wp_mkdir_p( $path_parts['dirname'] ) ) {
			die( 'Failed to create folder.' );
		}
		if ( !file_exists( $trashPath ) ) {
			$this->log( "The file $originalPath actually does not exist in the trash." );
			return true;
		}
		if ( !rename( $trashPath, $originalPath ) ) {
			die( 'Failed to move the file.' );
		}
		return true;
	}

	function recover( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$issue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), OBJECT );
		$issue->path = stripslashes( $issue->path );

		// Files
		if ( $issue->type == 0 ) {
			$this->recover_file( $issue->path );
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 0 WHERE id = %d", $id ) );
			return true;
		}
		// Media
		else if ( $issue->type == 1 ) {

			// Copy the main file back
			$fullpath = get_attached_file( $issue->postId );
			if ( empty( $fullpath ) ) {
				error_log( "Media {$issue->postId} does not have attached file anymore." );
				return false;
			}
			$mainfile = $this->clean_uploaded_filename( $fullpath );
			$baseUp = pathinfo( $mainfile );
			$baseUp = $baseUp['dirname'];
			$file = $this->clean_uploaded_filename( $fullpath );
			if ( !$this->recover_file( $file ) ) {
				$this->log( "Could not recover $file." );
				error_log( "Media Cleaner: Could not recover $file." );
			}

			// If images, copy the other files as well
			$meta = wp_get_attachment_metadata( $issue->postId );
			$isImage = isset( $meta, $meta['width'], $meta['height'] );
			$sizes = $this->get_image_sizes();
			if ( $isImage && isset( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $name => $attr ) {
					if  ( isset( $attr['file'] ) ) {
						$filepath = $this->upload_folder['basedir'];
						$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
						$file = $this->clean_uploaded_filename( $filepath );
						if ( !$this->recover_file( $file ) ) {
							$this->log( "Could not recover $file." );
							error_log( "Media Cleaner: Could not recover $file." );
						}
					}
				}
			}
			if ( !wp_untrash_post( $issue->postId ) ) {
				error_log( "Cleaner: Failed to Untrash Post {$issue->postId} (but deleted it from Cleaner DB)." );
			}
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 0 WHERE id = %d", $id ) );
			return true;
		}
	}

	function trash_file( $fileIssuePath ) {
		global $wpdb;
		$originalPath = trailingslashit( $this->upload_folder['basedir'] ) . $fileIssuePath;
		$trashPath = trailingslashit( $this->get_trashdir() ) . $fileIssuePath;
		$path_parts = pathinfo( $trashPath );

		try {
			if ( !file_exists( $path_parts['dirname'] ) && !wp_mkdir_p( $path_parts['dirname'] ) ) {
				$this->log( "Could not create the trash directory for Media Cleaner." );
				error_log( "Media Cleaner: Could not create the trash directory." );
				return false;
			}
			// Rename the file (move). 'is_dir' is just there for security (no way we should move a whole directory)
			if ( is_dir( $originalPath ) ) {
				$this->log( "Attempted to delete a directory instead of a file ($originalPath). Can't do that." );
				error_log( "Media Cleaner: Attempted to delete a directory instead of a file ($originalPath). Can't do that." );
				return false;
			}
			if ( !file_exists( $originalPath ) ) {
				$this->log( "The file $originalPath actually does not exist." );
				return true;
			}
			if ( !@rename( $originalPath, $trashPath ) ) {
				return false;
			}
		}
		catch ( Exception $e ) {
			return false;
		}
		$this->clean_dir( dirname( $originalPath ) );
		return true;
	}

	function ignore( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$issue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), OBJECT );
		if ( (int) $issue->ignored )
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET ignored = 0 WHERE id = %d", $id ) );
		else {
			if ( (int) $issue->deleted ) // If it is in trash, recover it
				$this->recover( $id );
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET ignored = 1 WHERE id = %d", $id ) );
		}
		return true;
	}

	function endsWith( $haystack, $needle )
	{
	  $length = strlen( $needle );
	  if ( $length == 0 )
	    return true;
	  return ( substr( $haystack, -$length ) === $needle );
	}

	function clean_dir( $dir ) {
		if ( !file_exists( $dir ) )
			return;
		else if ( $this->endsWith( $dir, 'uploads' ) )
			return;
		$found = array_diff( scandir( $dir ), array( '.', '..' ) );
		if ( count( $found ) < 1 ) {
			if ( rmdir( $dir ) ) {
				$this->clean_dir( dirname( $dir ) );
			}
		}
	}

	function delete( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$issue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), OBJECT );
		$regex = "^(.*)(\\s\\(\\+.*)$";
		$issue->path = preg_replace( '/' . $regex . '/i', '$1', $issue->path ); // remove " (+ 6 files)" from path

		// Make sure there isn't a media DB entry
		if ( $issue->type == 0 ) {
			$attachmentid = $this->find_media_id_from_file( $issue->path, true );
			if ( $attachmentid ) {
				$this->log( "Issue listed as filesystem but Media {$attachmentid} exists." );
			}
		}

		if ( $issue->type == 0 ) {

			if ( $issue->deleted == 0 ) {
				// Move file to the trash
				if ( $this->trash_file( $issue->path ) )
					$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 1, ignored = 0 WHERE id = %d", $id ) );
				return true;
			}
			else {
				// Delete file from the trash
				$trashPath = trailingslashit( $this->get_trashdir() ) . $issue->path;
				if ( !unlink( $trashPath ) ) {
					$this->log( "Failed to delete the file." );
					error_log( "Media Cleaner: Failed to delete the file." );
				}
				$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id ) );
				$this->clean_dir( dirname( $trashPath ) );
				return true;
			}
		}

		if ( $issue->type == 1 ) {
			if ( $issue->deleted == 0 && MEDIA_TRASH ) {
				// Move Media to trash
				// Let's copy the images to the trash so that it can be recovered.
				$fullpath = get_attached_file( $issue->postId );
				$mainfile = $this->clean_uploaded_filename( $fullpath );
				$baseUp = pathinfo( $mainfile );
				$baseUp = $baseUp['dirname'];
				$file = $this->clean_uploaded_filename( $fullpath );
				if ( !$this->trash_file( $file ) ) {
					$this->log( "Could not trash $file." );
					error_log( "Media Cleaner: Could not trash $file." );
					return false;
				}

				// If images, check the other files as well
				$meta = wp_get_attachment_metadata( $issue->postId );
				$isImage = isset( $meta, $meta['width'], $meta['height'] );
				$sizes = $this->get_image_sizes();
				if ( $isImage && isset( $meta['sizes'] ) ) {
					foreach ( $meta['sizes'] as $name => $attr ) {
						if  ( isset( $attr['file'] ) ) {
							$filepath = $this->upload_folder['basedir'];
							$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
							$file = $this->clean_uploaded_filename( $filepath );
							if ( !$this->trash_file( $file ) ) {
								$this->log( "Could not trash $file." );
								error_log( "Media Cleaner: Could not trash $file." );
							}
						}
					}
				}
				wp_delete_attachment( $issue->postId, false );
				$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET deleted = 1, ignored = 0 WHERE id = %d", $id ) );
				return true;
			}
			else {
				// Trash Media definitely by recovering it (to be like a normal Media) and remove it through the
				// standard WordPress workflow
				if ( MEDIA_TRASH )
					$this->recover( $id );
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

	function add_reference_url( $urlOrUrls, $type, $origin = null, $extra = null ) {
		$urlOrUrls = !is_array( $urlOrUrls ) ? array( $urlOrUrls ) : $urlOrUrls;
		foreach ( $urlOrUrls as $url ) {
			// With files, we need both filename without resolution and filename with resolution, it's important
			// to make sure the original file is not deleted if a size exists for it.
			// With media, all URLs should be without resolution to make sure it matches Media.
			if ( $this->current_method == 'files' )
				$this->add_reference( null, $url, $type, $origin );
			$this->add_reference( 0, $this->clean_url_from_resolution( $url ), $type, $origin );
		}
	}

	function add_reference_id( $idOrIds, $type, $origin = null, $extra = null ) {
		$idOrIds = !is_array( $idOrIds ) ? array( $idOrIds ) : $idOrIds;
		foreach ( $idOrIds as $id )
			$this->add_reference( $id, "", $type, $origin );
	}

	private $cached_ids = array();
	private $cached_urls = array();

	private function add_reference( $id, $url, $type, $origin = null, $extra = null ) {
		// The references are actually not being added directly in the DB, they are being pushed
		// into a cache ($this->refcache).
		if ( !empty( $id ) ) {
			if ( !in_array( $id, $this->cached_ids ) )
				array_push( $this->cached_ids, $id );
			else
				return;
		}
		if ( !empty( $url ) ) {
			// The URL shouldn't contain http, https, javascript at the beginning (and there are probably many more cases)
			// The URL must be cleaned before being passed as a reference.
			if ( substr( $url, 0, 5 ) === "http:" )
				return;
			if ( substr( $url, 0, 6 ) === "https:" )
				return;
			if ( substr( $url, 0, 11 ) === "javascript:" )
				return;
			if ( !in_array( $url, $this->cached_urls ) )
				array_push( $this->cached_urls, $url );
			else
				return;
		}
		//
		array_push( $this->refcache, array( 'id' => $id, 'url' => $url, 'type' => $type, 'origin' => $origin ) );

		// Without cache, the code would be this.
		// $wpdb->insert( $table_name,
		//	array(
		//		'time' => current_time('mysql'), 'mediaId' => $id, 'mediaUrl' => $url, 'origin' => $origin, 'originType' => $type )
		//	);
	}

	// The cache containing the references is wrote to the DB.
	function write_references() {
		global $wpdb;
		$table = $wpdb->prefix . "mclean_refs";
		$values = array();
		$place_holders = array();
		$query = "INSERT INTO $table (mediaId, mediaUrl, originType) VALUES ";
		foreach( $this->refcache as $key => $value ) {
			array_push( $values, $value['id'], $value['url'], $value['type'] );
			if ( $this->debug_logs ) {
				if ( !empty( $value['id'] ) )
					$this->log( "* {$value['type']}: Media #{$value['id']}" );
				if ( !empty( $value['url'] ) )
					$this->log( "* {$value['type']}: {$value['url']}" );
			}
			$place_holders[] = "('%d','%s','%s')";
		}
		if ( !empty( $values ) ) {
			$query .= implode( ', ', $place_holders );
			$prepared = $wpdb->prepare( "$query ", $values );
			$wpdb->query( $prepared );
		}
		$this->refcache = array();
	}

	function check_is_ignore( $file ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$count = $wpdb->get_var( "SELECT COUNT(*)
			FROM $table_name
			WHERE ignored = 1
			AND path LIKE '%".  esc_sql( $wpdb->esc_like( $file ) ) . "%'" );
		if ( $count > 0 ) {
			$this->log( "Could not trash $file." );
		}
		return ($count > 0);
	}

	function find_media_id_from_file( $file, $doLog ) {
		global $wpdb;
		$postmeta_table_name = $wpdb->prefix . 'postmeta';
		$file = $this->clean_uploaded_filename( $file );
		$sql = $wpdb->prepare( "SELECT post_id
			FROM {$postmeta_table_name}
			WHERE meta_key = '_wp_attached_file'
			AND meta_value = %s", $file
		);
		$ret = $wpdb->get_var( $sql );
		if ( $doLog ) {
			if ( empty( $ret ) )
				$this->log( "File $file not found as _wp_attached_file (Library)." );
			else {
				$this->log( "File $file found as Media $ret." );
			}
		}

		return $ret;
	}

	function get_image_sizes() {
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

	function clean_url_from_resolution( $url ) {
		$pattern = '/[_-]\d+x\d+(?=\.[a-z]{3,4}$)/';
		$url = preg_replace( $pattern, '', $url );
		return $url;
	}

	function is_url( $url ) {
		return ( (
			!empty( $url ) ) &&
			is_string( $url ) &&
			strlen( $url ) > 4 && (
				strtolower( substr( $url, 0, 4) ) == 'http' || $url[0] == '/'
			)
		);
	}

	function clean_url_from_resolution_ref( &$url ) {
		$url = $this->clean_url_from_resolution( $url );
	}

	// From a url to the shortened and cleaned url (for example '2013/02/file.png')
	function clean_url( $url ) {
		$dirIndex = strpos( $url, $this->contentDir );
		if ( empty( $url ) || $dirIndex == false )
			return null;
		return urldecode( substr( $url, 1 + strlen( $this->contentDir ) + $dirIndex ) );
	}

	// From a fullpath to the shortened and cleaned path (for example '2013/02/file.png')
	// Original version by Jordy
	// function clean_uploaded_filename( $fullpath ) {
	// 	$basedir = $this->upload_folder['basedir'];
	// 	$file = str_replace( $basedir, '', $fullpath );
	// 	$file = str_replace( "./", "", $file );
	// 	$file = trim( $file,  "/" );
	// 	return $file;
	// }

	// From a fullpath to the shortened and cleaned path (for example '2013/02/file.png')
	// Faster version, more difficult to read, by Mike Meinz
	function clean_uploaded_filename( $fullpath ) {
		$dirIndex = strpos( $fullpath, $this->contentDir );
		if ( $dirIndex == false ) {
			$file = $fullpath;
		}
		else {
		// Remove first part of the path leaving yyyy/mm/filename.ext
			$file = substr( $fullpath, 1 + strlen( $this->contentDir ) + $dirIndex );
		}
		if ( substr( $file, 0, 2 ) == './' ) {
			$file = substr( $file, 2 );
		}
		if ( substr( $file, 0, 1 ) == '/' ) {
			$file = substr( $file, 1 );
		}
		return $file;
	}

	/*
		Check if the file or the Media ID is used in the install.
		That file or ID will be checked against the database of references created by the plugin
		by the parsers.
	*/
	public function reference_exists( $file, $mediaId ) {
		global $wpdb;
		$table = $wpdb->prefix . "mclean_refs";
		$row = null;
		if ( !empty( $mediaId ) ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT originType FROM $table WHERE mediaId = %d", $mediaId ) );
			if ( !empty( $row ) ) {
				$this->last_analysis = $row->originType;
				$this->log( "OK! Media #{$mediaId} used by {$row->originType}" );
				return true;
			}
		}
		if ( !empty( $file ) ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT originType FROM $table WHERE mediaUrl = %s", $file ) );
			if ( !empty( $row ) ) {
				$this->last_analysis = $row->originType;
				$this->log( "OK! File {$file} used by {$row->originType}" );
				return true;
			}
		}
		return false;
	}

	function check_media( $attachmentId, $checkOnly = false ) {

		$this->last_analysis = "N/A";

		// Is it an image?
		$meta = wp_get_attachment_metadata( $attachmentId );
		$isImage = isset( $meta, $meta['width'], $meta['height'] );

		// Get the main file
		global $wpdb;
		$fullpath = get_attached_file( $attachmentId );
		$mainfile = $this->clean_uploaded_filename( $fullpath );
		$baseUp = pathinfo( $mainfile );
		$baseUp = $baseUp['dirname'];
		$size = 0;
		$countfiles = 0;
		$issue = 'NO_CONTENT';
		if ( file_exists( $fullpath ) ) {

			// Special scan: Broken only!
			if ( !$this->check_postmeta && !$this->check_posts && !$this->check_widgets )
				return true;

			$size = filesize( $fullpath );

			// Analysis
			$this->last_analysis = "NONE";
			$this->log( "Checking Media #{$attachmentId}: {$mainfile}" );
			if ( $this->check_is_ignore( $mainfile, $attachmentId ) ) {
				$this->last_analysis = "IGNORED";
				return true;
			}
			if ( $this->reference_exists( $mainfile, $attachmentId ) )
				return true;

			// If images, check the other files as well
			$countfiles = 0;
			$sizes = $this->get_image_sizes();
			if ( $isImage && isset( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $name => $attr ) {
					if  ( isset( $attr['file'] ) ) {
						$filepath = $this->upload_folder['basedir'];
						$filepath = trailingslashit( $filepath ) . trailingslashit( $baseUp ) . $attr['file'];
						if ( file_exists( $filepath ) )
							$size += filesize( $filepath );
						$file = $this->clean_uploaded_filename( $filepath );
						$countfiles++;
						// Analysis
						$this->log( "Checking Media #{$attachmentId}: {$file}" );
						if ( $this->reference_exists( $mainfile, $attachmentId ) )
							return true;
					}
				}
			}
		} else {
			$this->log( "File {$fullpath} does not exist." );
			$issue = 'ORPHAN_MEDIA';
		}

		if ( !$checkOnly ) {
			$table_name = $wpdb->prefix . "mclean_scan";
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
		}
		return false;
	}

	// Delete all issues
	function reset_issues( $includingIgnored = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		if ( $includingIgnored ) {
			$wpdb->query( "DELETE FROM $table_name WHERE deleted = 0" );
		}
		else {
			$wpdb->query( "DELETE FROM $table_name WHERE ignored = 0 AND deleted = 0" );
		}
		if ( file_exists( plugin_dir_path( __FILE__ ) . '/media-cleaner.log' ) ) {
			file_put_contents( plugin_dir_path( __FILE__ ) . '/media-cleaner.log', '' );
		}
		$table_name = $wpdb->prefix . "mclean_refs";
		$wpdb->query("TRUNCATE $table_name");
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
		else if ( $issue == 'ORPHAN_WEBP' ) {
			_e( "Orphan WebP.", 'media-cleaner' );
		}
		else if ( $issue == 'ORPHAN_MEDIA' ) {
			_e( "File not found.", 'media-cleaner' );
		}
		else {
			echo $issue;
		}
	}
}


/*
	INSTALL / UNINSTALL
*/

function wpmc_init( $mainfile ) {
	//register_activation_hook( $mainfile, 'wpmc_reset' );
	//register_deactivation_hook( $mainfile, 'wpmc_uninstall' );
	register_uninstall_hook( $mainfile, 'wpmc_uninstall' );
}

function wpmc_reset () {
	wpmc_uninstall();
	wpmc_install();
	$upload_folder = wp_upload_dir();
	$basedir = $upload_folder['basedir'];
	if ( !is_writable( $basedir ) ) {
		echo '<div class="error"><p>' . __( 'The directory for uploads is not writable. Media Cleaner will only be able to scan.', 'media-cleaner' ) . '</p></div>';
	}

}

function wpmc_install() {
	global $wpdb;
	$table_name = $wpdb->prefix . "mclean_scan";
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
		PRIMARY KEY  (id)
	) " . $charset_collate . ";" ;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $sql );
	$sql="ALTER TABLE $table_name ADD INDEX IgnoredIndex (ignored) USING BTREE;";
	$wpdb->query($sql);
	$table_name = $wpdb->prefix . "mclean_refs";
	$charset_collate = $wpdb->get_charset_collate();
	// This key doesn't work on too many installs because of the 'Specified key was too long' issue
	// KEY mediaLookUp (mediaId, mediaUrl)
	$sql = "CREATE TABLE $table_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		mediaId BIGINT(20) NULL,
		mediaUrl VARBINARY(256) NULL,
		originType VARBINARY(32) NOT NULL,
		PRIMARY KEY  (id)
	) " . $charset_collate . ";";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $sql );
}

function wpmc_uninstall () {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "mclean_scan";
	$table_name2 = $wpdb->prefix . "mclean_refs";
	$table_name3 = $wpdb->prefix . "wpmcleaner";
	$sql = "DROP TABLE IF EXISTS $table_name1, $table_name2, $table_name3;";
	$wpdb->query( $sql );
}
