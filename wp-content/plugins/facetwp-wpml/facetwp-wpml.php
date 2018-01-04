<?php
/*
Plugin Name: FacetWP - WPML
Description: WPML support for FacetWP
Version: 1.3.0
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-wpml
*/

defined( 'ABSPATH' ) or exit;

class FWP_WPML
{

    public $default_language;
    public $current_language;


    function __construct() {
        add_action( 'init' , array( $this, 'init' ) );
    }


    /**
     * Initialize
     */
    function init() {
        if ( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'FWP' ) ) {
            add_action( 'wp_footer', array( $this, 'wp_footer' ), 30 );
            add_filter( 'facetwp_query_args', array( $this, 'facetwp_query_args' ) );
            add_filter( 'facetwp_render_params', array( $this, 'support_preloader' ) );
            add_filter( 'facetwp_indexer_query_args', array( $this, 'indexer_query_args' ) );
            add_action( 'facetwp_indexer_post', array( $this, 'set_post_language_code' ) );

            // Setup properties
            $this->default_language = apply_filters( 'wpml_default_language', null );
            $this->current_language = apply_filters( 'wpml_current_language', null );

            // Require WPML String Translation
            if ( function_exists( 'icl_register_string' ) ) {
                add_action( 'admin_init', array( $this, 'register_strings' ) );
                add_filter( 'facetwp_i18n', array( $this, 'facetwp_i18n' ) );
            }
        }
    }


    /**
     * Put the language into FWP_HTTP
     */
    function wp_footer() {
        $lang = $this->current_language;
        echo "<script>var FWP_HTTP = FWP_HTTP || {}; FWP_HTTP.lang = '$lang';</script>";
    }


    /**
     * Support FacetWP preloading (3.0.4+)
     */
    function support_preloader( $params ) {
        if ( isset( $params['is_preload'] ) ) {
            $params['http_params']['lang'] = $this->current_language;
        }

        return $params;
    }


    /**
     * Query posts for the current language
     */
    function facetwp_query_args( $args ) {
        $http = FWP()->facet->http_params;
        if ( isset( $http['lang'] ) && $http['lang'] !== $this->default_language ) {
            do_action( 'wpml_switch_language', $http['lang'] );
        }

        return $args;
    }


    /**
     * Index all languages
     */
    function indexer_query_args( $args ) {
        if ( function_exists( 'is_checkout' ) && is_checkout() ) {
            return $args;
        }

        if ( -1 === $args['posts_per_page'] ) {
            do_action( 'wpml_switch_language', 'all' );
        }

        $args['suppress_filters'] = true; // query posts in all languages
        return $args;
    }


    /**
     * Find a post's language code
     */
    function get_post_language_code( $post_id ) {
        global $wpdb;

        $query = $wpdb->prepare( "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d", $post_id );
        return $wpdb->get_var( $query );
    }


    /**
     * Set the indexer language code
     */
    function set_post_language_code( $params ) {
        $language_code = $this->get_post_language_code( $params['post_id'] );
        do_action( 'wpml_switch_language', $language_code );
    }


    /**
     * Register dynamic strings
     */
    function register_strings() {
        $facets = FWP()->helper->get_facets();
        $whitelist = array( 'label', 'label_any', 'placeholder' );

        if ( ! empty( $facets ) ) {
            foreach ( $facets as $facet ) {
                foreach ( $whitelist as $k ) {
                    if ( ! empty( $facet[ $k ] ) ) {
                        do_action( 'wpml_register_single_string', 'FacetWP', $facet[ $k ], $facet[ $k ] );
                    }
                }
            }
        }
    }


    /**
     * Handle string translations
     */
    function facetwp_i18n( $string ) {
        $lang = $this->current_language;
        $default = $this->default_language;

        if ( isset( FWP()->facet->http_params['lang'] ) ) {
            $lang = FWP()->facet->http_params['lang'];
        }

        if ( $lang != $default ) {
            return apply_filters( 'wpml_translate_single_string', $string, 'FacetWP', $string, $lang );
        }

        return $string;
    }
}


$fwp_wpml = new FWP_WPML();
