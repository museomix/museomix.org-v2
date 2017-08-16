<?php
if ( ! defined( 'ABSPATH') ) {
    exit;
}

function CommunitiesMapdoCustomModule() {
    if(class_exists("ET_Builder_Module")){
        class ET_Builder_Module_CommunitiesMap extends ET_Builder_Module {
            function init() {
                wp_register_script( 'msx-google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyABpeQfeSPePCHf6eMZG03xgvIdbudl9Xg', null, null, true );
                wp_register_script( 'communities_map', get_stylesheet_directory_uri().'/assets/js/communities_map.js', array('msx-google-maps-api','global-js'), '', true);


                // wp_register_style('slick-css', '//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css');
                // wp_register_style('slick-theme', '//cdn.jsdelivr.net/jquery.slick/1.6.0/slick-theme.css');
                if(!is_admin()) {
                    wp_enqueue_script('communities_map');
                    wp_enqueue_script('msx-google-maps-api');
                    // wp_enqueue_style('slick-css');
                    // wp_enqueue_style('slick-theme');
                }

                $this->name            = esc_html__( 'Communities map' );
                $this->slug            = 'et_pb_communities_map_mc';

                $this->whitelisted_fields = array();
                foreach ( $this->get_fields() as $name => $field ) {
                    $this->whitelisted_fields[] = $name;
                }

            }

            function get_fields() {
                $fields = array(
                      'admin_label' => array(
                              'label'       => __( 'Admin Label', 'et_builder' ),
                              'type'        => 'text',
                              'description' => __( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
                      )
                );
                return $fields;
            }
            function shortcode_callback( $atts, $content = null, $function_name ) {

                $content = $this->shortcode_content;
                $communities = get_posts(
                    array(
                        'post_type' => 'community',
                        'suppress_filters' => false,
                        'posts_per_page' => -1
                    )
                );
                $locations = array();
                foreach($communities as $community):
                    $location = get_field('geolocation', $community->ID);
                    if ($location):
                        $locations[] = array(
                            'title' => '<h1>'.$community->post_title.'</h1>',
                            'lat' => (float)$location['lat'],
                            'lng' => (float)$location['lng'],
                            'link' => '<a class="btn map_info_link" href="'.get_permalink($community->ID).'">'.__('More info', 'museomix').'</a>'
                        );
                    endif;
                endforeach;
                if (!$locations)
                    return;

                $output = '<div class="et_pb_module et_pb_communities_map">
                        <div id="communities-map"><script>
                        var communities = '.json_encode($locations).';</script></div> <!-- .et_pb_slides -->
                    </div> <!-- .et_pb_slick_slider_mc -->
                    ';

                return $output;
            }
        }
        new ET_Builder_Module_CommunitiesMap;

        // On créée notre classe qui étend la classe 'ET_Buider_Module' de Divi
        class ET_Builder_Module_Communities_List extends ET_Builder_Module {

            // La fonction 'init' sert à déclarer toutes les propriétées et options de notre module
            function init() {
                $this->name       = esc_html__( 'Liste des communautés', 'et_builder' ); // Le nom
                $this->slug       = 'et_pb_communities_list'; // Le slug /!\ doit être unique
                //$this->fb_support = true; // Utilisable dans le Visual Builder

                // Ici on déclare nos champs pour notre module en admin
                $this->whitelisted_fields = array(
                    'title',
                    'include_country_list',
                    'admin_label',
                    'module_id',
                    'module_class',
                );

                // Ici on déclare nos différentes sections, et quels champs elles doivent contenir
                $this->options_toggles = array(
                    // Dans la section "général" on affiche nos champs principaux (main_content)
                    'general'  => array(
                        'toggles' => array( // Les 'toggles' sont les titres des sections en admin, on les réutilise dans la fonction get_fields
                            'main_content' => esc_html__( 'Contenu', 'et_builder' ),
                        ),
                    ),
                    // Dans la section "avancé" on affiche nos champs secondaires
                    'advanced' => array(
                        'toggles' => array(
                            'width' => array(
                                'title'    => esc_html__( 'Sizing', 'et_builder' ),
                                'priority' => 65,
                            ),
                        ),
                    ),
                );

                $this->main_css_element = '%%order_class%%';

                // On définit le sélecteur css de notre module pour la partir "CSS personnalisé" en admin
                $this->custom_css_options = array(
                    'main_element' => array(
                        'label'    => esc_html__( 'Main Element', 'et_builder' ),
                        'selector' => '.et_pb_communities_list.et_pb_module',
                        'no_space_before_selector' => true,
                    )
                );

                // On déclare que l'on veut 2 options supplémentaires : background et custom margin et padding
                // Ce sont deux options gérées nativement par Divi, nous n'avons pas besoin de les définir dans la fonction get_fields()
                $this->advanced_options = array(
                    'background' => array(
                        'settings' => array(
                            'color' => 'alpha',
                        ),
                    ),
                    'custom_margin_padding' => array(
                        'css' => array(
                            'important' => 'all',
                        ),
                    ),
                );
            }

            // La fonction get_fields sert à définir nos champs précédemment déclarés dans la fonction init()
            function get_fields() {
                $fields = array(
                    // Un titre pour notre module
                    'title' => array(
                        'label'       => esc_html__( 'Titre', 'et_builder' ), // Le label
                        'type'        => 'text', // Le type : text / select / etc
                        'description' => esc_html__( 'Titre de la liste', 'et_builder' ), // la description
                        'toggle_slug'     => 'main_content', // la section dans laquelle on veut qu'il s'affiche
                    ),
                    // La liste des pays (ACF)
                    'include_country_list' => array(
                        'label'           => esc_html__( 'Pays à afficher', 'et_builder' ),
                        'type'            => 'multiple_checkboxes', // Des cases à cocher
                        // On note ici le paramètres 'renderer', en effet nous devons afficher la liste de nos pays
                        // Et dynamiquement tant qu'à faire ! Ce paramètre permet d'aller chercher nos valeurs via une fonction
                        // Pour cela on appelle la fonction et_builder_include_country_list_option() (voir plus bas)
                        'renderer'         => $this->et_builder_include_country_list_option(),  
                        'description'     => esc_html__( 'Sélectionnez les pays à afficher', 'et_builder' ),
                        'toggle_slug'     => 'main_content',
                    ),
                    // Ci dessous les champs de base proposés par Divi ...
                    'disabled_on' => array(
                        'label'           => esc_html__( 'Disable on', 'et_builder' ),
                        'type'            => 'multiple_checkboxes',
                        'options'         => array(
                            'phone'   => esc_html__( 'Phone', 'et_builder' ),
                            'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
                            'desktop' => esc_html__( 'Desktop', 'et_builder' ),
                        ),
                        'additional_att'  => 'disable_on',
                        'option_category' => 'configuration',
                        'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
                    ),
                    'admin_label' => array(
                        'label'       => esc_html__( 'Admin Label', 'et_builder' ),
                        'type'        => 'text',
                        'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
                    ),
                    'module_id' => array(
                        'label'           => esc_html__( 'CSS ID', 'et_builder' ),
                        'type'            => 'text',
                        'option_category' => 'configuration',
                        'tab_slug'        => 'custom_css',
                        'option_class'    => 'et_pb_custom_css_regular',
                    ),
                    'module_class' => array(
                        'label'           => esc_html__( 'CSS Class', 'et_builder' ),
                        'type'            => 'text',
                        'option_category' => 'configuration',
                        'tab_slug'        => 'custom_css',
                        'option_class'    => 'et_pb_custom_css_regular',
                    ),
                );
                return $fields;
            }

            // La fonction shortcode_callback nous sert à récupérer les arguments créés dans le shortcode
            // Et d'en ressortir ce que l'on veut
            function shortcode_callback( $atts, $content = null, $function_name ) {

                // On récupère les valeurs via les identifiants de champs déclarés dans la fontion init()
                $title             = $this->shortcode_atts['title'];
                $country_list      = $this->shortcode_atts['include_country_list'];
                $module_id         = $this->shortcode_atts['module_id'];
                $module_class      = $this->shortcode_atts['module_class'];

                // La fonction add_module_order_class sert à construire la classe CSS de notre module
                $module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

                // Ici notre but est de récupérer les CPT "Communautés", on construir notre tableau d'arguments :
                $args = array(
                    'post_type' => 'community',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                );

                // On vérifie qu'il y a bien des pays cochés dans notre module
                if ( '' !== $country_list ) {
                    // Le soucis avec Divi c'est que comme il utilise des shortcodes, toutes nos valeurs sont des chaînes de caractères !
                    // Donc on récupère non pas un tableau des pays cochés en admin, mais une liste séparée par des virgules
                    // On va donc se construire notre propre tableau :
                    $include_countries = explode(',', $country_list);

                    // Et construire notre meta_query pour récupérer les CPT qui ont pour valeur une des villes sélectionnées en admin
                    $args['meta_query'][] = array(
                        'key' => 'country',
                        'value' => $include_countries,
                        'compare' => 'IN',
                    );
                }

                // On ouvre un tampon, pour temporiser la sortie
                ob_start();

                // On va chercher nos posts avec le tableau d'arguments précédemment créé
                $the_query = new WP_Query( $args );
                
                // Et on boucle !
                if ( $the_query->have_posts() ) {
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post(); ?>
                        <li class="et_pb_community">
                            <span class="community-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <?php if(get_field('sphere_of_influence')) : ?><span class="influcence">(<?php the_field('sphere_of_influence'); ?>)</span><?php endif; ?>
                            </span>
                        </li>
                        <?php
                    }
                }

                // On récupère notre tampon de sortie
                $posts = ob_get_contents();

                // Et on détruit le tampon
                ob_end_clean();

                // On construit notre variable $output 
                $output = sprintf(
                    '<ul %4$s class="%3$s et_pb_communities_list et_pb_module list-no-style">
                        %1$s
                        %2$s
                    </ul>',
                    ( '' !== $title ? sprintf( '<h3>%1$s</h3>', esc_html( $title ) ) : '' ), // le titre du module
                    $posts, // les posts (notre tampon)
                    esc_attr( $module_class ), //  la classe du module (s'il y a)
                    ( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ) // l'ID du module (s'il y a)
                );

                $output = sprintf( '<div class="et_pb_communities_list_wrapper">%1$s</div>', $output );

                // Et enfin on retourne notre résultat
                return $output;
            }

            // Cette fonction nous permets d'afficher dynamiquement dans nos cases à cocher du champs 'include_country_list'
            // La liste des pays paramétrés dans le champs ACF 'country'
            // Elle est directement inspirée de la fonction 'et_builder_include_categories_option' qui est définie dans Divi/includes/builder/functions.php
            function et_builder_include_country_list_option( $args = array() ) {

                // Ici on écrit temporairement un bout de JavaScript pour sauvegarder les cases des pays cochées
                // Cela est indispensable puisque lorsque l'on coche une case, rien n'est sauvegardé en BDD
                // Tant que nous n'avons pas mise à jour la page
                $output = "\t" . "<% var et_pb_include_country_list_temp = typeof et_pb_include_country_list !== 'undefined' ? et_pb_include_country_list.split( ',' ) : []; %>" . "\n";

                // On récupère les valeurs de notre champs ACF 'country'
                $countries = get_field_object( 'field_5763d528a1974');
                
                // Si on a des valeurs
                if( $countries ) {
                    foreach( $countries['choices'] as $k => $v ) {
                        // Pour chaque valeur on vérifie d'abord si une case a été cochée et stockée temporairement
                        // Si oui on la coche
                        $contains = sprintf(
                          '<%%= _.contains( et_pb_include_country_list_temp, "%1$s" ) ? checked="checked" : "" %%>',
                          esc_html( $k )
                        );

                        // Pour chaque valeur on affiche une cache à cocher + notre précédente variable $contains
                        $output .= sprintf(
                          '%4$s<label><input type="checkbox" name="et_pb_include_country_list" value="%1$s"%3$s> %2$s</label><br/>',
                          esc_attr( $k ),
                          esc_html( $v ),
                          $contains,
                          "\n\t\t\t\t\t"
                        );
                    }
                }
                $output = '<div id="et_pb_include_country_list">' . $output . '</div>';
                // On retourne le tout
                return $output;
            }
        }
        new ET_Builder_Module_Communities_List;
        // On vide le cache Divi lorsque les groupes de champs sont mis à jour pour actualiser la liste des pays dans notre module en admin
        add_action( 'acf/update_field_group', 'et_pb_force_regenerate_templates' );
    }
}

add_action('et_builder_ready', 'CommunitiesMapdoCustomModule');