<?php

function alter_locations_permalinks( $url, $post ) {
    if ( 'museomix' == get_post_type( $post ) ) {
        $edition = get_field('edition', $post);
		if (!$edition) {
			return $url;
		}
        return icl_get_home_url().'/editions/'.$edition->post_title.'/'.$post->post_name;
    }
	
    if ( 'prototype' == get_post_type( $post ) ) {
        $museomix = get_field('museomix', $post);
        $edition = get_field('edition', $museomix);
		if (!isset($edition->post_title)) {
			// error_log('Post ID = '.$post->ID);
		}
        return icl_get_home_url().'/editions/'.
		($edition ? $edition->post_title.'/' : '').
		$museomix->post_name.'/prototypes/'.
		$post->post_name;
    }
    return $url;
}
add_filter( 'post_type_link', 'alter_locations_permalinks', 10, 2 );