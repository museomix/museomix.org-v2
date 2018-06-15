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
