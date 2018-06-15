<?php

  // Beaver Builder: Update the Metadata, clear the cache
  // https://www.wpbeaverbuilder.com/frequently-asked-questions/

  add_action( 'mfrh_url_renamed', 'mfrh_beaver_builder', 10, 3 );

  function mfrh_beaver_builder( $post, $orig_image_url, $new_image_url ) {
    global $wpdb;
    $query = $wpdb->prepare( "UPDATE $wpdb->postmeta 
      SET meta_value = REPLACE(meta_value, 's:%d:\"$orig_image_url', 's:%d:\"$new_image_url')
      WHERE meta_key = '_fl_builder_data' 
      OR meta_key = '_fl_builder_draft'", 
      strlen( $orig_image_url ), strlen( $new_image_url ) );
    $query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta 
      SET meta_value = REPLACE(meta_value, 's:%d:\"$new_image_url', 's:%d:\"$orig_image_url')
      WHERE meta_key = '_fl_builder_data' 
      OR meta_key = '_fl_builder_draft'",
      strlen( $new_image_url ), strlen( $orig_image_url ) );
    $wpdb->query( $query );
    global $mfrh_core;
    $mfrh_core->log_sql( $query, $query_revert );
    $mfrh_core->log( "Beaver Metadata like $orig_image_url was replaced by $new_image_url." );

    // Clear cache
    $uploads = wp_upload_dir();
    $cache = trailingslashit( $uploads['basedir'] ) . 'bb-plugin';
    if ( file_exists( $cache ) )
      Meow_MFRH_Core::rmdir_recursive( $cache );
    else {
      $cache = trailingslashit( $uploads['basedir'] ) . 'fl-builder';
      if ( file_exists( $cache ) )
        Meow_MFRH_Core::rmdir_recursive( $cache );
    }
  }

?>