<?php

/*
 * Plugin Name: CW Shortcode 
 * Plugin URI:  http://www.creaweb2b.com/plugins/
 * Description: Plugin showing shortcode to use insert a module or section inside another
 * Author:      Fabrice ESQUIROL - Creaweb2b
 * Version:     1.0
 * Author URI:  http://www.creaweb2b.com
 */
  
add_action( 'load-post.php', 'cw_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'cw_post_meta_boxes_setup' );

function cw_post_meta_boxes_setup() { 
  add_action( 'add_meta_boxes', 'cw_add_post_meta_boxes' );
}

function cw_add_post_meta_boxes() {
  add_meta_box('cw-post-code',esc_html__( 'Shortcode'),'cw_post_code_meta_box','et_pb_layout','side','default');
}

function cw_post_code_meta_box( $object, $box ) {  
   echo '<p>Copy the following shortcode and paste it where you want the content of this layout to be inserted</p>';
   echo '<h4>[showmodule id="' . $object->ID . '"]</h4>';  
}

function cw_shortcode_add_column( $columns ) {
  $new_columns = array(  
  'shortcode' => __( 'Shortcode')
  );
  $filtered_columns = array_merge( $columns, $new_columns ); 
  return $filtered_columns;
}
add_filter('manage_et_pb_layout_posts_columns' , 'cw_shortcode_add_column');

function cw_shortcode_custom_column_content( $column ) {  
  global $post;
  switch ( $column ) {
    case 'shortcode' :   
      $shortcode = '[showmodule id="' . $post->ID .'"]';      
      echo $shortcode;
    break;      
  }
}
add_action( 'manage_et_pb_layout_posts_custom_column', 'cw_shortcode_custom_column_content' );

function showmodule_shortcode($moduleid) {
    extract(shortcode_atts(array('id' => '*'),$moduleid));   
    return do_shortcode('[et_pb_section global_module="'.$id.'"][/et_pb_section]');
  }
  add_shortcode('showmodule', 'showmodule_shortcode');
  
?>