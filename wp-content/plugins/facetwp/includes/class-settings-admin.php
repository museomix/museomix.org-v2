<?php

class FacetWP_Settings_Admin
{

    /**
     * Get the field settings array
     * @since 3.0.0
     */
    function get_settings() {

        $defaults = [
            'general' => [
                'label' => __( 'General', 'fwp' ),
                'fields' => [
                    'license_key' => [
                        'label' => __( 'License Key', 'fwp' ),
                        'html' => $this->get_field_html( 'license_key' )
                    ],
                    'gmaps_api_key' => [
                        'label' => __( 'Google Maps API Key', 'fwp' ),
                        'html' => $this->get_field_html( 'gmaps_api_key' )
                    ],
                    'separators' => [
                        'label' => __( 'Separators', 'fwp' ),
                        'html' => $this->get_field_html( 'separators' )
                    ],
                    'loading_animation' => [
                        'label' => __( 'Loading Animation', 'fwp' ),
                        'html' => $this->get_field_html( 'loading_animation', 'dropdown', [
                            'choices' => [ 'fade' => __( 'Fade', 'fwp' ), '' => __( 'Spin', 'fwp' ), 'none' => __( 'None', 'fwp' ) ]
                        ] )
                    ],
                    'prefix' => [
                        'label' => __( 'URL Prefix', 'fwp' ),
                        'html' => $this->get_field_html( 'prefix', 'dropdown', [
                            'choices' => [ 'fwp_' => 'fwp_', '_' => '_' ]
                        ] )
                    ],
                    'debug_mode' => [
                        'label' => __( 'Debug Mode', 'fwp' ),
                        'html' => $this->get_field_html( 'debug_mode', 'toggle', [
                            'true_value' => 'on',
                            'false_value' => 'off'
                        ] )
                    ]
                ]
            ],
            'woocommerce' => [
                'label' => __( 'WooCommerce', 'fwp' ),
                'fields' => [
                    'wc_enable_variations' => [
                        'label' => __( 'Support product variations?', 'fwp' ),
                        'notes' => __( 'Enable if your store uses variable products.', 'fwp' ),
                        'html' => $this->get_field_html( 'wc_enable_variations', 'toggle' )
                    ],
                    'wc_index_all' => [
                        'label' => __( 'Include all products?', 'fwp' ),
                        'notes' => __( 'Show facet choices for out-of-stock products?', 'fwp' ),
                        'html' => $this->get_field_html( 'wc_index_all', 'toggle' )
                    ]
                ]
            ],
            'backup' => [
                'label' => __( 'Backup', 'fwp' ),
                'fields' => [
                    'export' => [
                        'label' => __( 'Export', 'fwp' ),
                        'html' => $this->get_field_html( 'export' )
                    ],
                    'import' => [
                        'label' => __( 'Import', 'fwp' ),
                        'html' => $this->get_field_html( 'import' )
                    ]
                ]
            ]
        ];

        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            unset( $defaults['woocommerce'] );
        }

        return apply_filters( 'facetwp_settings_admin', $defaults, $this );
    }


    /**
     * Return HTML for a setting field
     * @since 3.0.0
     */
    function get_field_html( $setting_name, $field_type = 'text', $atts = [] ) {
        ob_start();

        if ( 'license_key' == $setting_name ) : ?>

        <input type="text" class="facetwp-license" style="width:300px" value="<?php echo FWP()->helper->get_license_key(); ?>"<?php echo defined( 'FACETWP_LICENSE_KEY' ) ? ' disabled' : ''; ?> />
        <div @click="activate" class="btn-normal btn-gray btn-small"><?php _e( 'Activate', 'fwp' ); ?></div>
        <div class="facetwp-activation-status field-notes"><?php echo $this->get_activation_status(); ?></div>

<?php elseif ( 'gmaps_api_key' == $setting_name ) : ?>

        <input type="text" v-model="app.settings.gmaps_api_key" style="width:300px" />
        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php _e( 'Get an API key', 'fwp' ); ?></a>

<?php elseif ( 'separators' == $setting_name ) : ?>

        34
        <input type="text" v-model="app.settings.thousands_separator" style="width:20px" />
        567
        <input type="text" v-model="app.settings.decimal_separator" style="width:20px" />
        89

<?php elseif ( 'export' == $setting_name ) : ?>

        <select class="export-items" multiple="multiple" style="width:250px; height:100px">
            <?php foreach ( $this->get_export_choices() as $val => $label ) : ?>
            <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="btn-normal btn-gray export-submit">
            <?php _e( 'Export', 'fwp' ); ?>
        </div>

<?php elseif ( 'import' == $setting_name ) : ?>

        <div><textarea class="import-code" placeholder="<?php _e( 'Paste the import code here', 'fwp' ); ?>"></textarea></div>
        <div><input type="checkbox" class="import-overwrite" /> <?php _e( 'Overwrite existing items?', 'fwp' ); ?></div>
        <div style="margin-top:5px">
            <div class="btn-normal btn-gray import-submit"><?php _e( 'Import', 'fwp' ); ?></div>
        </div>

<?php elseif ( 'dropdown' == $field_type ) : ?>

        <select class="facetwp-setting slim" v-model="app.settings.<?php echo $setting_name; ?>">
            <?php foreach ( $atts['choices'] as $val => $label ) : ?>
            <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>

<?php elseif ( 'toggle' == $field_type ) : ?>
<?php

$true_value = isset( $atts['true_value'] ) ? $atts['true_value'] : 'yes';
$false_value = isset( $atts['false_value'] ) ? $atts['false_value'] : 'no';

?>
        <label class="facetwp-switch">
            <input
                type="checkbox"
                v-model="app.settings.<?php echo $setting_name; ?>"
                true-value="<?php echo $true_value; ?>"
                false-value="<?php echo $false_value; ?>"
            />
            <span class="facetwp-slider"></span>
        </label>

<?php endif;

        return ob_get_clean();
    }


    /**
     * Get an array of all facets and templates
     * @since 3.0.0
     */
    function get_export_choices() {
        $export = [];

        $settings = FWP()->helper->settings;

        foreach ( $settings['facets'] as $facet ) {
            $export['facet-' . $facet['name']] = 'Facet - ' . $facet['label'];
        }

        foreach ( $settings['templates'] as $template ) {
            $export['template-' . $template['name']] = 'Template - '. $template['label'];
        }

        return $export;
    }


    /**
     * Get the activation status
     * @since 3.0.0
     */
    function get_activation_status() {
        $message = __( 'Not yet activated', 'fwp' );
        $activation = get_option( 'facetwp_activation' );

        if ( ! empty( $activation ) ) {
            $activation = json_decode( $activation );
            if ( 'success' == $activation->status ) {
                $message = __( 'License active', 'fwp' );
                $message .= ' (' . __( 'expires', 'fwp' ) . ' ' . date( 'M j, Y', strtotime( $activation->expiration ) ) . ')';
            }
            else {
                $message = $activation->message;
            }
        }

        return $message;
    }


    /**
     * Load i18n admin strings
     * @since 3.2.0
     */
    function get_i18n_strings() {
        return [
            'Results per row' => __( 'Results per row', 'fwp' ),
            'Grid gap' => __( 'Grid gap', 'fwp' ),
            'Text style' => __( 'Text style', 'fwp' ),
            'Text color' => __( 'Text color', 'fwp' ),
            'Font size' => __( 'Font size', 'fwp' ),
            'Background color' => __( 'Background color', 'fwp' ),
            'Border' => __( 'Border', 'fwp' ),
            'Border style' => __( 'Border style', 'fwp' ),
            'None' => __( 'None', 'fwp' ),
            'Solid' => __( 'Solid', 'fwp' ),
            'Dashed' => __( 'Dashed', 'fwp' ),
            'Dotted' => __( 'Dotted', 'fwp' ),
            'Double' => __( 'Double', 'fwp' ),
            'Border color' => __( 'Border color', 'fwp' ),
            'Border width' => __( 'Border width', 'fwp' ),
            'Button text' => __( 'Button text', 'fwp' ),
            'Button text color' => __( 'Button text color', 'fwp' ),
            'Button padding' => __( 'Button padding', 'fwp' ),
            'Separator' => __( 'Separator', 'fwp' ),
            'Custom CSS' => __( 'Custom CSS', 'fwp' ),
            'Column widths' => __( 'Column widths', 'fwp' ),
            'Content' => __( 'Content', 'fwp' ),
            'Image size' => __( 'Image size', 'fwp' ),
            'Author field' => __( 'Author field', 'fwp' ),
            'Display name' => __( 'Display name', 'fwp' ),
            'User login' => __( 'User login', 'fwp' ),
            'User ID' => __( 'User ID', 'fwp' ),
            'Field type' => __( 'Field type', 'fwp' ),
            'Text' => __( 'Text', 'fwp' ),
            'Date' => __( 'Date', 'fwp' ),
            'Number' => __( 'Number', 'fwp' ),
            'Date format' => __( 'Date format', 'fwp' ),
            'Input format' => __( 'Input format', 'fwp' ),
            'Number format' => __( 'Number format', 'fwp' ),
            'Link' => __( 'Link', 'fwp' ),
            'Link type' => __( 'Link type', 'fwp' ),
            'Post URL' => __( 'Post URL', 'fwp' ),
            'Custom URL' => __( 'Custom URL', 'fwp' ),
            'Open in new tab?' => __( 'Open in new tab?', 'fwp' ),
            'Prefix' => __( 'Prefix', 'fwp' ),
            'Suffix' => __( 'Suffix', 'fwp' ),
            'Hide item?' => __( 'Hide item?', 'fwp' ),
            'Padding' => __( 'Padding', 'fwp' ),
            'Unique name' => __( 'Unique name', 'fwp' ),
            'CSS class' => __( 'CSS class', 'fwp' ),
            'Button Border' => __( 'Button border', 'fwp' ),
            'Term URL' => __( 'Term URL', 'fwp' ),
            'Fetch' => __( 'Fetch', 'fwp' ),
            'All post types' => __( 'All post types', 'fwp' ),
            'and show' => __( 'and show', 'fwp' ),
            'per page' => __( 'per page', 'fwp' ),
            'Sort by' => __( 'Sort by', 'fwp' ),
            'Posts' => __( 'Posts', 'fwp' ),
            'Post Title' => __( 'Post Title', 'fwp' ),
            'Post Name' => __( 'Post Name', 'fwp' ),
            'Post Type' => __( 'Post Type', 'fwp' ),
            'Post Date' => __( 'Post Date', 'fwp' ),
            'Post Modified' => __( 'Post Modified', 'fwp' ),
            'Menu Order' => __( 'Menu Order', 'fwp' ),
            'Custom Fields' => __( 'Custom Fields', 'fwp' ),
            'Narrow results by' => __( 'Narrow results by', 'fwp' ),
            'Hit Enter' => __( 'Hit Enter', 'fwp' ),
            'Add sort' => __( 'Add sort', 'fwp' ),
            'Add filter' => __( 'Add filter', 'fwp' ),
            'Enter term slugs' => __( 'Enter term slugs', 'fwp' ),
            'Enter values' => __( 'Enter values', 'fwp' ),
            'Layout' => __( 'Layout', 'fwp' ),
            'Content' => __( 'Content', 'fwp' ),
            'Style' => __( 'Style', 'fwp' ),
            'Advanced' => __( 'Advanced', 'fwp' ),
            'Row' => __( 'Row', 'fwp' ),
            'Column' => __( 'Column', 'fwp' ),
            'Start typing' => __( 'Start typing', 'fwp' ),
            'Label' => __( 'Label', 'fwp' ),
            'Name' => __( 'Name', 'fwp' ),
            'Facet type' => __( 'Facet type', 'fwp' ),
            'Copy shortcode' => __( 'Copy shortcode', 'fwp' ),
            'Data source' => __( 'Data source', 'fwp' ),
            'Switch to advanced mode' => __( 'Switch to advanced mode', 'fwp' ),
            'Switch to visual mode' => __( 'Switch to visual mode', 'fwp' ),
            'Display' => __( 'Display', 'fwp' ),
            'Query' => __( 'Query', 'fwp' ),
            'Help' => __( 'Help', 'fwp' ),
            'Display Code' => __( 'Display Code', 'fwp' ),
            'Query Arguments' => __( 'Query Arguments', 'fwp' ),
            'Saving' => __( 'Saving', 'fwp' ),
            'Indexing' => __( 'Indexing', 'fwp' ),
            'Indexing complete' => __( 'Indexing complete', 'fwp' ),
            'Looking' => __( 'Looking', 'fwp' ),
            'Purging' => __( 'Purging', 'fwp' ),
            'Copied!' => __( 'Copied!', 'fwp' ),
            'Press CTRL+C to copy' => __( 'Press CTRL+C to copy', 'fwp' ),
            'Activating' => __( 'Activating', 'fwp' ),
            'Re-index' => __( 'Re-index', 'fwp' ),
            'Stop indexer' => __( 'Stop indexer', 'fwp' ),
            'Loading' => __( 'Loading', 'fwp' ),
            'Importing' => __( 'Importing', 'fwp' ),
            'Convert to query args' => __( 'Convert to query args', 'fwp' )
        ];
    }


    /**
     * Get available image sizes
     * @since 3.2.7
     */
    function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $sizes = [];

        $default_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large', 'full' ];

        foreach ( get_intermediate_image_sizes() as $size ) {
            if ( in_array( $size, $default_sizes ) ) {
                $sizes[ $size ]['width'] = (int) get_option( "{$size}_size_w" );
                $sizes[ $size ]['height'] = (int) get_option( "{$size}_size_h" );
                $sizes[ $size ]['crop'] = (bool) get_option( "{$size}_size_crop" );
            }
            elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
                $sizes[ $size ] = $_wp_additional_image_sizes[ $size ];
            }
        }

        return $sizes;
    }


    /**
     * Return an array of formatted image sizes
     * @since 3.2.7
     */
    function get_image_size_labels() {
        $labels = [];
        $sizes = $this->get_image_sizes();

        foreach ( $sizes as $size => $data ) {
            $height = ( 0 === $data['height'] ) ? 'w' : 'x' . $data['height'];
            $label = $size . ' (' . $data['width'] . $height . ')';
            $labels[ $size ] = $label;
        }

        $labels['full'] = __( 'full', 'fwp' );

        return $labels;
    }
}
