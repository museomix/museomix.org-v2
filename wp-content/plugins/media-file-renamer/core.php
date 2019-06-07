<?php

class Meow_MFRH_Core {

	private $admin = null;

	public function __construct( $admin ) {
		$this->admin = $admin;
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {
		include( 'api.php' );
		include( 'updates.php' );
		new Meow_MFRH_Updates( $this, $this->admin );
		load_plugin_textdomain( 'media-file-renamer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		$method = apply_filters( 'mfrh_method', 'media_title' );

		if ( $method === 'product_title' && class_exists( 'WooCommerce' ) )
			include( 'plugins/woocommerce.php' );

		// Those actions/filters are only for the admin screens
		if ( is_admin() ) {
			add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 20, 2 );
			add_action( 'save_post', array( $this, 'save_post' ) );

			if ( get_option( 'mfrh_on_upload', false ) ) {
				add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 10, 2 );
			}
		}
	}

	/**
	 *
	 * TOOLS / HELPERS
	 *
	 */

	// Check if the file exists, if it is, return the real path for it
	// https://stackoverflow.com/questions/3964793/php-case-insensitive-version-of-file-exists
	static function sensitive_file_exists( $filename, $fullpath = true, $caseInsensitive = true ) {
		$output = false;
		$directoryName = mfrh_dirname( $filename );
		$fileArray = glob( $directoryName . '/*', GLOB_NOSORT );
		$i = ( $caseInsensitive ) ? "i" : "";

		// Check if \ is in the string
		if ( preg_match( "/\\\|\//", $filename) ) {
			$array = preg_split("/\\\|\//", $filename);
			$filename = $array[count( $array ) -1];
		}
		// Compare filenames
		foreach ( $fileArray as $file ) {
			if ( preg_match( "/" . preg_quote( $filename ) . "$/{$i}", $file ) ) {
				$output = $file;
				break;
			}
    }
		return $output;
	}

	static function rmdir_recursive( $directory ) {
		foreach ( glob( "{$directory}/*" ) as $file ) {
			if ( is_dir( $file ) )
				Meow_MFRH_Core::rmdir_recursive( $file );
			else
				unlink( $file );
		}
		rmdir( $directory );
	}

	function wpml_media_is_installed() {
		return defined( 'WPML_MEDIA_VERSION' );
	}

	// To avoid issue with WPML Media for instance
	function is_real_media( $id ) {
		if ( $this->wpml_media_is_installed() ) {
			global $sitepress;
			$language = $sitepress->get_default_language( $id );
			return icl_object_id( $id, 'attachment', true, $language ) == $id;
		}
		return true;
	}

	function is_header_image( $id ) {
		static $headers = false;
		if ( $headers == false ) {
			global $wpdb;
			$headers = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_is_custom_header'" );
		}
		return in_array( $id, $headers );
	}

	function generate_unique_filename( $actual, $dirname, $filename, $counter = null ) {
		$new_filename = $filename;
		if ( !is_null( $counter ) ) {
			$whereisdot = strrpos( $new_filename, '.' );
			$new_filename = substr( $new_filename, 0, $whereisdot ) . '-' . $counter
				. '.' . substr( $new_filename, $whereisdot + 1 );
		}
		if ( $actual == $new_filename )
			return false;
		if ( file_exists( $dirname . "/" . $new_filename ) )
			return $this->generate_unique_filename( $actual, $dirname, $filename,
				is_null( $counter ) ? 2 : $counter + 1 );
		return $new_filename;
	}

	function get_post_from_media( $id ) {
		global $wpdb;
		$postid = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_parent p
			FROM $wpdb->posts p
			WHERE ID = %d", $id ),
			0, 0 );
		if ( empty( $postid ) )
			return null;
		return get_post( $postid, OBJECT );
	}

	/**
	 * Returns all the media sharing the same file
	 * @param string $file The attached file path
	 * @param int|array $excludes The post ID(s) to exclude from the results
	 * @return array An array of IDs
	 */
	function get_posts_by_attached_file( $file, $excludes = null ) {
		global $wpdb;
		$r = array ();
		$q = <<< SQL
SELECT post_id
FROM {$wpdb->postmeta}
WHERE meta_key = '%s'
AND meta_value = '%s'
SQL;
		$rows = $wpdb->get_results( $wpdb->prepare( $q, '_wp_attached_file', _wp_relative_upload_path( $file ) ), OBJECT );
		if ( $rows && is_array( $rows ) ) {
			if ( !is_array( $excludes ) )
				$excludes = $excludes ? array ( (int) $excludes ) : array ();

			foreach ( $rows as $item ) {
				$id = (int) $item->post_id;
				if ( in_array( $id, $excludes ) ) continue;
				$r[] = $id;
			}
			$r = array_unique( $r );
		}
		return $r;
	}

	/*****************************************************************************
		RENAME ON UPLOAD
	*****************************************************************************/

	function wp_handle_upload_prefilter( $file ) {

		$this->log( "** On Upload: " . $file['name'] );
		$pp = mfrh_pathinfo( $file['name'] );

		// If everything's fine, renames in based on the Title in the EXIF
		$method = apply_filters( 'mfrh_method', 'media_title' );
		switch ( $method ) {
		case 'media_title':
			$exif = wp_read_image_metadata( $file['tmp_name'] );
			if ( !empty( $exif ) && isset( $exif[ 'title' ] ) && !empty( $exif[ 'title' ] ) ) {
				$new_filename = $this->new_filename( null, $exif[ 'title' ] );
				if ( !is_null( $new_filename ) ) {
					$file['name'] = "{$new_filename}.{$pp['extension']}";
					$this->log( "New file should be: " . $file['name'] );
				}
				return $file;
			}
			break;
		case 'post_title':
		case 'product_title':
			if ( !isset( $_POST['post_id'] ) || $_POST['post_id'] < 1 ) break;
			$post = get_post( $_POST['post_id'] );
			if ( !empty( $post ) && !empty( $post->post_title ) ) {
				$new_filename = $this->new_filename( null, $post->post_title );
				if ( !is_null( $new_filename ) ) {
					$file['name'] = "{$new_filename}.{$pp['extension']}";
					$this->log( "New file should be: " . $file['name'] );
				}
				return $file;
			}
			break;
		}
		// Otherwise, let's do the basics based on the filename

		// The name will be modified at this point so let's keep it in a global
		// and re-inject it later
		global $mfrh_title_override;
		$mfrh_title_override = $pp['filename'];
		add_filter( 'wp_read_image_metadata', array( $this, 'wp_read_image_metadata' ), 10, 2 );

		// Modify the filename
		$pp = mfrh_pathinfo( $file['name'] );
		$new_filename = $this->new_filename( null, $pp['basename'] );
		if ( !is_null( $new_filename ) ) $file['name'] = $new_filename;
		return $file;
	}

	function wp_read_image_metadata( $meta, $file ) {
		// Override the title, without this it is using the new filename
		global $mfrh_title_override;
    $meta['title'] = $mfrh_title_override;
    return $meta;
	}

	/****************************************************************************/

	// Return false if everything is fine, otherwise return true with an output.
	function check_attachment( $post, &$output = array(), $manual_filename = null ) {
		$id = $post['ID'];
		$old_filepath = get_attached_file( $id );
		$old_filepath = Meow_MFRH_Core::sensitive_file_exists( $old_filepath );
		$path_parts = mfrh_pathinfo( $old_filepath );

		// If the file doesn't exist, let's not go further.
		if ( !isset( $path_parts['dirname'] ) || !isset( $path_parts['basename'] ) )
			return false;

		//print_r( $path_parts );
		$directory = $path_parts['dirname'];
		$old_filename = $path_parts['basename'];

		// Check if media/file is dead
		if ( !$old_filepath || !file_exists( $old_filepath ) ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Is it forced/manual
		// Check mfrh_new_filename (coming from manual input) if it is different than previous filename
		if ( empty( $manual_filename ) && isset( $post['mfrh_new_filename'] ) ) {
			if ( strtolower( $post['mfrh_new_filename'] ) != strtolower( $old_filename ) )
				$manual_filename =  $post['mfrh_new_filename'];
		}

		if ( !empty( $manual_filename ) ) {
			$new_filename = $manual_filename;
			$output['manual'] = true;
		}
		else {
			$method = apply_filters( 'mfrh_method', 'media_title' );
			if ( $method === 'none') {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}
			if ( get_post_meta( $id, '_manual_file_renaming', true ) ) {
				return false;
			}

			// Skip header images
			if ( $this->is_header_image( $id ) ) {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}

			// Get information
			$base_title = $post['post_title'];
			switch ( $method ) {
			case 'post_title':
			case 'product_title':
				$attachedpost = $this->get_post_from_media( $id );
				if ( is_null( $attachedpost ) )
					return false;
				$base_title = $attachedpost->post_title;
				break;
			case 'alt_text':
				$image_alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
				if ( is_null( $image_alt ) )
					return false;
				$base_title = $image_alt;
				break;
			}
			$new_filename = $this->new_filename( $post, $base_title );
			if ( is_null( $new_filename ) ) return false; // Leave it as it is

			//$this->log( "New title: $base_title, New filename: $new_filename" );
		}

		// If a filename has a counter, and the ideal is without the counter, let's ignore it
		$ideal = preg_replace( '/-[1-9]{1,10}\./', '$1.', $old_filename );
		if ( !$manual_filename ) {
			if ( $ideal == $new_filename ) {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}
		}

		// Filename is equal to sanitized title
		if ( $new_filename == $old_filename ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Check for case issue, numbering
		$new_filepath = trailingslashit( $directory ) . $new_filename;
		$existing_file = Meow_MFRH_Core::sensitive_file_exists( $new_filepath );
		$case_issue = strtolower( $old_filename ) == strtolower( $new_filename );
		if ( $existing_file && !$case_issue ) {
			$is_numbered = apply_filters( 'mfrh_numbered', false );
			if ( $is_numbered ) {
				$new_filename = $this->generate_unique_filename( $ideal, $directory, $new_filename );
				if ( !$new_filename ) {
					delete_post_meta( $id, '_require_file_renaming' );
					return false;
				}
				$new_filepath = trailingslashit( $directory ) . $new_filename;
			}
		}

		// Send info to the requester function
		$output['post_id'] = $id;
		$output['post_name'] = $post['post_name'];
		$output['post_title'] = $post['post_title'];
		$output['current_filename'] = $old_filename;
		$output['current_filepath'] = $old_filepath;
		$output['desired_filename'] = $new_filename;
		$output['desired_filepath'] = $new_filepath;
		$output['case_issue'] = $case_issue;
		$output['manual'] = !empty( $manual_filename );
		$output['locked'] = get_post_meta( $id, '_manual_file_renaming', true );
		$output['desired_filename_exists'] = false;

		// It seems it could be renamed :)
		if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) ) {
			add_post_meta( $post['ID'], '_require_file_renaming', true, true );
		}
		return true;
	}

	function check_text() {
		$issues = array();
		global $wpdb;
		$ids = $wpdb->get_col( "
			SELECT p.ID
			FROM $wpdb->posts p
			WHERE post_status = 'inherit'
			AND post_type = 'attachment'
		" );
		foreach ( $ids as $id )
			if ( $this->check_attachment( get_post( $id, ARRAY_A ), $output ) )
				array_push( $issues, $output );
		return $issues;
	}

	/**
	 *
	 * RENAME ON SAVE / PUBLISH
	 * Originally proposed by Ben Heller
	 * Added and modified by Jordy Meow
	 */

	function save_post( $post_id ) {
		$status = get_post_status( $post_id );
		if ( !in_array( $status, array( 'publish', 'draft', 'future', 'private' ) ) )
			return;
		$onsave = get_option( "mfrh_rename_on_save" );
		if ( !$onsave )
			return;
		$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $post_id );
		$medias = get_posts( $args );
		if ( $medias ) {
			$this->log( '[save_post]' );
			foreach ( $medias as $attach ) {
				// In the past, I used this to detect if the Media Library is NOT used:
				// isset( $attachment['image-size'] );
				$this->rename( $attach->ID );
			}
		}
	}

	/**
	 *
	 * EDITOR
	 *
	 */

	function attachment_fields_to_save( $post, $attachment ) {
		$this->log( '[attachment_fields_to_save]' );
		$post = $this->rename( $post );
		return $post;
	}

	function log_sql( $data, $antidata ) {
		if ( !get_option( 'mfrh_logsql' ) || !$this->admin->is_registered() )
			return;
		$fh = fopen( trailingslashit( dirname(__FILE__) ) . 'mfrh_sql.log', 'a' );
		$fh_anti = fopen( trailingslashit( dirname(__FILE__) ) . 'mfrh_sql_revert.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "{$data}\n" );
		fwrite( $fh_anti, "{$antidata}\n" );
		fclose( $fh );
		fclose( $fh_anti );
	}

	function log( $data, $inErrorLog = false ) {
		if ( $inErrorLog )
			error_log( $data );
		if ( !get_option( 'mfrh_log' ) ) {
			return;
		}
		$fh = fopen( trailingslashit( dirname(__FILE__) ) . 'media-file-renamer.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "$date: {$data}\n" );
		fclose( $fh );
	}

	/**
	 *
	 * GENERATE A NEW FILENAME
	 *
	 */

	/**
	 * Performs transliteration
	 * @param string $str The string to transliterate
	 * @return string
	 */
	function transliterate( $str ) {
		// Conversion table
		static $chars = null;

		if ( is_null($chars) ) {
			$chars = array (
				/** Cyrillics **/
				'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',
				'Д' => 'D',    'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',
				'З' => 'Z',    'И' => 'I',    'Й' => 'I',    'К' => 'K',
				'Л' => 'L',    'М' => 'M',    'Н' => 'N',    'О' => 'O',
				'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
				'У' => 'U',    'Ф' => 'F',    'Х' => 'Kh',   'Ц' => 'Ts',
				'Ч' => 'Ch',   'Ш' => 'Sh',   'Щ' => 'Shch', 'Ъ' => 'Ie',
				'Ы' => 'Y',    'Ь' => '',     'Э' => 'E',    'Ю' => 'Iu',
				'Я' => 'Ia',
				'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',
				'д' => 'd',    'е' => 'e',    'ё' => 'e',    'ж' => 'zh',
				'з' => 'z',    'и' => 'i',    'й' => 'i',    'к' => 'k',
				'л' => 'l',    'м' => 'm',    'н' => 'n',    'о' => 'o',
				'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
				'у' => 'u',    'ф' => 'f',    'х' => 'kh',   'ц' => 'ts',
				'ч' => 'ch',   'ш' => 'sh',   'щ' => 'shch', 'ъ' => 'ie',
				'ы' => 'y',    'ь' => '',     'э' => 'e',    'ю' => 'iu',
				'я' => 'ia'
			);
		}
		// Preform conversion
		foreach ( $chars as $from => $to )
			$str = str_replace( $from, $to, $str );

		return $str;
	}

	/**
	 * Removes some unicode puncuation characters from a string.
	 * The conversion table derived from `sanitize_title_with_dashes()`
	 * @param string $str The string to remove from
	 * @return string
	 * @see sanitize_title_with_dashes()
	 */
	function remove_special_chars( $str ) {
		// Conversion table
		static $chars = null;

		if ( is_null($chars) ) {
			$chars = array (
				// iexcl and iquest
				'%c2%a1', '%c2%bf',
				// angle quotes
				'%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
				// curly quotes
				'%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
				'%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
				// copy, reg, deg, hellip and trade
				'%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
				// acute accents
				'%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
				// grave accent, macron, caron
				'%cc%80', '%cc%84', '%cc%8c',
			);
			$chars = array_map( 'urldecode', $chars );

			// Combining Diacritical Marks (U+0300 - U+036F)
			// @see http://www.fileformat.info/info/unicode/block/combining_diacritical_marks/list.htm
			for ( $i = 0x0300; $i <= 0x036f; $i++ ) $chars[] = $this->u( $i );

			// Combining Diacritical Marks Extended (U+1AB0 - U+1ABE)
			// @see http://www.fileformat.info/info/unicode/block/combining_diacritical_marks_extended/list.htm
			for ( $i = 0x1ab0; $i <= 0x1abe; $i++ ) $chars[] = $this->u( $i );

			// Combining Diacritical Marks Supplement (U+1DC0 - U+1DEF)
			// @see http://www.fileformat.info/info/unicode/block/combining_diacritical_marks_supplement/list.htm
			for ( $i = 0x1dc0; $i <= 0x1def; $i++ ) $chars[] = $this->u( $i );

			// Common Diacrical Marks
			$x = array (
				// Circumflex
				0x005e, 0x02c6,
				// Low Circumflex
				0xa788,
				// Tilde
				0x007e, 0x02dc,
				// Low Tilde
				0x02f7,
			);
			$chars = array_merge( $chars, array_map( array ( $this, 'u' ), $x ) );
		}
		// Preform conversion
		foreach ( $chars as $char )
			$str = str_replace( $char, '', $str );

		return $str;
	}

	/**
	 * Returns a decoded unicode character from its code point
	 * @param int $code_point
	 * @return string
	 */
	function u( $code_point ) {
		$u = str_pad( dechex( $code_point ), 4, '0', STR_PAD_LEFT ); // 4 digits hexadecimal string
		return json_decode( '"\u' . $u . '"' );
	}

	function replace_chars( $str ) {
		$special_chars = array();
		$special_chars = apply_filters( 'mfrh_replace_rules', $special_chars );
		if ( !empty( $special_chars ) )
			foreach ( $special_chars as $key => $value )
				$str = str_replace( $key, $value, $str );
		return $str;
	}

	/**
	 * Computes the ideal filename based on a text
	 * @param array $media
	 * @param string $text
	 * @param string $manual_filename
	 * @return string|NULL If the resulting filename had no any valid characters, NULL is returned
	 */
	function new_filename( $media, $text, $manual_filename = null ) {

		$old_filename = null;
		$old_filename_no_ext = null;
		$new_ext = null;

		if ( !empty( $media ) ) {
			// Media already exists (not a fresh upload). Gets filename and ext.
			$old_filepath = get_attached_file( $media['ID'] );
			$pp = mfrh_pathinfo( $old_filepath );
			$new_ext = empty( $pp['extension'] ) ? "" : $pp['extension'];
			$old_filename = $pp['basename'];
			$old_filename_no_ext = $pp['filename'];
		}
		else {
			// It's an upload, let's check if the extension is provided in the text
			$pp = mfrh_pathinfo( $text );
			$new_ext = empty( $pp['extension'] ) ? "" : $pp['extension'];
			$text = $pp['filename'];
		}

		// Generate the new filename.
		if ( !empty( $manual_filename ) ) {
			// Filename is forced. Strip the extension. Keeps this extension in $new_ext.
			$pp = mfrh_pathinfo( $manual_filename );
			$manual_filename = $pp['filename'];
			$new_ext = empty( $pp['extension'] ) ? $new_ext : $pp['extension'];
			$new_filename = $manual_filename;
		}
		else {
			// Filename is generated from $text, without an extension.
			$text = str_replace( ".jpg", "", $text );
			$text = str_replace( ".png", "", $text );
			$text = str_replace( "'", "-", $text );
			$text = strtolower( $this->replace_chars( $text ) );
			$new_filename = sanitize_file_name( $text );
		}

		// Convert all accent characters to ASCII characters
		if ( apply_filters( 'mfrh_converts', false ) ) {
			$new_filename = remove_accents( $new_filename );
			$new_filename = $this->remove_special_chars( $new_filename );
			$new_filename = $this->transliterate( $new_filename );
			$new_filename = strtolower( $new_filename );
		}

		// If the resulting filename had no any valid character, return NULL
		if ( empty( $new_filename ) ) return null;

		if ( !$manual_filename )
			$new_filename = apply_filters( 'mfrh_new_filename', $new_filename, $old_filename_no_ext, $media );

		// We know have a new filename, let's add an extension.
		$new_filename = !empty( $new_ext ) ? ( $new_filename . '.' . $new_ext ) : $new_filename;

		return $new_filename;
	}

	// Only replace the first occurence
	function str_replace( $needle, $replace, $haystack ) {
		$pos = strpos( $haystack, $needle );
		if ( $pos !== false )
			$haystack = substr_replace( $haystack, $replace, $pos, strlen( $needle ) );
		return $haystack;
	}

	/**
	 *
	 * RENAME FILES + COFFEE TIME
	 */

	 function call_hooks_rename_url( $post, $orig_image_url, $new_image_url  ) {
		 // With the full URLs
		 do_action( 'mfrh_url_renamed', $post, $orig_image_url, $new_image_url );

		 // With DB URLs
		 $upload_dir = wp_upload_dir();
		 do_action( 'mfrh_url_renamed', $post, str_replace( $upload_dir, "", $orig_image_url ),
		 	str_replace( $upload_dir, "", $new_image_url ) );
	 }

	function rename_file( $old, $new, $case_issue = false ) {
		// Some plugins can create custom thumbnail folders instead in the same folder, so make sure
		// the thumbnail folders are available.
		wp_mkdir_p( dirname($new) );

		// If there is a case issue, that means the system doesn't make the difference between AA.jpg and aa.jpg even though WordPress does.
		// In that case it is important to rename the file to a temporary filename in between like: AA.jpg -> TMP.jpg -> aa.jpg.
		if ( $case_issue ) {
			if ( !rename( $old, $old . md5( $old ) ) ) {
				$this->log( "The file couldn't be renamed (case issue) from $old to " . $old . md5( $old ) . "." );
				return false;
			}
			if ( !rename( $old . md5( $old ), $new ) ) {
				$this->log( "The file couldn't be renamed (case issue) from " . $old . md5( $old ) . " to $new." );
				return false;
			}
		}
		else if ( ( !rename( $old, $new ) ) ) {
			$this->log( "The file couldn't be renamed from $old to $new." );
			return false;
		}
		return true;
	}

	function move( $media, $newPath ) {
		$id = null;
		$post = null;

		// Check the arguments
		if ( is_numeric( $media ) ) {
			$id = $media;
			$post = get_post( $media, ARRAY_A );
		}
		else if ( is_array( $media ) ) {
			$id = $media['ID'];
			$post = $media;
		}
		else {
			die( 'Media File Renamer: move() requires the ID or the array for the media.' );
		}

		// Prepare the variables
		$old_filepath = get_attached_file( $id );
		$path_parts = mfrh_pathinfo( $old_filepath );
		$old_ext = $path_parts['extension'];
		$upload_dir = wp_upload_dir();
		$old_directory = trim( str_replace( $upload_dir['basedir'], '', $path_parts['dirname'] ), '/' ); // '2011/01'
		$new_directory = trim( $newPath, '/' );
		$filename = $path_parts['basename']; // 'whatever.jpeg'
		$new_filepath = trailingslashit( trailingslashit( $upload_dir['basedir'] ) . $new_directory ) . $filename;

		$this->log( "** Move Media: " . $filename );
		$this->log( "The new directory will be: " . mfrh_dirname( $new_filepath ) );

		// Create the directory if it does not exist
		if ( !file_exists( mfrh_dirname( $new_filepath ) ) ) {
			mkdir( mfrh_dirname( $new_filepath ), 0777, true );
		}

		// There is no support for UNDO (as the current process of Media File Renamer doesn't keep the path for the undo, only the filename... so the move breaks this - let's deal with this later).

		// Move the main media file
		if ( !$this->rename_file( $old_filepath, $new_filepath ) ) {
			$this->log( "[!] File\t$old_filepath -> $new_filepath" );
			return false;
		}
		$this->log( "File\t$old_filepath -> $new_filepath" );
		do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );

		// Update the attachment meta
		$meta = wp_get_attachment_metadata( $id );

		if ( $meta ) {
			if ( isset( $meta['file'] ) && !empty( $meta['file'] ) )
				$meta['file'] = $this->str_replace( $old_directory, $new_directory, $meta['file'] );
			if ( isset( $meta['url'] ) && !empty( $meta['url'] ) && count( $meta['url'] ) > 4 )
				$meta['url'] = $this->str_replace( $old_directory, $new_directory, $meta['url'] );
		}

		// Better to check like this rather than with wp_attachment_is_image
		// PDFs also have thumbnails now, since WP 4.7
		$has_thumbnails = isset( $meta['sizes'] );

		if ( $has_thumbnails ) {
			$orig_image_urls = array();
			$orig_image_data = wp_get_attachment_image_src( $id, 'full' );
			$orig_image_urls['full'] = $orig_image_data[0];
			foreach ( $meta['sizes'] as $size => $meta_size ) {
				if ( !isset($meta['sizes'][$size]['file'] ) )
					continue;
				$meta_old_filename = $meta['sizes'][$size]['file'];
				$meta_old_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $old_directory ) . $meta_old_filename;
				$meta_new_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $new_directory ) . $meta_old_filename;
				$orig_image_data = wp_get_attachment_image_src( $id, $size );
				$orig_image_urls[$size] = $orig_image_data[0];

				// Double check files exist before trying to rename.
				if ( file_exists( $meta_old_filepath )
						&& ( ( !file_exists( $meta_new_filepath ) ) || is_writable( $meta_new_filepath ) ) ) {
					// WP Retina 2x is detected, let's rename those files as well
					if ( function_exists( 'wr2x_get_retina' ) ) {
						$wr2x_old_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_old_filepath );
						$wr2x_new_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_new_filepath );
						if ( file_exists( $wr2x_old_filepath )
							&& ( ( !file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {

							// Rename retina file
							if ( !$this->rename_file( $wr2x_old_filepath, $wr2x_new_filepath, $case_issue ) && !$force_rename ) {
								$this->log( "[!] Retina $wr2x_old_filepath -> $wr2x_new_filepath" );
								return $post;
							}
							$this->log( "Retina\t$wr2x_old_filepath -> $wr2x_new_filepath" );
							do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
						}
					}

					// Rename meta file
					if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath ) ) {
						$this->log( "[!] File $meta_old_filepath -> $meta_new_filepath" );
						return false;
					}

					// Success, call other plugins
					$this->log( "File\t$meta_old_filepath -> $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );

				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $id );
		}

		// Update metadata
		if ( $meta )
			wp_update_attachment_metadata( $id, $meta );
		update_attached_file( $id, $new_filepath );

		// I wonder about cleaning the cache for this media. It might have no impact, and will not reset the cache for the posts using this media anyway, and it adds processing time. I keep it for now, but there might be something better to do.
		clean_post_cache( $id );

		// Call the actions so that the plugin's plugins can update everything else (than the files)
		if ( $has_thumbnails ) {
			$orig_image_url = $orig_image_urls['full'];
			$new_image_data = wp_get_attachment_image_src( $id, 'full' );
			$new_image_url = $new_image_data[0];
			$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
			if ( !empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $meta_size ) {
					$orig_image_url = $orig_image_urls[$size];
					$new_image_data = wp_get_attachment_image_src( $id, $size );
					$new_image_url = $new_image_data[0];
					$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
				}
			}
		}
		else {
			$new_attachment_url = wp_get_attachment_url( $id );
			$this->call_hooks_rename_url( $post, $orig_attachment_url, $new_attachment_url );
		}

		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath );
		return true;
	}

	function rename( $media, $manual_filename = null, $fromMediaLibrary = true ) {
		$id = null;
		$post = null;

		// Check the arguments
		if ( is_numeric( $media ) ) {
			$id = $media;
			$post = get_post( $media, ARRAY_A );
		}
		else if ( is_array( $media ) ) {
			$id = $media['ID'];
			$post = $media;
		}
		else {
			die( 'Media File Renamer: rename() requires the ID or the array for the media.' );
		}

		$force_rename = apply_filters( 'mfrh_force_rename', false );
		$method = apply_filters( 'mfrh_method', 'media_title' );

		// Check attachment
		$need_rename = $this->check_attachment( $post, $output, $manual_filename );
		if ( !$need_rename ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return $post;
		}

		// Prepare the variables
		$old_filepath = $output['current_filepath'];
		$case_issue = $output['case_issue'];
		$new_filepath = $output['desired_filepath'];
		$new_filename = $output['desired_filename'];
		$manual = $output['manual'] || !empty( $manual_filename );
		$path_parts = mfrh_pathinfo( $old_filepath );
		$directory = $path_parts['dirname']; // '2011/01'
		$old_filename = $path_parts['basename']; // 'whatever.jpeg'

		$this->log( "** Rename Media: " . $old_filename );
		$this->log( "New file should be: " . $new_filename );

		// Check for issues with the files
		if ( !file_exists( $old_filepath ) ) {
			$this->log( "The original file ($old_filepath) cannot be found." );
			return $post;
		}
		if ( !$case_issue && !$force_rename && file_exists( $new_filepath ) ) {
			$this->log( "The new file already exists ($new_filepath). It is not a case issue. Renaming cancelled." );
			return $post;
		}

		// Keep the original filename
		$original_filename = get_post_meta( $id, '_original_filename', true );
		if ( empty( $original_filename ) )
			add_post_meta( $id, '_original_filename', $old_filename, true );

		// Rename the main media file.
		if ( !$this->rename_file( $old_filepath, $new_filepath, $case_issue ) && !$force_rename ) {
			$this->log( "[!] File $old_filepath -> $new_filepath" );
			return $post;
		}
		$this->log( "File\t$old_filepath -> $new_filepath" );
		do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );

		// The new extension (or maybe it's just the old one)
		$old_ext = $path_parts['extension'];
		$new_ext = $old_ext;
		if ( $manual_filename ) {
			$pp = mfrh_pathinfo( $manual_filename );
			$new_ext = $pp['extension'];
		}

		// Filenames without extensions
		$noext_old_filename = $this->str_replace( '.' . $old_ext, '', $old_filename );
		$noext_new_filename = $this->str_replace( '.' . $old_ext, '', $new_filename );

		// Update the attachment meta
		$meta = wp_get_attachment_metadata( $id );

		if ( $meta ) {
			if ( isset( $meta['file'] ) && !empty( $meta['file'] ) )
				$meta['file'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
			if ( isset( $meta['url'] ) && !empty( $meta['url'] ) && strlen( $meta['url'] ) > 4 )
				$meta['url'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['url'] );
			else
				$meta['url'] = $noext_new_filename . '.' . $old_ext;
		}

		// Better to check like this rather than with wp_attachment_is_image
		// PDFs also have thumbnails now, since WP 4.7
		$has_thumbnails = isset( $meta['sizes'] );

		// Loop through the different sizes in the case of an image, and rename them.
		if ( $has_thumbnails ) {
			$orig_image_urls = array();
			$orig_image_data = wp_get_attachment_image_src( $id, 'full' );
			$orig_image_urls['full'] = $orig_image_data[0];
			foreach ( $meta['sizes'] as $size => $meta_size ) {
				if ( !isset($meta['sizes'][$size]['file'] ) )
					continue;
				$meta_old_filename = $meta['sizes'][$size]['file'];
				$meta_old_filepath = trailingslashit( $directory ) . $meta_old_filename;
				$meta_new_filename = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );

				// Manual Rename also uses the new extension (if it was not stripped to avoid user mistake)
				if ( $force_rename && !empty( $new_ext ) ) {
					$meta_new_filename = $this->str_replace( $old_ext, $new_ext, $meta_new_filename );
				}

				$meta_new_filepath = trailingslashit( $directory ) . $meta_new_filename;
				$orig_image_data = wp_get_attachment_image_src( $id, $size );
				$orig_image_urls[$size] = $orig_image_data[0];

				// Double check files exist before trying to rename.
				if (
					$force_rename || (
						file_exists( $meta_old_filepath ) && (
							( !file_exists( $meta_new_filepath ) ) ||
							is_writable( $meta_new_filepath )
						)
					)
				) {
					// WP Retina 2x is detected, let's rename those files as well
					if ( function_exists( 'wr2x_get_retina' ) ) {
						$wr2x_old_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_old_filepath );
						$wr2x_new_filepath = $this->str_replace( '.' . $new_ext, '@2x.' . $new_ext, $meta_new_filepath );
						if ( file_exists( $wr2x_old_filepath )
							&& ( ( !file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {

							// Rename retina file
							if ( !$this->rename_file( $wr2x_old_filepath, $wr2x_new_filepath, $case_issue ) && !$force_rename ) {
								$this->log( "[!] Retina $wr2x_old_filepath -> $wr2x_new_filepath" );
								return $post;
							}
							$this->log( "Retina\t$wr2x_old_filepath -> $wr2x_new_filepath" );
							do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
						}
					}

					// Rename meta file
					if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath, $case_issue ) && !$force_rename ) {
						$this->log( "[!] File $meta_old_filepath -> $meta_new_filepath" );
						return $post;
					}

					$meta['sizes'][$size]['file'] = $meta_new_filename;

					// Detect if another size has exactly the same filename
					foreach ( $meta['sizes'] as $s => $m ) {
						if ( !isset( $meta['sizes'][$s]['file'] ) )
							continue;
						if ( $meta['sizes'][$s]['file'] ==  $meta_old_filename ) {
							$this->log( "Updated $s based on $size, as they use the same file (probably same size)." );
							$meta['sizes'][$s]['file'] = $meta_new_filename;
						}
					}

					// Success, call other plugins
					$this->log( "File\t$meta_old_filepath -> $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );

				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $id );
		}

		// This media doesn't require renaming anymore
		delete_post_meta( $id, '_require_file_renaming' );

		// If it was renamed manually (including undo), lock the file
		if ( $manual )
			add_post_meta( $id, '_manual_file_renaming', true, true );

		// Update metadata
		if ( $meta )
			wp_update_attachment_metadata( $id, $meta );
		update_attached_file( $id, $new_filepath );

		// I wonder about cleaning the cache for this media. It might have no impact, and will not reset the cache for the posts using this media anyway, and it adds processing time. I keep it for now, but there might be something better to do.
		clean_post_cache( $id );

		// Rename slug/permalink
		if ( get_option( "mfrh_rename_slug" ) ) {
			$oldslug = $post['post_name'];
			$info = mfrh_pathinfo( $new_filepath );
			$newslug = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $info['basename'] );
			$post['post_name'] = $newslug;
			if ( wp_update_post( $post ) )
				$this->log( "Slug\t$oldslug -> $newslug" );
		}

		// Call the actions so that the plugin's plugins can update everything else (than the files)
		if ( $has_thumbnails ) {
			$orig_image_url = $orig_image_urls['full'];
			$new_image_data = wp_get_attachment_image_src( $id, 'full' );
			$new_image_url = $new_image_data[0];
			$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
			if ( !empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $meta_size ) {
					$orig_image_url = $orig_image_urls[$size];
					$new_image_data = wp_get_attachment_image_src( $id, $size );
					$new_image_url = $new_image_data[0];
					$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
				}
			}
		}
		else {
			$new_attachment_url = wp_get_attachment_url( $id );
			$this->call_hooks_rename_url( $post, $orig_attachment_url, $new_attachment_url );
		}

		// HTTP REFERER set to the new media link
		if ( isset( $_REQUEST['_wp_original_http_referer'] ) &&
			strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
			$_REQUEST['_wp_original_http_referer'] = get_permalink( $id );
		}

		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath );
		return $post;
	}

	/**
	 * Locks a post to be manual-rename only
	 * @param int|WP_Post $post The post to lock
	 * @return True on success, false on failure
	 */
	function lock( $post ) {
		return !!add_post_meta( $post instanceof WP_Post ? $post->ID : $post, '_manual_file_renaming', true, true );
	}

	/**
	 * Unlocks a locked post
	 * @param int|WP_Post $post The post to unlock
	 * @return True on success, false on failure
	 */
	function unlock( $post ) {
		return delete_post_meta( $post instanceof WP_Post ? $post->ID : $post, '_manual_file_renaming' );
	}

	/**
	 * Determines whether a post is locked
	 * @param int|WP_Post $post The post to check
	 * @return Boolean
	 */
	function is_locked( $post ) {
		return get_post_meta( $post instanceof WP_Post ? $post->ID : $post, '_manual_file_renaming', true ) === true;
	}
}
