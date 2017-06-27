<?php

// This file will contains all the CORE checkers of the Media Cleaner system.
// Each 'checker' checks the usage of the file by a certain feature of WordPress.

class Meow_WPMC_Checkers {

	private $core;

	public function __construct( $core ) {
		$this->core = $core;
	}

	function has_background_or_header( $file ) {
		if ( current_theme_supports( 'custom-header' ) ) {
			$custom_header = get_custom_header();
			if ( $custom_header && $custom_header->url ) {
				if ( strpos( $custom_header->url, $file ) !== false ) {
					if ($this->core->debug)
						error_log("{$file} found in header");
					return true;
				}
			}
		}

		if ( current_theme_supports( 'custom-background' ) ) {
			$custom_background = get_theme_mod('background_image');
			if ( $custom_background ) {
				if ( strpos( $custom_background, $file ) !== false ) {
					if ($this->core->debug)
						error_log("{$file} found in background");
					return true;
				}
			}
		}

		return false;
	}

	function check_in_gallery( $file ) {

		if ( !get_option( 'wpmc_galleries', false ) )
			return false;

		$file = $this->core->wpmc_clean_uploaded_filename( $file );
		$uploads = wp_upload_dir();
		$parsedURL = parse_url( $uploads['baseurl'] );
		$regex_match_file = '(' . preg_quote( $file ) . ')';
		$regex = addcslashes( '(?:(?:http(?:s)?\\:)?//' .
			preg_quote( $parsedURL['host'] ).')?' .
			preg_quote( $parsedURL['path'] ) . '/' . $regex_match_file, '/');
		$images = get_transient( "wpmc_galleries_images" );
		if ( !empty( $images ) ) {
			foreach ( $images as $image ) {
				$found = preg_match('/' . $regex . '/i', $image);
				if ( $this->core->debug && $found )
					error_log("{$file} found in a gallery");
				if ( $found )
					return true;
			}
		}
		return false;
	}

	function has_meta( $file, $attachment_id = 0 ) {

		if ( !get_option( 'wpmc_postmeta', true ) )
			return false;

		global $wpdb;
		$uploads = wp_upload_dir();
		$parsedURL = parse_url( $uploads['baseurl'] );
		$file = $this->core->wpmc_clean_uploaded_filename( $file );
		$regex_match_file = '(' . preg_quote( $file ) . ')';
		$regex = addcslashes( '(?:(?:(?:http(?:s)?\\:)?//' .
			preg_quote( $parsedURL['host']) . ')?(?:' .
			preg_quote( $parsedURL['path']) . '/)|^)' . $regex_match_file, '/');
		$regex_mysql = str_replace( '(?:', '(', $regex );
		if ( $attachment_id > 0 ) {
			$mediaCount = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*)
					FROM $wpdb->postmeta
					WHERE post_id != %d
					AND meta_key != '_wp_attached_file'
					AND (meta_value REGEXP %s OR meta_value = %d)",
					$attachment_id, $regex_mysql, $attachment_id
				)
			);
		} else {
			$mediaCount = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*)
					FROM $wpdb->postmeta
					WHERE meta_key != '_wp_attached_file'
					AND meta_value REGEXP %s",
					$regex_mysql
				)
			);
		}
		if ( $this->core->debug && $mediaCount > 0 )
			error_log("{$file} found in POSTMETA");
		return $mediaCount > 0;
	}


	function has_content( $file, $mediaId = null ) {

		global $wpdb;
		$this->core->last_analysis_ids = null;
		$shortcode_support = get_option( 'wpmc_shortcode', false );

		// Check in Posts Content
		if ( get_option( 'wpmc_posts', true ) ) {
			$file = $this->core->wpmc_clean_uploaded_filename( $file );
			$uploads = wp_upload_dir();
			$parsedURL = parse_url( $uploads['baseurl'] );
			$pinfo = pathinfo( $file );
			$regex_match_file = '(' . $pinfo['dirname'] . '/' . $pinfo['filename'] . "(\\-[0-9]{1,8}x[0-9]{1,8})?\\." .
				( isset( $pinfo['extension'] ) ? $pinfo['extension'] : '' ) . ')';

			// SUPER STRICT MODE
			// $regex = addcslashes('=[\'"](?:(?:http(?:s)?\\:)?//'
			// 	. preg_quote( $parsedURL['host'] ) . ')?'
			// 	. preg_quote( $parsedURL['path'] ) . '/'
			// 	. $regex_match_file . '(?:\\?[^\'"]*)*[\'"]', '/' );

			// NORMAL REGEX
			$regex = addcslashes( preg_quote( $parsedURL['path']) . '/' . $regex_match_file . '(?:\\?[^\'"]*)*[\'"]', '/' );
			$regex_mysql = str_replace('(?:', '(', $regex);
			$sql = $wpdb->prepare( "SELECT ID
				FROM $wpdb->posts
				WHERE post_type <> 'revision'
				AND post_type <> 'attachment'
				AND post_content REGEXP %s", $regex_mysql );
			$foundIds = $wpdb->get_col( $sql );
			$this->core->last_analysis_ids = $foundIds;
			$mediaCount = count( $foundIds );
			if ( $this->core->debug && $mediaCount > 0 )
				error_log( "File {$file} found in post_content, $mediaCount time(s)" );
			if ( $mediaCount > 0 )
				return true;

			if ( !empty( $mediaId ) ) {
				$sql = $wpdb->prepare( "SELECT ID
					FROM $wpdb->posts
					WHERE post_type <> 'revision'
					AND post_type <> 'attachment'
					AND post_content LIKE %s", "%wp-image-$mediaId%" );
				$foundIds = $wpdb->get_col( $sql );
				$this->core->last_analysis_ids = $foundIds;
				$mediaCount = count( $foundIds );
				if ( $this->core->debug && $mediaCount > 0 )
					error_log( "Media {$mediaId} found in post_content, $mediaCount time(s)" );
				if ( $mediaCount > 0 )
					return true;
			}
		}

		// Shortcode analysis
		global $shortcode_tags;
		$active_tags = array_keys( $shortcode_tags );
		if ( !empty( $active_tags ) ) {
			$post_contents = get_transient( 'wpmc_posts_with_shortcode' );
			if ( $post_contents === false ) {

				$post_contents = array();

				// Resolve shortcodes from posts
				if ( $shortcode_support ) {
					$query = array();
					$query[] = "SELECT ID, post_content FROM {$wpdb->posts}";
					$query[] = "WHERE post_type <> 'revision' AND post_type <> 'attachment'";
					$sub_query = array();
					foreach ( $active_tags as $tag ) {
						$sub_query[] = "post_content LIKE '%[" .  esc_sql( $wpdb->esc_like( $tag ) ) . "%'";
					}
					$query[] = "AND (" . implode ( " OR ", $sub_query ) . ")";
					$sql = join( ' ', $query );
					$results = $wpdb->get_results( $sql );
					foreach ( $results as $key => $data ) {
						$post_contents['post_' . $data->ID] = do_shortcode( $data->post_content );
					}
				}

				// Read Widgets
				if ( get_option( 'wpmc_widgets', false ) ) {
					global $wp_registered_widgets;
					$active_widgets = get_option( 'sidebars_widgets' );
					foreach ( $active_widgets as $sidebar_name => $sidebar_widgets ) {
						if ( $sidebar_name != 'wp_inactive_widgets' && !empty( $sidebar_widgets ) && is_array( $sidebar_widgets ) ) {
							$i = 0;
							foreach ( $sidebar_widgets as $widget_instance ) {
								$widget_class = $wp_registered_widgets[$widget_instance]['callback'][0]->option_name;
								$instance_id = $wp_registered_widgets[$widget_instance]['params'][0]['number'];
								$widget_data = get_option($widget_class);
								if ( !empty( $widget_data[$instance_id]['text'] ) ) {

									// Resolve Widgets or just get them
									if ( $shortcode_support )
										$post_contents['widget_' . $i] = do_shortcode( $widget_data[$instance_id]['text'] );
									else
										$post_contents['widget_' . $i] = $widget_data[$instance_id]['text'];
								}
								$i++;
							}
						}
					}
				}

				if ( !empty( $post_contents ) )
					set_transient( 'wpmc_posts_with_shortcode', $post_contents, 2 * 60 * 60 );
			}

			if ( !empty( $post_contents ) ) {
				foreach ( $post_contents as $key => $content ) {
					$found = preg_match( '/' . $regex . '/i', $content );
					if ( $this->core->debug && $found )
						error_log( "File Cleaner: {$file} found in {$key} shortcode or widget" );
					if ( $found )
						return true;
				}
			}
		}
		return false;
	}

}

?>
