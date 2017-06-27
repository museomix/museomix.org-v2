<?php
add_action('init', 'custom_rewrite_rules', 0);
function custom_rewrite_rules() {
  add_rewrite_rule('^editions/([0-9]+)?/([a-z-0-9]+)$', 'index.php?museomix=$matches[2]', 'top');
  add_rewrite_rule('^editions/([0-9]+)?$', 'index.php?post_type=museomix&edition_year=$matches[1]', 'top');
  add_rewrite_rule('^editions/([0-9]+)/([a-z-0-9]+)/prototypes/([a-z-0-9]+)?', 'index.php?post_type=prototype&pagename=$matches[3]', 'top');
}

//add_filter( 'template_include', 'portfolio_page_template', 10 );

function portfolio_page_template( $template ) {
	if ( isset($wp_query->query['museomix'])) {
		$new_template = locate_template( array( 'single-community.php' ) );
		if ( '' != $new_template ) {
			return $new_template ;
		}
	}

	return $template;
}


function custom_rewrite_tags() {
        add_rewrite_tag('%edition_year%', '([0-9]+)');
    }
add_action('init', 'custom_rewrite_tags', 10, 0);
