<?php
add_filter( 'automatic_updates_is_vcs_checkout', '__return_false', 1 );  

add_filter( 'redirect_canonical', '__return_false' );
add_theme_support( 'menus' );
function wpdocs_child_theme_setup() {
    load_child_theme_textdomain( 'museomix', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'wpdocs_child_theme_setup' );

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'divi', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'css-sprites', get_stylesheet_directory_uri() . '/sprites.css' );
	wp_enqueue_style( 'css-supp', get_stylesheet_directory_uri() . '/site.css' );
	wp_enqueue_style('quicksand', 'https://fonts.googleapis.com/css?family=Quicksand:400,700');
	wp_register_script('global-js',get_stylesheet_directory_uri().'/assets/js/global.js',array(),NULL,true);
wp_enqueue_script('global-js');

$custom_variables = array( 'template_url' => get_stylesheet_directory_uri() );
wp_localize_script( 'global-js', 'custom_variables', $custom_variables );

}
function msx_theme_add_editor_styles() {
    add_editor_style();
}
add_action( 'admin_init', 'msx_theme_add_editor_styles' );

function museomix_init() {
  load_plugin_textdomain( 'museomix', false, dirname( plugin_basename( __FILE__ ) ) );
}
add_action('plugins_loaded', 'museomix_init');

require_once(dirname(__FILE__).'/includes/custom_post_types.php');
require_once(dirname(__FILE__).'/includes/acf.php');
require_once(dirname(__FILE__).'/includes/divi_modules.php');
require_once(dirname(__FILE__).'/includes/sidebars.php');
require_once(dirname(__FILE__).'/includes/display_functions.php');
require_once(dirname(__FILE__).'/includes/rewrite_rules.php');
require_once(dirname(__FILE__).'/includes/menus.php');
require_once(dirname(__FILE__).'/includes/permalinks.php');
require_once(dirname(__FILE__).'/includes/locations.php');
require_once(dirname(__FILE__).'/includes/taxonomies.php');
require_once(dirname(__FILE__).'/includes/shortcodes.php');

add_image_size('community_image', 528,297, true);
add_image_size('news_thumbnail', 346,200, true);
add_image_size('sponsor_thumbnail', 346,200);
add_image_size('museum_picture', 276, 418, true);
add_image_size('hexa_image', 94, 94, true);
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page();

}


function get_attachment_id_from_url($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
  if (isset($attachment[0])):
    return $attachment[0];
  endif;
  return false;
}

function locations_nopaging( $query ){
    if( ! is_admin()
        && $query->is_post_type_archive( 'museomix' )
        && $query->is_main_query() ){
            $query->set( 'nopaging', true );
            $query->set( 'orderby', 'title' );
            $query->set( 'order', 'ASC' );
    }
}
add_action( 'pre_get_posts', 'locations_nopaging' );



/*
    MENU ANYWHERE
*/
function menu_function($atts, $content = null) {
    extract(
        shortcode_atts(
            array( 'name' => null, ),
            $atts
        )
    );
    return wp_nav_menu(
        array(
            'menu' => $name,
            'echo' => false
        )
    );
}
add_shortcode('menu', 'menu_function');

/* FACET WP PAGINATION */
function my_facetwp_pager_html( $output, $params ) {
    $output = '';
    $page = $params['page'];
    $total_pages = $params['total_pages'];
    

    $text_page      = __( 'Page', 'fwp' );
    $text_of        = __( 'of', 'fwp' );

    // "Page 5 of 150"
    $output .= '<span class="facetwp-pager-label">' . "$text_page $page $text_of $total_pages</span>";
    if ( $page > 1 ) {
        $output .= '<a class="facetwp-page" data-page="' . ($page - 1) . '">&lt;</a>';
    }
    if ( 3 < $page ) {
        $output .= '<a class="facetwp-page first-page" data-page="1">&lt;&lt;</a>';
    }
    if ( 1 < ( $page - 10 ) ) {
        $output .= '<a class="facetwp-page" data-page="' . ($page - 10) . '">' . ($page - 10) . '</a>';
    }
    for ( $i = 2; $i > 0; $i-- ) {
        if ( 0 < ( $page - $i ) ) {
            $output .= '<a class="facetwp-page" data-page="' . ($page - $i) . '">' . ($page - $i) . '</a>';
        }
    }

    // Current page
    $output .= '<a class="facetwp-page active" data-page="' . $page . '">' . $page . '</a>';

    for ( $i = 1; $i <= 2; $i++ ) {
        if ( $total_pages >= ( $page + $i ) ) {
            $output .= '<a class="facetwp-page" data-page="' . ($page + $i) . '">' . ($page + $i) . '</a>';
        }
    }
    if ( $total_pages > ( $page + 10 ) ) {
        $output .= '<a class="facetwp-page" data-page="' . ($page + 10) . '">' . ($page + 10) . '</a>';
    }
    if ( $page < $total_pages && $total_pages > 1 ) {
        $output .= '<a class="facetwp-page" data-page="' . ($page + 1) . '">&gt;</a>';
    }
    if ( $total_pages > ( $page + 2 ) ) {
        $output .= '<a class="facetwp-page last-page" data-page="' . $total_pages . '">&gt;&gt;</a>';
    }
    return $output;
}

add_filter( 'facetwp_pager_html', 'my_facetwp_pager_html', 10, 2 );

function mysite_opengraph_content($val) {
    return preg_replace("/<img[^>]+\>/i", "", $val); 
 }
add_filter('wpseo_pre_analysis_post_content', 'mysite_opengraph_content');

function get_image_id_from_url($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
    return $attachment[0]; 
}