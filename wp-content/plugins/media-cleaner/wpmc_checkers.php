<?php

// This file will contains all the CORE checkers of the Media Cleaner system.
// Each 'checker' checks the usage of the file by a certain feature of WordPress.

class Meow_WPMC_Checkers {

	private $core;

	public function __construct( $core ) {
		$this->core = $core;
	}

	function has_background_or_header( $file, $mediaId = null ) {
		if ( current_theme_supports( 'custom-header' ) ) {
			$custom_header = get_custom_header();
			if ( $custom_header && $custom_header->url ) {
				if ( strpos( $custom_header->url, $file ) !== false ) {
					$this->core->log( "{$file} found in theme (Custom Header)" );
					return true;
				}
			}
		}

		if ( current_theme_supports( 'custom-background' ) ) {
			$custom_background = get_theme_mod( 'background_image' );
			if ( $custom_background ) {
				if ( strpos( $custom_background, $file ) !== false ) {
					$this->core->log( "{$file} found in theme (Custom Background)" );
					return true;
				}
			}
		}

		// Support for Kantan Theme
		if ( $mediaId ) {
			$photography_hero_image = get_theme_mod( 'photography_hero_image' );
			if ( !empty( $photography_hero_image ) ) {
				if ( $photography_hero_image == $mediaId ) {
					$this->core->log( "{$file} found in theme (Photography Hero Image)" );
					return true;
				}
			}
			$author_profile_picture = get_theme_mod( 'author_profile_picture' );
			if ( !empty( $author_profile_picture ) ) {
				if ( $author_profile_picture == $mediaId ) {
					$this->core->log( "{$file} found in theme (Author Profile Photo)" );
					return true;
				}
			}
		}

		return false;
	}

	function check_in_gallery( $file, $mediaId = 0 ) {

		if ( !get_option( 'wpmc_galleries', false ) )
			return false;

		$file = $this->core->wpmc_clean_uploaded_filename( $file );
		$pinfo = pathinfo( $file );
		$url = $pinfo['dirname'] . '/' . $pinfo['filename'] .
			( isset( $pinfo['extension'] ) ? ( '.' . $pinfo['extension'] ) : '' );

		// Galleries in Visual Composer (WPBakery)
		if ( class_exists( 'Vc_Manager' ) ) {
			$galleries_images_vc = get_transient( "wpmc_galleries_images_visualcomposer" );
			if ( is_array( $galleries_images_vc ) && in_array( $mediaId, $galleries_images_vc ) ) {
				$this->core->log( "Media {$mediaId} found in a Visual Composer gallery" );
				return true;
			}
		}

		// Galleries in Fusion Builder (Avada Theme)
		if ( function_exists( 'fusion_builder_map' ) ) {
			$galleries_images_fb = get_transient( "wpmc_galleries_images_fusionbuilder" );
			if ( is_array( $galleries_images_fb ) && in_array( $mediaId, $galleries_images_fb ) ) {
				$this->core->log( "Media {$mediaId} found in post_content (Fusion Builder)" );
				return true;
			}
		}

		// Check in WooCommerce Galleries
		if ( class_exists( 'WooCommerce' ) ) {
			$galleries_images_wc = get_transient( "wpmc_galleries_images_woocommerce" );
			if ( is_array( $galleries_images_wc ) && in_array( $mediaId, $galleries_images_wc ) ) {
				$this->core->log( "Media {$mediaId} found in a WooCommerce gallery" );
				return true;
			}
		}

		// Check in standard WP Galleries (URLS)
		$galleries_images = get_transient( "wpmc_galleries_images" );
		if ( is_array( $galleries_images ) && in_array( $file, $galleries_images ) ) {
			$this->core->log( "URL {$file} found in a standard WP Gallery" );
			return true;
		}

		return false;
	}

	function has_meta( $file, $mediaId = 0 ) {

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
		if ( $mediaId > 0 ) {
			$mediaCount = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*)
					FROM $wpdb->postmeta
					WHERE post_id != %d
					AND meta_key != '_wp_attached_file'
					AND (meta_value REGEXP %s OR meta_value = %d)",
					$mediaId, $regex_mysql, $mediaId
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
		if ( $mediaCount > 0 )
			$this->core->log( "{$file} found in Post Meta" );
		return $mediaCount > 0;
	}


	function has_content( $file, $mediaId = null ) {

		global $wpdb;
		$this->core->last_analysis_ids = null;
		$shortcode_support = get_option( 'wpmc_shortcode', false );
		$file = $this->core->wpmc_clean_uploaded_filename( $file );
		$pinfo = pathinfo( $file );
		$url = $pinfo['dirname'] . '/' . $pinfo['filename'] .
			( isset( $pinfo['extension'] ) ? ( '.' . $pinfo['extension'] ) : '' );

		// Check in Posts Content
		if ( get_option( 'wpmc_posts', true ) ) {

			if ( !empty( $mediaId ) ) {
				// Search through the CSS class
				$posts_images_ids = get_transient( "wpmc_posts_images_ids" );
				if ( in_array( $mediaId, $posts_images_ids ) ) {
					$this->core->log( "Media {$mediaId} found in content (Posts Images IDs)" );
					return true;
				}

				// Posts in Visual Composer (WPBakery)
				if ( class_exists( 'Vc_Manager' ) ) {
					$posts_images_vc = get_transient( "wpmc_posts_images_visualcomposer" );
					if ( in_array( $mediaId, $posts_images_vc ) ) {
						$this->core->log( "Media {$mediaId} found in content (Visual Composer)" );
						return true;
					}
				}
			}

			// Search through the filename
			$posts_images_urls = get_transient( "wpmc_posts_images_urls" );
			if ( in_array( $url, $posts_images_urls ) ) {
				$this->core->log( "URL {$url} found in content (Posts Images URLs)" );
				return true;
			}
		}

		// Search in widgets
		if ( get_option( 'wpmc_widgets', false ) ) {
			$widgets_images = get_transient( "wpmc_widgets_images" );
			if ( in_array( $url, $widgets_images ) ) {
				$this->core->log( "URL {$url} found in widgets (Widgets Images)" );
				$this->core->last_analysis = "WIDGET";
				return true;
			}
		}

		return false;
	}

}

?>
