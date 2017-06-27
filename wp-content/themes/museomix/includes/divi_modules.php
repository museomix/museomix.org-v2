<?php
function CommunitiesMapdoCustomModule() {
	if(class_exists("ET_Builder_Module")){
		class ET_Builder_Module_CommunitiesMap extends ET_Builder_Module {
			function init() {
				wp_register_script( 'msx-google-maps-api', 'https://maps.googleapis.com/maps/api/js?key='.GOOGLE_MAPS_KEY, null, null, true );
				wp_register_script( 'communities_map', get_stylesheet_directory_uri().'/assets/js/communities_map.js', array('msx-google-maps-api','global-js'), '', true);


				// wp_register_style('slick-css', '//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css');
				// wp_register_style('slick-theme', '//cdn.jsdelivr.net/jquery.slick/1.6.0/slick-theme.css');
				if(!is_admin()) {
					wp_enqueue_script('communities_map');
					wp_enqueue_script('msx-google-maps-api');
					// wp_enqueue_style('slick-css');
					// wp_enqueue_style('slick-theme');
				}

				$this->name            = esc_html__( 'Communities map' );
				$this->slug            = 'et_pb_communities_map_mc';

				$this->whitelisted_fields = array();
				foreach ( $this->get_fields() as $name => $field ) {
					$this->whitelisted_fields[] = $name;
				}

			}

			function get_fields() {
				$fields = array(
					  'admin_label' => array(
							  'label'       => __( 'Admin Label', 'et_builder' ),
							  'type'        => 'text',
							  'description' => __( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
					  )
				);
				return $fields;
			}
			function shortcode_callback( $atts, $content = null, $function_name ) {

				$content = $this->shortcode_content;
				$communities = get_posts(
					array(
						'post_type' => 'community',
						'suppress_filters' => false,
						'posts_per_page' => -1
					)
				);
				$locations = array();
				foreach($communities as $community):
					$location = get_field('geolocation', $community->ID);
					if ($location):
						$locations[] = array(
							'title' => '<h1>'.$community->post_title.'</h1>',
							'lat' => (float)$location['lat'],
							'lng' => (float)$location['lng'],
							'link' => '<a class="btn map_info_link" href="'.get_permalink($community->ID).'">'.__('More info', 'museomix').'</a>'
						);
					endif;
				endforeach;
				if (!$locations)
					return;

				$output = '<div class="et_pb_module et_pb_communities_map">
						<div id="communities-map"><script>
						var communities = '.json_encode($locations).';</script></div> <!-- .et_pb_slides -->
					</div> <!-- .et_pb_slick_slider_mc -->
					';

				return $output;
			}
		}
	new ET_Builder_Module_CommunitiesMap;

	}
}

add_action('after_setup_theme', 'prepareCommunitiesMapModule', 999);
function prepareCommunitiesMapModule(){
	global $pagenow;

	$is_admin = is_admin();
	$action_hook = $is_admin ? 'wp_loaded' : 'wp';
	$required_admin_pages = array( 'edit.php', 'post.php', 'post-new.php', 'admin.php', 'customize.php', 'edit-tags.php', 'admin-ajax.php', 'export.php' ); // list of admin pages where we need to load builder files
	$specific_filter_pages = array( 'edit.php', 'admin.php', 'edit-tags.php' ); // list of admin pages where we need more specific filtering
	$is_edit_library_page = 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'et_pb_layout' === $_GET['post_type'];
	$is_role_editor_page = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'et_divi_role_editor' === $_GET['page'];
	$is_import_page = 'admin.php' === $pagenow && isset( $_GET['import'] ) && 'wordpress' === $_GET['import']; // Page Builder files should be loaded on import page as well to register the et_pb_layout post type properly
	$is_edit_layout_category_page = 'edit-tags.php' === $pagenow && isset( $_GET['taxonomy'] ) && 'layout_category' === $_GET['taxonomy'];

	if ( ! $is_admin || ( $is_admin && in_array( $pagenow, $required_admin_pages ) && ( ! in_array( $pagenow, $specific_filter_pages ) || $is_edit_library_page || $is_role_editor_page || $is_edit_layout_category_page || $is_import_page ) ) ) {
		add_action($action_hook, 'CommunitiesMapdoCustomModule', 9789);
	}
}
