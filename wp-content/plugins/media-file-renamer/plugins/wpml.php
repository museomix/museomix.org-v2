<?php

  // Thanks to David García from WPML

  add_action( 'mfrh_media_renamed', 'mfrh_wpml_update_translations', 10, 3 );

  function mfrh_wpml_update_translations( $post, $old_filepath, $new_filepath ) {
    $args = array('element_id' => $post['ID'], 'element_type' => 'attachment' );
    $info = apply_filters( 'wpml_element_language_details', null, $args );
    if ( ! empty( $info->trid ) ) {
      $translations = apply_filters( 'wpml_get_element_translations', NULL, $info->trid, 'post_attachment' );
      foreach ( $translations as $translation ) {
        if ( $post['ID'] != $translation->element_id ) {
          update_post_meta( $translation->element_id, '_wp_attached_file', get_post_meta( $post['ID'],
            '_wp_attached_file', true ) );
          update_post_meta( $translation->element_id, '_wp_attachment_metadata', get_post_meta( $post['ID'],
            '_wp_attachment_metadata', true ) );
        }
      }
    }
  }
