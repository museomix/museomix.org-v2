<?php

class FacetWP_Facet_Radio_Core extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Radio', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause = $wpdb->prefix . 'facetwp_index f';

        // Facet in "OR" mode
        $where_clause = $this->get_where_clause( $facet );

        // Orderby
        $orderby = $this->get_orderby( $facet );

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
        $from_clause = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 20;

        $sql = "
        SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, COUNT(DISTINCT f.post_id) AS counter
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        $output = $wpdb->get_results( $sql, ARRAY_A );

        // Show "ghost" facet choices
        // For performance gains, only run if facets are in use
        $show_ghosts = FWP()->helper->facet_is( $facet, 'ghosts', 'yes' );
        $is_filtered = FWP()->unfiltered_post_ids !== FWP()->facet->query_args['post__in'];

        if ( $show_ghosts && $is_filtered && ! empty( FWP()->unfiltered_post_ids ) ) {
            $raw_post_ids = implode( ',', FWP()->unfiltered_post_ids );

            $sql = "
            SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, 0 AS counter
            FROM $from_clause
            WHERE f.facet_name = '{$facet['name']}' AND post_id IN ($raw_post_ids)
            GROUP BY f.facet_value
            ORDER BY $orderby
            LIMIT $limit";

            $ghost_output = $wpdb->get_results( $sql, ARRAY_A );

            // Keep the facet placement intact
            if ( FWP()->helper->facet_is( $facet, 'preserve_ghosts', 'yes' ) ) {
                $tmp = [];
                foreach ( $ghost_output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                $output = $tmp;
            }
            else {
                // Make the array key equal to the facet_value (for easy lookup)
                $tmp = [];
                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row; // Force a string array key
                }
                $output = $tmp;

                foreach ( $ghost_output as $row ) {
                    $facet_value = $row['facet_value'];
                    if ( ! isset( $output[ "$facet_value " ] ) ) {
                        $output[ "$facet_value " ] = $row;
                    }
                }
            }

            $output = array_splice( $output, 0, $limit );
            $output = array_values( $output );
        }

        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $facet = $params['facet'];

        $output = '';
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        $key = 0;
        foreach ( $values as $key => $result ) {
            $selected = in_array( $result['facet_value'], $selected_values ) ? ' checked' : '';
            $selected .= ( 0 == $result['counter'] && '' == $selected ) ? ' disabled' : '';
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="' . esc_attr( $result['facet_value'] ) . '">';
            $output .= esc_html( $result['facet_display_value'] ) . ' <span class="facetwp-counter">(' . $result['counter'] . ')</span>';
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value IN ('$selected_values')";
        return facetwp_sql( $sql, $facet );
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <div class="facetwp-row" v-show="facet.source.substr(0, 3) == 'tax'">
            <div>
                <?php _e('Parent term', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        To show only child terms, enter the parent <a href="https://facetwp.com/how-to-find-a-wordpress-terms-id/" target="_blank">term ID</a>.
                        Otherwise, leave blank.
                    </div>
                </div>
            </div>
            <div>
                <input type="text" class="facet-parent-term" />
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Show ghosts', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Show choices that would return zero results?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.ghosts == 'yes'">
            <div>
                <?php _e('Preserve ghost order', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Keep ghost choices in the same order?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-preserve-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row">
            <div><?php _e('Sort by', 'fwp'); ?>:</div>
            <div>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Highest Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                    <option value="term_order"><?php _e( 'Term Order', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Count', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <input type="text" class="facet-count" value="20" />
                <input type="hidden" class="facet-operator" value="or" />
            </div>
        </div>
<?php
    }
}
