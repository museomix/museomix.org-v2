<?php

add_filter('bcn_template_tags', 'locations_pages_alter_breadcrumbs', 10, 3);
function locations_pages_alter_breadcrumbs($replacements, $type, $id) {
  global $post;
  if (is_singular('museomix')) {
    $edition = get_field('edition', $post);

    /* Edition page */
    $replacements['%htitle_edition_year%'] = $edition->post_title;
    $replacements['%link_edition%'] = icl_get_home_url().'editions/'.$edition->post_title;

    /* Concept page */
    $concept_page_id = apply_filters(
      'wpml_object_id',
      get_field('concept_page', 'option'),
      'page',
      true
    );
    $concept_page = get_post($concept_page_id);
    $replacements['%htitle_concept_page%'] = $concept_page->post_title;
    $replacements['%link_concept_page%'] = get_the_permalink($concept_page);

  }
  if (is_singular('prototype')) {
	$location = get_field('museomix');
    $edition = get_field('edition', $location->ID);

    /* Edition page */
    $replacements['%htitle_edition_year%'] = $edition->post_title;
    $replacements['%link_edition%'] = icl_get_home_url().'editions/'.$edition->post_title;

	/* Location page */
    $replacements['%htitle_location%'] = $location->post_title;
    $replacements['%link_location%'] = icl_get_home_url().'editions/'.$edition->post_title.'/'.$location->post_name;

    /* Concept page */
    $concept_page_id = apply_filters(
      'wpml_object_id',
      get_field('concept_page', 'option'),
      'page',
      true
    );
    $concept_page = get_post($concept_page_id);
    $replacements['%htitle_concept_page%'] = $concept_page->post_title;
    $replacements['%link_concept_page%'] = get_the_permalink($concept_page);

  }
  return $replacements;
}

/* Filter locations by year */
function featured_posts( $query ) {
	global $wp_query;
	if (isset($wp_query->query_vars['edition_year']) && $query->is_main_query() && is_post_type_archive('museomix')):
		$edition =   get_page_by_title($wp_query->query_vars['edition_year'], OBJECT, 'edition');
		$meta_query = array(
			array(
				'key'     => 'edition',
				'value'   => $edition->ID,
				'compare' => '=',
			),
	 );
	 $query->set('meta_query',$meta_query);
    $query->set('posts_per_page', -1);
 endif;
}
add_action( 'pre_get_posts', 'featured_posts' );
