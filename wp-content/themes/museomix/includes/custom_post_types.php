<?php


function creer_posts_types(){
	TypesPagesEditions();
	TypesPagesMuseomix();
	TypesPagesMuseum();
	TypesPagesPrototype();
	TypesPagesSponsor();
	communities_post_type();
}
add_action( 'init', 'creer_posts_types' );

/* Post_types Editions
   =================== */
function TypesPagesEditions(){
	register_post_type(
		'edition',
		array(
			'labels' => array(
				'name' => __('Edition','museomix'),
				'singular_name' => __('Edition','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add an edition','museomix'),
				'edit_item' => __('Edit edition','museomix'),
				'new_item' => __('New edition','museomix'),
				'all_items' => __('All editions','museomix'),
				'view_item' => __('Show edition','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' => __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Editions','museomix')
			),
			'public' => true,
			'menu_position' => 20,
			'hierarchical' => true,
			'supports' => array('title'),
			'has_archive' => true,
			'rewrite' => array('slug'=>'msx_edition'),
			'supports' => array('title','editor'),
			'capability_type' => 'page',
		)
	);
}

/* Post_types Museomix
   =================== */
function TypesPagesMuseomix(){
	register_post_type(
		'museomix',
		array(
			'labels' => array(
				'name' => __('Locations','museomix'),
				'singular_name' => __('Location','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add a location','museomix'),
				'edit_item' => __('Edit the location','museomix'),
				'new_item' => __('New','museomix'),
				'all_items' => __('All the locations','museomix'),
				'view_item' => __('View the location','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' =>  __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Locations','museomix')
			),
			'public' => true,
			'hierarchical' => true,
			'has_archive' => true,
			'rewrite' => false,
			//'rewrite' => array('slug'=>'localisation'),
			'supports' => array('title','editor','author','revisions'),
			#'map_meta_cap' => true,
		)
	);
}


/* Post_types Musées
   ================= */
function TypesPagesMuseum(){
	register_post_type(
		'museum',
		array(
			'labels' => array(
				'name' => __('Museum','museomix'),
				'singular_name' => __('Museum','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add a museum','museomix'),
				'edit_item' => __('Edit the museum','museomix'),
				'new_item' => __('New','museomix'),
				'all_items' => __('All museums','museomix'),
				'view_item' => __('Show museum','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' =>  __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Museums','museomix')
			),
			'public' => true,
			'hierarchical' => true,
			'has_archive' => true,
			'menu_position' => 7,
			'show_ui' => true,
			'rewrite' => array('slug'=>'museums'),
			'supports' => array('title','editor'),
		)
	);
}


/* Post_types Prototype
   ==================== */
function TypesPagesPrototype(){
	register_post_type(
		'prototype',
		array(
			'labels' => array(
				'name' => __('Prototype','museomix'),
				'singular_name' =>	__('Prototype','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add a prototype','museomix'),
				'edit_item' => __('Edit the prototype','museomix'),
				'new_item' => __('New','museomix'),
				'all_items' => __('All prototypes','museomix'),
				'view_item' => __('Show prototype','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' =>  __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Prototypes','museomix')
			),
			'public' => true,
			'hierarchical' => true,
			'has_archive' => true,
			'rewrite' => array('slug'=>'prototypes'),
			'taxonomies' => array('post_tag', 'theme_prototype', 'technologie', 'perimetre'),
			'supports' => array('title', 'editor', 'author', 'thumbnail','revisions'),
		)
	);
}
/* Post_types Sponsors
   =================== */
function TypesPagesSponsor(){
	register_post_type(
		'sponsor',
		array(
			'labels' => array(
				'name' => __('Partner','museomix'),
				'singular_name' => __('Partner','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add a partner','museomix'),
				'edit_item' => __('Edit partner','museomix'),
				'new_item' =>	__('New','museomix'),
				'all_items' => __('All partners','museomix'),
				'view_item' => __('Show partner','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' =>  __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Partners','museomix')
			),
			'public' => true,
			'show_ui' => true,
			'hierarchical' => true,
			'has_archive' => true,
			'rewrite' => array('slug'=>'partenaires'),
			'supports' => array('title','editor'),
		)
	);
}

/* Communities post type */
function communities_post_type(){
	register_post_type(
		'community',
		array(
			'labels' => array(
				'name' => __('Communities','museomix'),
				'singular_name' => __('Community','museomix'),
				'add_new' => __('Add','museomix'),
				'add_new_item' => __('Add an community','museomix'),
				'edit_item' => __('Edit community','museomix'),
				'new_item' => __('New community','museomix'),
				'all_items' => __('All communities','museomix'),
				'view_item' => __('Show community','museomix'),
				'search_items' => __('Search','museomix'),
				'not_found' => __('No element','museomix'),
				'not_found_in_trash' => __('No element in trash','museomix'),
				'parent_item_colon' => '',
				'menu_name' => __('Communities','museomix')
			),
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'menu_position' => 8,
			'supports' => array('title'),
			'has_archive' => false,
			'supports' => array('title','editor'),
			'capability_type' => 'page',

		)
	);
}


/* renommer post en news
   ===================== */
add_action( 'init', 'NewsMenu' );
add_action( 'admin_menu', 'RenommerMenuPost' );

function RenommerMenuPost(){
	global $menu;
	global $submenu;
	$menu[5][0] = __('News','museomix');
	$submenu['edit.php'][5][0] = __('All news','museomix');
	$submenu['edit.php'][10][0] = __('Add','museomix');
	$submenu['edit.php'][16][0] = __('keywords','museomix');
}

function NewsMenu() {
	global $wp_post_types;
	$labels = &$wp_post_types['post']->labels;
	$labels->name = __('News','museomix');
	$labels->singular_name = __('News','museomix');
	$labels->add_new = __('Add','museomix');
	$labels->add_new_item = __('Add','museomix');
	$labels->edit_item = __('Edit','museomix');
	$labels->new_item = __('New','museomix');
	$labels->all_item = __('All news','museomix');
	$labels->view_item = __('Show','museomix');
	$labels->search_items = __('Search','museomix');
	$labels->not_found = __('No element','museomix');
	$labels->not_found_in_trash = __('No element in trash','museomix');
	$labels->menu_name = __('News','museomix');
}
