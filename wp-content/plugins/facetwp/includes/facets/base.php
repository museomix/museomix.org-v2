<?php

class FacetWP_Facet
{

    /**
     * Grab the orderby, as needed by several facet types
     * @since 3.0.4
     */
    function get_orderby( $facet ) {
        $key = $facet['orderby'];

        // Count (default)
        $orderby = 'counter DESC, f.facet_display_value ASC';

        // Display value
        if ( 'display_value' == $key ) {
            $orderby = 'f.facet_display_value ASC';
        }
        // Raw value
        elseif ( 'raw_value' == $key ) {
            $orderby = 'f.facet_value ASC';
        }
        // Term order
        elseif ('term_order' == $key && 'tax' == substr( $facet['source'], 0, 3 ) ) {
            $term_ids = get_terms( [
                'taxonomy' => str_replace( 'tax/', '', $facet['source'] ),
                'fields' => 'ids',
            ] );

            if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
                $term_ids = implode( ',', $term_ids );
                $orderby = "FIELD(f.term_id, $term_ids)";
            }
        }

        // Sort by depth just in case
        $orderby = "f.depth, $orderby";

        return $orderby;
    }


    /**
     * Adjust the $where_clause for facets in "OR" mode
     *
     * FWP()->or_values contains EVERY facet and their matching post IDs
     * FWP()->unfiltered_post_ids contains original post IDs
     *
     * @since 3.2.0
     */
    function get_where_clause( $facet ) {

        // If no results, empty the facet
        if ( 0 === FWP()->facet->query->found_posts ) {
            $post_ids = [];
        }

        // Ignore the current facet's selections
        elseif ( isset( FWP()->or_values ) && ( 1 < count( FWP()->or_values ) || ! isset( FWP()->or_values[ $facet['name'] ] ) ) ) {
            $post_ids = [];
            $or_values = FWP()->or_values; // Preserve the original
            unset( $or_values[ $facet['name'] ] );

            $counter = 0;
            foreach ( $or_values as $name => $vals ) {
                $post_ids = ( 0 == $counter ) ? $vals : array_intersect( $post_ids, $vals );
                $counter++;
            }

            $post_ids = array_intersect( $post_ids, FWP()->unfiltered_post_ids );
        }

        // Default
        else {
            $post_ids = FWP()->unfiltered_post_ids;
        }

        $post_ids = empty( $post_ids ) ? [ 0 ] : $post_ids;
        return ' AND post_id IN (' . implode( ',', $post_ids ) . ')';
    }
}
