<?php

/**
 *
 * GLOBAL FUNCTIONS
 *
 */

// Rename the media automatically based on the settings
function mfrh_rename( $mediaId ) {
  global $mfrh_core;
  return $mfrh_core->rename( $mediaId );
}

// Move the media to another folder (relative to /uploads/)
function mfrh_move( $mediaId, $newPath ) {
  global $mfrh_core;
  return $mfrh_core->move( $mediaId, $newPath );
}

/**
 * Calls the specified mb_*** function if it is available.
 * If it isn't, calls the regular function instead
 * @param string $fn The function name to call
 * @return mixed
 */
function mfrh_mb($fn) {
	static $available = null;
	if ( is_null($available) ) $available = extension_loaded( 'mbstring' );

	if ( func_num_args() > 1 ) {
		$args = func_get_args();
		array_shift( $args ); // Remove 1st arg
		return $available ?
			call_user_func_array( "mb_{$fn}", $args ) :
			call_user_func_array( $fn, $args );
	}
	return $available ?
		call_user_func( "mb_{$fn}" ) :
		call_user_func( $fn );
}

/**
 * A multibyte compatible implementation of pathinfo()
 * @param string $path
 * @param int $options
 * @return string|array
 */
function mfrh_pathinfo( $path, $options = null ) {
	if ( is_null( $options ) ) {
		$r = array ();
		if ( $x = mfrh_pathinfo( $path, PATHINFO_DIRNAME ) ) $r['dirname'] = $x;
		$r['basename'] = mfrh_pathinfo( $path, PATHINFO_BASENAME );
		if ( $x = mfrh_pathinfo( $path, PATHINFO_EXTENSION ) ) $r['extension'] = $x;
		$r['filename'] = mfrh_pathinfo( $path, PATHINFO_FILENAME );
		return $r;
	}
	if ( !$path ) return '';
	$path = rtrim( $path, DIRECTORY_SEPARATOR );
	switch ( $options ) {
	case PATHINFO_DIRNAME:
		$x = mfrh_mb( 'strrpos', $path, DIRECTORY_SEPARATOR ); // The last occurrence of slash
		return is_int($x) ? mfrh_mb( 'substr', $path, 0, $x ) : '.';

	case PATHINFO_BASENAME:
		$x = mfrh_mb( 'strrpos', $path, DIRECTORY_SEPARATOR ); // The last occurrence of slash
		return is_int($x) ? mfrh_mb( 'substr', $path, $x + 1 ) : $path;

	case PATHINFO_EXTENSION:
		$x = mfrh_mb( 'strrpos', $path, '.' ); // The last occurrence of dot
		return is_int($x) ? mfrh_mb( 'substr', $path, $x + 1 ) : '';

	case PATHINFO_FILENAME:
		$basename = mfrh_pathinfo( $path, PATHINFO_BASENAME );
		$x = mfrh_mb( 'strrpos', $basename, '.' ); // The last occurrence of dot
		return is_int($x) ? mfrh_mb( 'substr', $basename, 0, $x ) : $basename;
	}
	return pathinfo( $path, $options );
}

/**
 * A multibyte compatible implementation of dirname()
 * @param string $path
 * @return string
 */
function mfrh_dirname( $path ) {
	return mfrh_pathinfo( $path, PATHINFO_DIRNAME );
}

/**
 * A multibyte compatible implementation of basename()
 * @param string $path
 * @return string
 */
function mfrh_basename( $path ) {
	return mfrh_pathinfo( $path, PATHINFO_BASENAME );
}

/**
 *
 * TESTS
 *
 */

// add_action( 'wp_loaded', 'mfrh_test_move' );
// function mfrh_test_move() {
//   mfrh_move( 1620, '/2020/01' );
// }

/**
 *
 * ACTIONS AND FILTERS
 *
 * Available actions are:
 * mfrh_path_renamed
 * mfrh_url_renamed
 * mfrh_media_renamed
 *
 * Please have a look at the custom.php file for examples.
 *
 */

?>
