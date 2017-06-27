<?php

/**
 * Created by PhpStorm.
 * User: konrad
 * Date: 12.01.17
 * Time: 12:36
 */
class WPML_ACF_Pro {

	public function __construct() {
		add_action('icl_make_duplicate', array($this, 'make_duplicate_action'), 10, 4);
	}

	public function make_duplicate_action($master_post_id, $lang, $post_array, $id) {
		if (isset($post_array['post_type']) && 'acf-field-group' == $post_array['post_type']) {
			$args = array(
				'post_parent' => $id,
				'post_type'   => 'acf-field',
				'numberposts' => -1
			);

			$posts = get_posts($args);

			if (is_array($posts) && count($posts) > 0) {
				foreach($posts as $post) {
					wp_delete_post($post->ID, true);
				}
			}
		}

	}

}