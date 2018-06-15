<?php

// EXAMPLES TO USE ACTIONS AND FILTERS
// For help about this file, check:
// https://meowapps.com/media-file-renamer/faq/

// HANDLE THE RENAMING
// $new is the proposed filename by Media File Renamer (without extension)
// $old is the current filename (without extension)
// $post is the attachment/media
// return: your ideal filename
// =============================================================================
// add_filter( 'mfrh_new_filename', 'add_hello_in_front_of_filenames', 10, 3 );
// function add_hello_in_front_of_filenames( $new, $old, $post ) {
//   return $new . "-offbeat";
// }

// REPLACE CHARACTER/STRING IN THE FILENAMES
// =============================================================================
// add_filter( 'mfrh_replace_rules', 'replace_s_by_z', 10, 1 );
//
// function replace_s_by_z( $rules ) {
//   $rules['s'] = 'z';
//   return $rules;
// }

// DO SOMETHING (UPDATES FOR INSTANCE) WHEN THE NEW URL IS READY
// =============================================================================
// add_action( 'mfrh_url_renamed', 'url_of_media_was_modified', 10, 3 );
//
// function url_of_media_was_modified( $post, $orig_image_url, $new_image_url ) {
//   global $wpdb;
//   $query = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = REPLACE(meta_value, '%s', '%s');", $orig_image_url, $new_image_url );
//   $query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta 
//     SET meta_value = REPLACE(meta_value, '%s', '%s');", $new_image_url, $orig_image_url );
//   $wpdb->query( $query );
//   $this->log_sql( $query, $query_revert );
//   $this->log( "Metadata like $orig_image_url were replaced by $new_image_url." );
// }

// DO SOMETHING (UPDATES FOR INSTANCE) WHEN THE FILE IS READY
// =============================================================================
// add_action( 'mfrh_media_renamed', 'filepath_of_media_was_modified', 10, 3 );
//
// function filepath_of_media_was_modified( $post, $orig_image_url, $new_image_url ) {
//   $original_filename = get_post_meta( $post['ID'], '_original_filename', true );
// }
// =============================================================================

// RENAME THE IMAGES IN A WOOCOMMERCE PRODUCT GALLERY EVERY TIME THE PRODUCT IS MODIFIED
// =============================================================================
// add_action( 'woocommerce_update_product', 'woocommerce_product_was_modified', 10, 1 );

// function woocommerce_product_was_modified( $productId ) {
//   $wcProduct = new WC_Product( $productId );
//   $images = array();
//   if ( ( $mainImageId = intval( $wcProduct->get_image_id() ) ) !== 0 ) {
//     $images[] = $mainImageId;
//   }
//   $galleryImages = $wcProduct->get_gallery_image_ids();
//   foreach ( $galleryImages as $imageId ) {
//     $images[] = intval( $imageId );
//   }
//   foreach ( $images as $imageId ) {
//     if ( $imageId != 0 ) {
//       mfrh_rename( $imageId );
//     }
//   }
//   return true;
// }
// =============================================================================

?>
