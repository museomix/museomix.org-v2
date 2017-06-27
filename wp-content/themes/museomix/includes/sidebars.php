<?php

add_action( 'widgets_init', 'sidebars_declaration' );
function sidebars_declaration() {
    register_sidebar( array(
        'name' => __( 'Footer left sidebar', 'museomix' ),
        'id' => 'footer-left',
        'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>',
    ) );
	 register_sidebar( array(
        'name' => __( 'Footer center sidebar', 'museomix' ),
        'id' => 'footer-center',
        'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>',
    ) );
	 register_sidebar( array(
        'name' => __( 'Footer right sidebar', 'museomix' ),
        'id' => 'footer-right',
        'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>',
    ) );
}