<?php

add_action( 'after_setup_theme', 'register_menus' );
function register_menus() {
  register_nav_menu( 'community-page', __( 'Community page', 'museomix' ) );
  $menu_name = __('Community menu','museomix');
  $menu_id = wp_create_nav_menu($menu_name);

  register_nav_menu( 'location-page', __( 'Location page', 'museomix' ) );
  $menu_name = __('Location menu','museomix');
  $menu_id = wp_create_nav_menu($menu_name);

  register_nav_menu( 'prototype-page', __( 'Prototype page', 'museomix' ) );
  $menu_name = __('Prototype menu','museomix');
  $menu_id = wp_create_nav_menu($menu_name);

}


function add_subitems_to_menu( $menu_name, $parent_object_id, $subitems ) {
    // Don't add anything in admin area. Otherwise WP will try to display the items in the
    // Menu editor and it won't work fine and cause strange behaviour
    if ( is_admin() ) {
        return;
    }

    // Use wp_get_nav_menu_items filter, is used by Timber to get WP menu items
    add_filter( 'wp_get_nav_menu_items', function( $items, $menu )
            use( $menu_name, $parent_object_id, $subitems ) {
        // If no menu found, just return the items without adding anything
        if ( $menu->name != $menu_name && $menu->slug != $menu_name ) {
          echo $menu->name;

            return $items;
        }

        // Find the menu item ID corresponding to the given post/page object ID
        // If no post/page found, the subitems won't have any parent (will be on 1st level)
        $parent_menu_item_id = 0;
        foreach ( $items as $item ) {
            if ( $parent_object_id == $item->object_id ) {
                $parent_menu_item_id = $item->ID;
                break;
            }
        }

        $menu_order = count( $items ) + 1;

        foreach ( $subitems as $subitem ) {
            // Create objects containing all (and only) those properties from WP_Post
            // used by WP to create a menu item
            $items[] = (object) array(
                'ID'                => $menu_order + 1000, // ID that WP won't use
                'title'             => $subitem['text'],
                'url'               => $subitem['url'],
                'menu_item_parent'  => $parent_menu_item_id,
                'menu_order'        => $menu_order,
                // These are not necessary, but PHP warning will be thrown if undefined
                'type'              => '',
                'object'            => '',
                'object_id'         => '',
                'db_id'             => '',
                'classes'           => '',
            );
            $menu_order++;
        }
        return $items;
    }, 0, 2);
}

function get_wp_object_id( $post_identifier, $post_type = 'page' ) {

    $post_id = 0;

    if ( get_page_by_title( $post_identifier, OBJECT, $post_type ) ) {
        $post_id = get_page_by_title( $post_identifier, OBJECT, $post_type )->ID;
    }
    else if ( get_page_by_path( $post_identifier, OBJECT, $post_type ) ) {
        $post_id = get_page_by_path( $post_identifier, OBJECT, $post_type )->ID;
    }
    else if ( get_post( $post_identifier ) ) {
        $post_id = get_post( $post_identifier )->ID;
    }

    return $post_id;
}
