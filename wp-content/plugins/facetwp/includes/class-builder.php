<?php

class FacetWP_Builder
{

    public $css = array();
    public $data = array();
    public $custom_css;


    /**
     * Generate the layout HTML
     * @since 3.2.0
     */
    function render_layout( $layout ) {
        global $wp_query;

        $settings = $layout['settings'];
        $this->custom_css = $settings['custom_css'];
        $css_class = empty( $settings['css_class'] ) ? '' : ' ' . $settings['css_class'];

        $this->css['.fwpl-layout'] = array(
            'display' => 'grid',
            'grid-template-columns' => trim( str_repeat( '1fr ', $settings['num_columns'] ) ),
            'grid-gap' => $settings['grid_gap'] . 'px'
        );

        $this->css['.fwpl-result'] = $this->build_styles( $settings );

        $output = '<div class="fwpl-layout">';

        if ( have_posts() ) {
            while ( have_posts() ) : the_post();

                // Prevent short-tags from leaking onto other posts
                $this->data = array();

                $output .= '<div class="fwpl-result ' . $settings['name'] . $css_class . '">';

                foreach ( $layout['items'] as $row ) {
                    $output .= $this->render_row( $row );
                }

                $output .= '</div>';

                $output = $this->short_tags( $output );

            endwhile;
        }

        $output .= '</div>';

        $output .= $this->render_css();
 
        return $output;
    }


    /**
     * Generate the row HTML
     * @since 3.2.0
     */
    function render_row( $row ) {
        $settings = $row['settings'];

        $this->css['.fwpl-row'] = array( 'display' => 'grid' );
        $this->css['.fwpl-row.' . $settings['name'] ] = $this->build_styles( $settings );

        $css_class = empty( $settings['css_class'] ) ? '' : ' ' . $settings['css_class'];
        $output = '<div class="fwpl-row ' . $settings['name'] . $css_class . '">';

        foreach ( $row['items'] as $col ) {
            $output .= $this->render_col( $col );
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Generate the col HTML
     * @since 3.2.0
     */
    function render_col( $col ) {
        $settings = $col['settings'];

        $this->css['.fwpl-col.' . $settings['name'] ] = $this->build_styles( $settings );

        $css_class = empty( $settings['css_class'] ) ? '' : ' ' . $settings['css_class'];
        $output = '<div class="fwpl-col ' . $settings['name'] . $css_class . '">';

        foreach ( $col['items'] as $item ) {
            if ( 'row' == $item['type'] ) {
                $output .= $this->render_row( $item );
            }
            elseif ( 'item' == $item['type'] ) {
                $output .= $this->render_item( $item );
            }
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Generate the item HTML
     * @since 3.2.0
     */
    function render_item( $item ) {
        global $post;

        $settings = $item['settings'];
        $name = $settings['name'];
        $source = $item['source'];
        $value = $source;

        $selector = '.fwpl-item.' . $name;

        if ( 'button' == $source ) {
            $this->css[ $selector . ' button'] = $this->build_styles( $settings );
        }
        else {
            $this->css[ $selector . ",\n" . $selector . ' a' ] = $this->build_styles( $settings );
        }

        if ( 0 === strpos( $source, 'post_' ) || 'ID' == $source ) {
            if ( 'post_title' == $source ) {
                $value = $this->linkify( $post->$source, $settings['link'] );
            }
            elseif ( 'post_excerpt' == $source ) {
                $value = get_the_excerpt( $post->ID );
            }
            elseif ( 'post_content' == $source ) {
                $value = apply_filters( 'the_content', $post->post_content );
            }
            elseif ( 'post_author' == $source ) {
                $field = $settings['author_field'];
                $user = get_user_by( 'id', $post->$source );
                $value = $user->$field;
            }
            elseif ( 'post_type' == $source ) {
                $value = $post->$source;
                $post_type = get_post_type_object( $value );
                if ( isset( $post_type->labels->singular_name ) ) {
                    $value = $post_type->labels->singular_name;
                }
            }
            else {
                $value = $post->$source;
            }
        }
        elseif ( 0 === strpos( $source, 'cf/' ) ) {
            $value = get_post_meta( $post->ID, substr( $source, 3 ), true );
            $value = $this->linkify( $value, $settings['link'] );
        }
        elseif ( 0 === strpos( $source, 'tax/' ) ) {
            $temp = array();
            $taxonomy = substr( $source, 4 );
            $terms = get_the_terms( $post->ID, $taxonomy );

            if ( is_array( $terms ) ) {
                foreach ( $terms as $term_obj ) {
                    $term = $this->linkify( $term_obj->name, $settings['term_link'], array(
                            'term_id' => $term_obj->term_id,
                            'taxonomy' => $taxonomy
                    ) );

                    $temp[] = '<span class="fwpl-term fwpl-term-' . $term_obj->slug . ' fwpl-tax-' . $taxonomy . '">' . $term . '</span>';
                }
            }

            $value = implode( $settings['separator'], $temp );
        }
        elseif ( 0 === strpos( $source, 'acf/' ) ) {
            $field_key = substr( $source, 4 );
            $value = get_field( $field_key, $post->ID );
        }
        elseif ( 'featured_image' == $source ) {
            $value = get_the_post_thumbnail( $post->ID, $settings['image_size'] );
            $value = $this->linkify( $value, $settings['link'] );
        }
        elseif ( 'button' == $source ) {
            $value = '<button>' . $settings['button_text'] . '</button>';
            $value = $this->linkify( $value, $settings['link'] );
        }
        elseif ( 'html' == $source ) {
            $value = $settings['content'];
        }

        // Date format
        if ( ! empty( $settings['date_format'] ) ) {
            if ( ! empty( $settings['input_format'] ) ) {
                $date = DateTime::createFromFormat( $settings['input_format'], $value );
            }
            else {
                $date = new DateTime( $value );
            }

            if ( $date ) {
                $value = $date->format( $settings['date_format'] );
            }
        }

        // Number format
        if ( ! empty( $settings['number_format'] ) ) {
            $decimals = 2;
            $format = $settings['number_format'];
            $decimal_sep = FWP()->helper->get_setting( 'decimal_separator' );
            $thousands_sep = FWP()->helper->get_setting( 'thousands_separator' );

            // No thousands separator
            if ( false === strpos( $format, ',' ) ) {
                $thousands_sep = '';
            }

            // Handle decimals
            if ( false === ( $pos = strpos( $format, '.' ) ) ) {
                $decimals = 0;
            }
            else {
                $decimals = strlen( $format ) - $pos - 1;
            }

            $value = number_format( $value, $decimals, $decimal_sep, $thousands_sep );
        }

        $output = '';
        $prefix = isset( $settings['prefix'] ) ? $settings['prefix'] : '';
        $suffix = isset( $settings['suffix'] ) ? $settings['suffix'] : '';

        // Allow value hooks
        $value = apply_filters( 'facetwp_builder_item_value', $value, $item );

        // Convert array to string
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }

        // Store the RAW short-tag
        $this->data[ "$name:raw" ] = $value;

        // Attach the prefix / suffix to the value
        $value = $prefix . $value . $suffix;

        // Store the short-tag
        $this->data[ $name ] = $value;

        // Prevent output
        if ( ! $settings['is_hidden'] ) {
            $css_class = empty( $settings['css_class'] ) ? '' : ' ' . $settings['css_class'];
            $output = '<div class="fwpl-item ' . $name . $css_class . '">' . $value . '</div>';
        }

        return $output;
    }


    /**
     * Populate short-tag content, e.g. {{ first_name }}
     */
    function short_tags( $output ) {
        foreach ( $this->data as $tag => $tag_value ) {
            $pattern = "/({{ $tag }})/s";
            $tag_value = str_replace( '$', '\$', $tag_value );
            $output = preg_replace( $pattern, $tag_value, $output );
        }

        return $output;
    }


    /**
     * Build the redundant styles (border, padding,etc)
     * @since 3.2.0
     */
    function build_styles( $settings ) {
        $styles = array();

        if ( isset( $settings['grid_template_columns'] ) ) {
            $styles['grid-template-columns'] = $settings['grid_template_columns'];
        }
        if ( isset( $settings['border'] ) ) {
            $styles['border-style'] = $settings['border']['style'];
            $styles['border-color'] = $settings['border']['color'];
            $styles['border-width'] = $this->get_widths( $settings['border']['width'] );
        }
        if ( isset( $settings['background_color'] ) ) {
            $styles['background-color'] = $settings['background_color'];
        }
        if ( isset( $settings['padding'] ) ) {
            $styles['padding'] = $this->get_widths( $settings['padding'] );
        }
        if ( isset( $settings['text_style'] ) ) {
            $styles['text-align'] = $settings['text_style']['align'];
            $styles['font-weight'] = $settings['text_style']['bold'] ? 'bold' : '';
            $styles['font-style'] = $settings['text_style']['italic'] ? 'italic' : '';
        }
        if ( isset( $settings['font_size'] ) ) {
            $styles['font-size'] = $settings['font_size']['size'] . $settings['font_size']['unit'];
        }
        if ( isset( $settings['text_color'] ) ) {
            $styles['color'] = $settings['text_color'];
        }
        if ( isset( $settings['button_border'] ) ) {
            $border = $settings['button_border'];
            $width = $border['width'];
            $unit = $width['unit'];

            $styles['color'] = $settings['button_text_color'];
            $styles['background-color'] = $settings['button_color'];
            $styles['padding'] = $this->get_widths( $settings['button_padding'] );
            $styles['border-style'] = $border['style'];
            $styles['border-color'] = $border['color'];
            $styles['border-top-width'] = $width['top'] . $unit;
            $styles['border-right-width'] = $width['right'] . $unit;
            $styles['border-bottom-width'] = $width['bottom'] . $unit;
            $styles['border-left-width'] = $width['left'] . $unit;
        }

        return $styles;
    }


    /**
     * Build the CSS widths, e.g. for "padding" or "border-width"
     * @since 3.2.0
     */
    function get_widths( $data ) {
        $unit = $data['unit'];
        $top = $data['top'];
        $right = $data['right'];
        $bottom = $data['bottom'];
        $left = $data['left'];

        if ( $top == $right && $right == $bottom && $bottom == $left ) {
            return "$top$unit";
        }
        elseif ( $top == $bottom && $left == $right ) {
            return "$top$unit $left$unit";
        }

        return "$top$unit $right$unit $bottom$unit $left$unit";
    }


    /**
     * Convert a value into a link
     * @since 3.2.0
     */
    function linkify( $value, $link_data, $term_data = array() ) {
        global $post;

        $type = $link_data['type'];
        $href = $link_data['href'];
        $target = $link_data['target'];

        if ( 'none' !== $type ) {
            if ( 'post' == $type ) {
                $href = get_permalink();
            }
            if ( 'term' == $type ) {
                $href = get_term_link( $term_data['term_id'], $term_data['taxonomy'] );
            }

            $value = '<a href="' . $href . '" target="' . $target . '">' . $value . '</a>';
        }

        return $value;
    }


    /**
     * Turn the CSS array into valid CSS
     * @since 3.2.0
     */
    function render_css() {
        $output = "\n<style>\n";

        foreach ( $this->css as $selector => $props ) {
            $valid_rules = $this->get_valid_css_rules( $props );

            if ( ! empty( $valid_rules ) ) {
                $output .= $selector . " {\n";
                foreach ( $valid_rules as $prop => $value ) {
                    $output .= "    $prop: $value;\n";
                }
                $output .= "}\n";
            }
        }

        if ( ! empty( $this->custom_css ) ) {
            $output .= $this->custom_css . "\n";
        }

        $output .= "
@media (max-width: 480px) {
    .fwpl-layout {
        grid-template-columns: 1fr;
    }
}
";

        $output .= "</style>\n";

        return $output;
    }


    /**
     * Filter out empty or invalid rules
     * @since 3.2.0
     */
    function get_valid_css_rules( $props ) {
        $rules = array();

        foreach ( $props as $prop => $value ) {
            if ( $this->is_valid_css_rule( $prop, $value ) ) {
                $rules[ $prop ] = $value;
            }
        }

        return $rules;
    }


    /**
     * Optimize CSS rules
     * @since 3.2.0
     */
    function is_valid_css_rule( $prop, $value ) {
        $return = true;

        if ( empty( $value ) || 'px' === $value || '0px' === $value || 'none' === $value ) {
            $return = false;
        }

        if ( 'font-size' === $prop && '0px' === $value ) {
            $return = false;
        }

        return $return;
    }


    /**
     * Make sure the query is valid
     * @since 3.2.0
     */
    function parse_query_obj( $query_obj ) {
        $tax_query = array();
        $meta_query = array();
        $date_query = array();
        $post_type = array();
        $post_status = array( 'publish' );
        $post_in = array();
        $post_not_in = array();
        $author_in = array();
        $author_not_in = array();
        $orderby = array();

        foreach ( $query_obj['post_type'] as $data ) {
            $post_type[] = $data['value'];
        }

        if ( empty( $post_type ) ) {
            $post_type = 'any';
        }

        foreach ( $query_obj['filters'] as $filter ) {
            $key = $filter['key'];
            $value = $filter['value'];
            $compare = $filter['compare'];
            $type = $filter['type'];

            $in_clause = in_array( $compare, array( 'IN', 'NOT IN' ) );
            $exists_clause = in_array( $compare, array( 'EXISTS', 'NOT EXISTS' ) );

            if ( empty( $value ) && ! $exists_clause ) {
                continue;
            }

            if ( ! $in_clause ) {
                $value = $exists_clause ? '' : $value[0];
            }

            if ( 'ID' == $key ) {
                if ( 'IN' == $compare ) {
                    $post_in = $value;
                }
                else {
                    $post_not_in = $value;
                }
            }
            elseif ( 'post_author' == $key ) {
                if ( 'IN' == $compare ) {
                    $author_in = $value;
                }
                else {
                    $author_not_in = $value;
                }
            }
            elseif ( 'post_status' == $key ) {
                $post_status = $value;
            }
            elseif ( 'post_date' == $key || 'post_modified' == $key ) {
                if ( '>' == $compare || '>=' == $compare ) {
                    $date_query[] = array(
                        'after' => $value,
                        'inclusive' => ( '>=' == $compare )
                    );
                }
                if ( '<' == $compare || '<=' == $compare ) {
                    $date_query[] = array(
                        'before' => $value,
                        'inclusive' => ( '<=' == $compare )
                    );
                }
            }
            elseif ( 0 === strpos( $key, 'tax/' ) ) {
                $temp = array(
                    'taxonomy' => substr( $key, 4 ),
                    'field' => 'slug',
                    'operator' => $compare
                );

                if ( ! $exists_clause ) {
                    $temp['terms'] = $value;
                }

                $tax_query[] = $temp;
            }
            else {
                $temp = array(
                    'key' => substr( $key, strpos( $key, '/' ) + 1 ),
                    'compare' => $compare,
                    'type' => $type
                );

                if ( ! $exists_clause ) {
                    $temp['value'] = $value;
                }

                $meta_query[] = $temp;
            }
        }

        foreach ( $query_obj['orderby'] as $index => $data ) {
            if ( 'cf/' == substr( $data['key'], 0, 3 ) ) {
                $meta_query['sort_' . $index] = array(
                    'key' => substr( $data['key'], 3 ),
                    'type' => $data['type']
                );

                $orderby['sort_' . $index] = $data['order'];
            }
            else {
                $orderby[ $data['key'] ] = $data['order'];
            }
        }

        $output = array(
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
            'date_query' => $date_query,
            'author__in' => $author_in,
            'author__not_in' => $author_not_in,
            'post__in' => $post_in,
            'post__not_in' => $post_not_in,
            'posts_per_page' => $query_obj['posts_per_page'],
            'post_status' => $post_status,
            'post_type' => $post_type,
            'orderby' => $orderby
        );

        return $output;
    }


    /**
     * Get necessary values for the layout builder
     * @since 3.2.0
     */
    function get_layout_data() {
        $sources = FWP()->helper->get_data_sources();
        unset( $sources['post'] );

        // Static options
        $output = array(
            'row' => 'Child Row',
            'html' => 'HTML',
            'button' => 'Button',
            'featured_image' => 'Featured Image',
            'ID' => 'Post ID',
            'post_title' => 'Post Title',
            'post_name' => 'Post Name',
            'post_content' => 'Post Content',
            'post_excerpt' => 'Post Excerpt',
            'post_date' => 'Post Date',
            'post_modified' => 'Post Modified',
            'post_author' => 'Post Author',
            'post_type' => 'Post Type'
        );

        foreach ( $sources as $group ) {
            foreach ( $group['choices'] as $name => $label ) {
                $output[ $name ] = $label;
            }
        }

        return $output;
    }


    /**
     * Get necessary data for the query builder
     * @since 3.0.0
     */
    function get_query_data() {
        $builder_post_types = array();

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $data_sources = FWP()->helper->get_data_sources();

        foreach ( $post_types as $type ) {
            $builder_post_types[] = array(
                'label' => $type->labels->name,
                'value' => $type->name
            );
        }

        $data_sources['posts']['choices'] = array(
            'ID' => 'ID',
            'post_author' => 'Post Author',
            'post_status' => 'Post Status',
            'post_date' => 'Post Date',
            'post_modified' => 'Post Modified'
        );

        return array(
            'post_types' => $builder_post_types,
            'filter_by' => $data_sources
        );
    }
}
